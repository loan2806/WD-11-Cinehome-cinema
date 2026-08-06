<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFoodCategoryRequest;
use App\Http\Requests\Admin\UpdateFoodCategoryRequest;
use App\Models\DanhMucDoAn;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FoodCategoryController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = DanhMucDoAn::where('is_combo', false)->orderBy('name');

        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where('name', 'like', "%{$keyword}%");
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.foods.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.foods.categories.create');
    }

    public function store(StoreFoodCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['is_combo'] = false;

        $category = DanhMucDoAn::create($data);

        AdminNotificationService::push(
            'Thêm danh mục món lẻ',
            "Đã thêm danh mục {$category->name}",
            'Success'
        );

        $this->ghiNhatKy(
            $request,
            'Thêm danh mục',
            'Quản lý đồ ăn lẻ',
            "Đã thêm danh mục {$category->name}"
        );

        return redirect()
            ->route('admin.foods.categories.index')
            ->with('success', 'Danh mục đã được tạo.');
    }

    public function edit(DanhMucDoAn $category)
    {
        return view('admin.foods.categories.edit', compact('category'));
    }

    public function update(UpdateFoodCategoryRequest $request, DanhMucDoAn $category)
    {
        $data = $request->validated();

        if ($category->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        $this->ghiNhatKy(
            $request,
            'Cập nhật danh mục',
            'Quản lý đồ ăn lẻ',
            "Cập nhật danh mục {$category->name}"
        );

        return redirect()
            ->route('admin.foods.categories.index')
            ->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(DanhMucDoAn $category)
    {
        if ($category->doAns()->exists()) {
            return back()->with('error', 'Không thể xóa vì danh mục đang có món.');
        }

        $category->delete();

        return back()->with('success', 'Danh mục đã được xóa.');
    }
}
