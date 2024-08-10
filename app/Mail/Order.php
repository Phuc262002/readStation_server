<?php

namespace App\Mail;

use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Order extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public LoanOrders $loanOrders)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo xác nhận đơn hàng #' . $this->loanOrders->order_code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        $orderDetails = LoanOrderDetails::with(['bookDetails', 'bookDetails.book'])->where('loan_order_id', $this->loanOrders->id)->get();
        
        return new Content(
            markdown: 'mail.orders.create',
            with: ['loanOrders' => $this->loanOrders]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
