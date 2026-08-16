<?php

namespace App\Mail;

use App\Models\LienHe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LienHePhanHoiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lienHe;

    public function __construct(LienHe $lienHe)
    {
        $this->lienHe = $lienHe;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CineHome - Phản hồi yêu cầu hỗ trợ của bạn '
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lien_he_phan_hoi',
        );
    }
}
