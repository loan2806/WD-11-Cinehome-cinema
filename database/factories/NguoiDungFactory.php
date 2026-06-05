<?php

namespace Database\Factories;

use App\Models\NguoiDung; // Đã đổi từ User sang NguoiDung
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<NguoiDung>
 */
class NguoiDungFactory extends Factory
{
    /**
     * QUAN TRỌNG: Khai báo chính xác Model tương ứng với Factory
     * vì tên file tiếng Việt không tuân theo quy tắc tiếng Anh mặc định của Laravel.
     */
    protected $model = NguoiDung::class;

    /**
     * Mật khẩu dùng chung để tối ưu hóa hiệu năng khi tạo hàng loạt dữ liệu mẫu.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ho_ten' => fake()->name(), // Đã sửa từ 'name' thành 'ho_ten'
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'mat_khau' => static::$password ??= Hash::make('12345678'), // Đã sửa từ 'password' thành 'mat_khau' và đổi mật khẩu mẫu thành 12345678 cho dễ nhớ
            'vai_tro' => 'khach_hang', // Mới: Cập nhật trường vai trò mặc định khi fake dữ liệu
            'trang_thai_hoat_dong' => true, // Mới: Cập nhật trạng thái hoạt động mặc định
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Định trạng thái email chưa được xác thực (nếu cần dùng).
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}