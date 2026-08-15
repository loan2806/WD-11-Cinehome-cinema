<?php

namespace App\Mail;

use App\Models\NguoiDung;
use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class VoucherDuocTangMail extends Mailable
{
    use Queueable, SerializesModels;

    public NguoiDung $customer;
    public Voucher $voucher;
    public Collection $issuedVouchers;
    public string $lyDoNhan;

    public function __construct(
        NguoiDung $customer,
        Voucher $voucher,
        Collection $issuedVouchers,
        string $lyDoNhan
    ) {
        $this->customer = $customer;
        $this->voucher = $voucher;
        $this->issuedVouchers = $issuedVouchers;
        $this->lyDoNhan = $lyDoNhan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CineHome - Bạn vừa nhận được voucher ưu đãi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.voucher_duoc_tang',
        );
    }
}