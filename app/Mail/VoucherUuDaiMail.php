<?php

namespace App\Mail;

use App\Models\LienHe;
use App\Models\NguoiDungVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VoucherUuDaiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lienHe;
    public $nguoiDungVoucher;
    public $lyDoNhan;

    public function __construct(
        LienHe $lienHe,
        NguoiDungVoucher $nguoiDungVoucher,
        string $lyDoNhan
    ) {
        $this->lienHe = $lienHe;
        $this->nguoiDungVoucher = $nguoiDungVoucher->loadMissing('voucher');
        $this->lyDoNhan = $lyDoNhan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CineHome - Bạn vừa nhận được một voucher ưu đãi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.voucher_uu_dai',
        );
    }
}