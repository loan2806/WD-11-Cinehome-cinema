<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DiemThanhVienMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loai;
    public int $soDiem;
    public string $noiDung;
    public int $diemHienTai;
    public string $hoTen;

    public function __construct(
        string $loai,
        int $soDiem,
        string $noiDung,
        int $diemHienTai,
        string $hoTen = 'Quý khách'
    ) {
        $this->loai = $loai;
        $this->soDiem = $soDiem;
        $this->noiDung = $noiDung;
        $this->diemHienTai = $diemHienTai;
        $this->hoTen = $hoTen;
    }

    public function build()
    {
        $tieuDe = $this->loai === 'tang'
            ? '🎁 Bạn vừa được tặng điểm - CineHome'
            : '🔄 Điểm thành viên đã được cập nhật - CineHome';

        return $this
            ->subject($tieuDe)
            ->view('emails.diem-thanh-vien');
    }
}