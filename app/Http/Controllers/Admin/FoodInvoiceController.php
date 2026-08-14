<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doan;
use App\Models\FoodInvoice;
use App\Services\FoodInventoryService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PayOS\PayOS;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FoodInvoiceController extends Controller
{
    use Loggable;

    private const PAYOS_HOLD_MINUTES = 4;

    public function __construct(private FoodInventoryService $foodInventory)
    {
    }

    public function index(Request $request)
    {
        $query = FoodInvoice::with('items', 'user')->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('food_name', 'like', "%{$keyword}%"));
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $summaryQuery = clone $query;

        $summaryInvoices = $summaryQuery->get();
        $summary = [
            'count' => $summaryInvoices->count(),
            'paid_count' => $summaryInvoices->where('payment_status', 'paid')->count(),
            'pending_count' => $summaryInvoices->where('payment_status', 'pending')->count(),
            'paid_total' => $summaryInvoices->where('payment_status', 'paid')->sum('total'),
        ];

        $invoices = $query->paginate(10)->withQueryString();

        $foodsForSale = Doan::active()
            ->with([
                'category',
                'variants' => fn ($variantQuery) => $variantQuery
                    ->where('is_active', true)
                    ->orderBy('price')
                    ->orderBy('id'),
                'comboItems.variant',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Mỗi biến thể (size) là một lựa chọn "chọn nhanh" riêng, vì giá và
        // tồn kho khác nhau theo từng size — gộp chung dễ gây hiểu lầm "không trừ kho".
        $quickFoods = $foodsForSale
            ->flatMap(function (Doan $food) {
                if ($food->isCombo()) {
                    return $food->stock_quantity > 0 ? [[
                        'food_id' => $food->id,
                        'variant_id' => null,
                        'label' => $food->name,
                        'price' => $food->price,
                        'stock' => $food->stock_quantity,
                    ]] : [];
                }

                return $food->variants
                    ->filter(fn ($variant) => $variant->stock_quantity > 0)
                    ->map(fn ($variant) => [
                        'food_id' => $food->id,
                        'variant_id' => $variant->id,
                        'label' => $variant->value ? "{$food->name} - {$variant->value}" : $food->name,
                        'price' => (float) $variant->price,
                        'stock' => $variant->stock_quantity,
                    ]);
            })
            ->values();

        $lowStockFoods = $foodsForSale
            ->filter(fn (Doan $food) => $food->stock_quantity > 0 && $food->stock_quantity <= $food->min_stock_quantity)
            ->sortBy('stock_quantity')
            ->take(5)
            ->values();

        return view('admin.food-invoices.index', compact('invoices', 'summary', 'quickFoods', 'lowStockFoods'));
    }

    /**
     * In hóa đơn đồ ăn để khách cầm ra quầy nhận đồ ăn.
     * Chỉ in được khi hóa đơn đã thanh toán xong, và chỉ in được ĐÚNG 1 LẦN —
     * xác nhận in xong là đánh dấu printed_at ngay, không cho in lại nữa.
     */
    public function print(FoodInvoice $foodInvoice)
    {
        if ($foodInvoice->payment_status !== 'paid') {
            abort(422, 'Hóa đơn chưa thanh toán thành công nên chưa thể in.');
        }

        if ($foodInvoice->isPrinted()) {
            abort(422, 'Hóa đơn này đã được in trước đó, không thể in lại.');
        }

        $foodInvoice->load(['items', 'user']);
        $foodInvoice->update(['printed_at' => now()]);

        return view('admin.food-invoices.print', ['invoice' => $foodInvoice]);
    }

    public function store(Request $request)
    {
        $items = collect($request->input('items', []))
            ->filter(fn ($item) => filled($item['food_name'] ?? null) || filled($item['unit_price'] ?? null))
            ->values()
            ->all();

        $request->merge(['items' => $items]);

        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:tiền mặt,chuyển khoản,thẻ'],
            'received_amount' => ['nullable', 'required_if:payment_method,tiền mặt', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_id' => ['nullable', 'integer', 'exists:foods,id'],
            'items.*.food_variant_id' => ['nullable', 'integer', 'exists:food_variants,id'],
            'items.*.food_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Vui lòng nhập ít nhất một món.',
            'items.min' => 'Vui lòng nhập ít nhất một món.',
            'items.*.food_name.required' => 'Vui lòng nhập tên món.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.unit_price.required' => 'Vui lòng nhập đơn giá.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'received_amount.required_if' => 'Vui lòng nhập số tiền khách đưa.',
        ]);

        $discount = (float) ($data['discount'] ?? 0);

        $items = $this->normalizeItems($data['items']);
        $subtotal = collect($items)->sum(fn ($item) => (int) $item['quantity'] * (float) $item['unit_price']);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Giảm giá không được lớn hơn tạm tính.',
            ]);
        }

        $total = max($subtotal - $discount, 0);
        $isBankTransfer = $data['payment_method'] === 'chuyển khoản';

        // Chuyển khoản: khách phải quét mã QR và PayOS xác nhận đã nhận tiền
        // thì mới coi là đã thanh toán — không trừ kho ngay lúc tạo.
        if ($isBankTransfer) {
            $invoice = DB::transaction(function () use ($data, $items, $subtotal, $discount, $total) {
                $invoice = FoodInvoice::create([
                    'invoice_code' => 'FD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'user_id' => Auth::id(),
                    'customer_name' => $data['customer_name'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_status' => 'pending',
                    'inventory_deducted' => false,
                    'payment_method' => $data['payment_method'],
                    'expires_at' => now()->addMinutes(self::PAYOS_HOLD_MINUTES),
                    'note' => $data['note'] ?? null,
                ]);

                foreach ($items as $item) {
                    $invoice->items()->create([
                        'food_id' => $item['food_id'],
                        'food_variant_id' => $item['food_variant_id'],
                        'food_name' => $item['food_name'],
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (float) $item['unit_price'],
                        'total_price' => (int) $item['quantity'] * (float) $item['unit_price'],
                    ]);
                }

                return $invoice;
            });

            try {
                $this->createPayosLink($invoice, $total);
            } catch (\Throwable $exception) {
                report($exception);
                $invoice->delete();

                throw ValidationException::withMessages([
                    'payment_method' => 'Không tạo được mã QR thanh toán. Vui lòng thử lại.',
                ]);
            }

            $this->ghiNhatKy($request, 'Tạo hóa đơn đồ ăn (chờ chuyển khoản)', 'Quản lý hóa đơn đồ ăn', "Tạo hóa đơn: {$invoice->invoice_code}");

            return redirect()->route('admin.food-invoices.vietqr-waiting', $invoice);
        }

        $receivedAmount = null;
        $changeAmount = null;

        if ($data['payment_method'] === 'tiền mặt') {
            $receivedAmount = (float) $data['received_amount'];

            if ($receivedAmount < $total) {
                throw ValidationException::withMessages([
                    'received_amount' => 'Số tiền khách đưa không đủ để thanh toán hóa đơn.',
                ]);
            }

            $changeAmount = $receivedAmount - $total;
        }

        $invoice = DB::transaction(function () use ($data, $items, $subtotal, $discount, $total, $receivedAmount, $changeAmount) {
            $this->deductInventory($items);

            $invoice = FoodInvoice::create([
                'invoice_code' => 'FD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'user_id' => Auth::id(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_status' => 'paid',
                'inventory_deducted' => true,
                'payment_method' => $data['payment_method'],
                'received_amount' => $receivedAmount,
                'change_amount' => $changeAmount,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($items as $item) {
                $invoice->items()->create([
                    'food_id' => $item['food_id'],
                    'food_variant_id' => $item['food_variant_id'],
                    'food_name' => $item['food_name'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'total_price' => (int) $item['quantity'] * (float) $item['unit_price'],
                ]);
            }

            return $invoice;
        });

        $this->ghiNhatKy($request, 'Tạo hóa đơn đồ ăn', 'Quản lý hóa đơn đồ ăn', "Tạo hóa đơn: {$invoice->invoice_code}");

        return redirect()
            ->route('admin.food-invoices.index')
            ->with('success', 'Đã tạo hóa đơn đồ ăn.');
    }

    /**
     * Màn hình chờ khách quét mã QR VietQR (PayOS) để thanh toán.
     */
    public function vietQrWaiting(FoodInvoice $foodInvoice)
    {
        if ($foodInvoice->payment_status === 'paid') {
            return redirect()->route('admin.food-invoices.index')->with('success', 'Hóa đơn đã được thanh toán.');
        }

        if ($foodInvoice->payment_status === 'cancelled') {
            return redirect()->route('admin.food-invoices.index')->with('error', 'Hóa đơn này đã bị hủy.');
        }

        if ($foodInvoice->isExpired()) {
            $this->expireInvoice($foodInvoice);

            return redirect()->route('admin.food-invoices.index')->with('error', 'Đã hết thời gian chờ thanh toán, hóa đơn bị hủy.');
        }

        if (! $foodInvoice->payos_qr_code) {
            return redirect()->route('admin.food-invoices.index')->with('error', 'Hóa đơn thiếu dữ liệu mã QR. Vui lòng tạo lại.');
        }

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->generate((string) $foodInvoice->payos_qr_code);

        return view('admin.food-invoices.vietqr-waiting', [
            'invoice' => $foodInvoice->load('items'),
            'qrSvg' => $qrSvg,
        ]);
    }

    /**
     * Frontend gọi định kỳ để kiểm tra PayOS đã ghi nhận thanh toán chưa.
     */
    public function vietQrStatus(FoodInvoice $foodInvoice)
    {
        if ($foodInvoice->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'status' => 'PAID',
                'redirect_url' => route('admin.food-invoices.index'),
            ]);
        }

        if ($foodInvoice->payment_status === 'cancelled') {
            return response()->json([
                'success' => true,
                'status' => 'CANCELLED',
                'redirect_url' => route('admin.food-invoices.index'),
            ]);
        }

        if ($foodInvoice->isExpired()) {
            $this->expireInvoice($foodInvoice);

            return response()->json([
                'success' => true,
                'status' => 'EXPIRED',
                'redirect_url' => route('admin.food-invoices.index'),
            ]);
        }

        try {
            $status = $this->getPayosStatus($foodInvoice);

            if ($status === 'PAID') {
                $this->finalizePaidInvoice($foodInvoice);

                return response()->json([
                    'success' => true,
                    'status' => 'PAID',
                    'redirect_url' => route('admin.food-invoices.index'),
                ]);
            }

            if ($status === 'CANCELLED') {
                $this->expireInvoice($foodInvoice);

                return response()->json([
                    'success' => true,
                    'status' => 'CANCELLED',
                    'redirect_url' => route('admin.food-invoices.index'),
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $status ?: 'PENDING',
                'expires_at' => optional($foodInvoice->expires_at)?->toIso8601String(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'status' => 'PENDING',
                'message' => 'Chưa thể kiểm tra PayOS, hệ thống sẽ tự thử lại.',
            ], 200);
        }
    }

    /**
     * Nhân viên hủy thủ công giao dịch VietQR đang chờ.
     */
    public function cancelPendingVietQr(Request $request, FoodInvoice $foodInvoice)
    {
        if ($foodInvoice->payment_status === 'pending') {
            try {
                if ($this->getPayosStatus($foodInvoice) === 'PAID') {
                    $this->finalizePaidInvoice($foodInvoice);

                    return redirect()
                        ->route('admin.food-invoices.index')
                        ->with('success', 'PayOS vừa xác nhận thanh toán thành công nên hóa đơn không bị hủy.');
                }
            } catch (\Throwable $exception) {
                report($exception);
            }

            $this->cancelPayosLinkSilently($foodInvoice, 'Nhan vien huy hoa don tai quay');
            $this->expireInvoice($foodInvoice);
        }

        return redirect()
            ->route('admin.food-invoices.index')
            ->with('success', 'Đã hủy hóa đơn chờ chuyển khoản.');
    }

    public function updateStatus(Request $request, FoodInvoice $foodInvoice)
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
        ]);

        $oldStatus = $foodInvoice->payment_status;

        DB::transaction(function () use ($foodInvoice, $data) {
            $foodInvoice->load('items');
            $newStatus = $data['payment_status'];

            if (! $foodInvoice->inventory_deducted && $newStatus !== 'cancelled') {
                $this->deductInventory($foodInvoice->items);
                $foodInvoice->inventory_deducted = true;
            }

            if ($foodInvoice->inventory_deducted && $newStatus === 'cancelled') {
                $this->restoreInventory($foodInvoice->items);
                $foodInvoice->inventory_deducted = false;
            }

            $foodInvoice->payment_status = $newStatus;
            $foodInvoice->save();
        });

        $this->ghiNhatKy(
            $request,
            'Cập nhật trạng thái hóa đơn đồ ăn',
            'Quản lý hóa đơn đồ ăn',
            "Cập nhật {$foodInvoice->invoice_code}: {$oldStatus} -> {$foodInvoice->payment_status}"
        );

        return back()->with('success', 'Đã cập nhật trạng thái hóa đơn.');
    }

    public function destroy(Request $request, FoodInvoice $foodInvoice)
    {
        $code = $foodInvoice->invoice_code;

        DB::transaction(function () use ($foodInvoice) {
            $foodInvoice->load('items');

            if ($foodInvoice->inventory_deducted) {
                $this->restoreInventory($foodInvoice->items);
            }

            $foodInvoice->items()->delete();
            $foodInvoice->delete();
        });

        $this->ghiNhatKy($request, 'Xóa hóa đơn đồ ăn', 'Quản lý hóa đơn đồ ăn', "Xóa hóa đơn: {$code}");

        return back()->with('success', 'Đã xóa hóa đơn đồ ăn.');
    }

    private function normalizeItems(array $items): array
    {
        $foodIds = collect($items)
            ->pluck('food_id')
            ->filter()
            ->unique()
            ->values();

        $foods = Doan::with([
            'category',
            'variants' => fn ($variantQuery) => $variantQuery
                ->where('is_active', true)
                ->orderBy('price')
                ->orderBy('id'),
            'comboItems.variant',
        ])
            ->whereIn('id', $foodIds)
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function ($item) use ($foods) {
                $food = filled($item['food_id'] ?? null) ? $foods->get((int) $item['food_id']) : null;

                if ($food && ! $food->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Món {$food->name} đang tạm ẩn, không thể bán mới.",
                    ]);
                }

                $variant = null;
                if ($food && filled($item['food_variant_id'] ?? null)) {
                    $variant = $food->variants->firstWhere('id', (int) $item['food_variant_id']);
                }

                $foodName = $food?->name ?? trim($item['food_name']);
                if ($variant?->value) {
                    $foodName .= " - {$variant->value}";
                }

                return [
                    'food_id' => $food?->id,
                    'food_variant_id' => $variant?->id,
                    'food_name' => $foodName,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $variant ? (float) $variant->price : ($food ? (float) $food->price : (float) $item['unit_price']),
                ];
            })
            ->values()
            ->all();
    }

    private function deductInventory($items): void
    {
        try {
            $this->foodInventory->deduct($items);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages(['items' => $exception->getMessage()]);
        }
    }

    private function restoreInventory($items): void
    {
        $this->foodInventory->restore($items);
    }

    /**
     * Tạo link + mã QR VietQR qua PayOS cho hóa đơn đang chờ chuyển khoản.
     */
    private function createPayosLink(FoodInvoice $invoice, float $total): void
    {
        $orderCode = (int) (now()->format('ymdHis') . random_int(10, 99));

        $payOS = new PayOS(
            env('PAYOS_CLIENT_ID'),
            env('PAYOS_API_KEY'),
            env('PAYOS_CHECKSUM_KEY')
        );

        $response = $payOS->paymentRequests->create([
            'orderCode' => $orderCode,
            'amount' => (int) round($total),
            'description' => 'FD' . $orderCode,
            'returnUrl' => route('admin.food-invoices.index'),
            'cancelUrl' => route('admin.food-invoices.index'),
            'expiredAt' => $invoice->expires_at->timestamp,
        ]);

        $checkoutUrl = data_get($response, 'checkoutUrl');
        $qrCode = data_get($response, 'qrCode');

        if (! $checkoutUrl || ! $qrCode) {
            throw new \RuntimeException('PayOS không trả về đầy đủ thông tin thanh toán VietQR.');
        }

        $invoice->update([
            'payos_order_code' => $orderCode,
            'payos_checkout_url' => $checkoutUrl,
            'payos_qr_code' => $qrCode,
        ]);
    }

    private function getPayosStatus(FoodInvoice $invoice): string
    {
        if (! $invoice->payos_order_code) {
            throw new \RuntimeException('Hóa đơn không có PayOS orderCode.');
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'x-client-id' => (string) env('PAYOS_CLIENT_ID'),
                'x-api-key' => (string) env('PAYOS_API_KEY'),
            ])
            ->get('https://api-merchant.payos.vn/v2/payment-requests/' . $invoice->payos_order_code);

        if (! $response->successful()) {
            throw new \RuntimeException('Không thể lấy trạng thái PayOS (HTTP ' . $response->status() . ').');
        }

        return strtoupper((string) data_get($response->json(), 'data.status', 'PENDING'));
    }

    /** Hủy payment link PayOS nhưng không làm hỏng luồng nếu API đang lỗi. */
    private function cancelPayosLinkSilently(FoodInvoice $invoice, string $reason): void
    {
        if (! $invoice->payos_order_code) {
            return;
        }

        try {
            Http::timeout(10)
                ->acceptJson()
                ->withHeaders([
                    'x-client-id' => (string) env('PAYOS_CLIENT_ID'),
                    'x-api-key' => (string) env('PAYOS_API_KEY'),
                ])
                ->post(
                    'https://api-merchant.payos.vn/v2/payment-requests/' . $invoice->payos_order_code . '/cancel',
                    ['cancellationReason' => $reason]
                );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Hoàn tất đúng một lần hóa đơn VietQR đã PAID: trừ kho + đánh dấu đã thanh toán.
     */
    private function finalizePaidInvoice(FoodInvoice $invoice): FoodInvoice
    {
        return DB::transaction(function () use ($invoice) {
            $locked = FoodInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->payment_status === 'paid') {
                return $locked;
            }

            if ($locked->payment_status === 'cancelled') {
                throw new \RuntimeException('Hóa đơn đã bị hủy trước khi PayOS xác nhận thanh toán.');
            }

            $locked->load('items');

            if (! $locked->inventory_deducted) {
                $this->deductInventory($locked->items);
            }

            $locked->update([
                'payment_status' => 'paid',
                'inventory_deducted' => true,
                'expires_at' => null,
            ]);

            return $locked;
        });
    }

    /** Đánh dấu hóa đơn chờ chuyển khoản là đã hủy (hết hạn hoặc bị hủy thủ công). Chưa trừ kho nên không cần hoàn kho. */
    private function expireInvoice(FoodInvoice $invoice): void
    {
        if ($invoice->payment_status !== 'pending') {
            return;
        }

        $invoice->update([
            'payment_status' => 'cancelled',
            'expires_at' => null,
        ]);
    }
}
