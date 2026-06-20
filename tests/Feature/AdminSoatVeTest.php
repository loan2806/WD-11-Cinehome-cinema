<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\VeXemPhim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminSoatVeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create([
            'name' => 'soat_ve_vao_cua',
            'guard_name' => 'web',
        ]);
    }

    public function test_marks_a_paid_ticket_as_used_from_the_admin_qr_check_endpoint(): void
    {
        $admin = NguoiDung::factory()->create(['vai_tro' => 'admin']);
        $admin->givePermissionTo('soat_ve_vao_cua');

        $ticket = $this->createTicket([
            'ma_ve' => 'VE-TEST-001',
            'trang_thai' => 'da_thanh_toan',
            'nguoi_dung_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.soat-ve.check'), ['ma_ve' => $ticket->ma_ve])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('ticket.ma_ve', 'VE-TEST-001')
            ->assertJsonPath('ticket.trang_thai', 'da_su_dung');

        $this->assertSame('da_su_dung', $ticket->fresh()->trang_thai);
    }

    public function test_rejects_cancelled_tickets_from_the_admin_qr_check_endpoint(): void
    {
        $admin = NguoiDung::factory()->create(['vai_tro' => 'admin']);
        $admin->givePermissionTo('soat_ve_vao_cua');

        $ticket = $this->createTicket([
            'ma_ve' => 'VE-CANCELLED-001',
            'trang_thai' => 'da_huy',
            'nguoi_dung_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.soat-ve.check'), ['ma_ve' => $ticket->ma_ve])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('ticket.trang_thai', 'da_huy');

        $this->assertSame('da_huy', $ticket->fresh()->trang_thai);
    }

    private function createTicket(array $overrides = []): VeXemPhim
    {
        return VeXemPhim::create(array_merge([
            'ma_ve' => 'VE-DEFAULT-001',
            'ten_phim' => 'Demo Movie',
            'ten_rap' => 'CineHome',
            'ten_phong' => 'Phong 1',
            'ma_ghe' => 'A1',
            'thoi_gian_chieu' => now()->addHour(),
            'tong_tien' => 90000,
            'loai_ve' => 'truc_tuyen',
            'trang_thai' => 'da_thanh_toan',
        ], $overrides));
    }
}
