<?php

namespace App\Mail;

use App\Models\VeXemPhim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VeXemPhimDaDatMail extends Mailable
{
    use Queueable, SerializesModels;

    public $veXemPhim;
    public $foodItems;

    public function __construct(VeXemPhim $veXemPhim, array $foodItems = [])
    {
        $this->veXemPhim = $veXemPhim;
        $this->foodItems = $foodItems;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CineHome - Xác nhận đặt vé thành công #' . $this->veXemPhim->ma_ve,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ve_xem_phim',
        );
    }
}