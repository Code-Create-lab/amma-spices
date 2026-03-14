<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $order;
    public $isAdmin;
    public function __construct($order , $isAdmin)
    {
        $this->order = $order;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        if($this->isAdmin){
            return new Envelope(

                subject: 'New Order #'.$this->order->cart_id.' Received',
                
            );
        }else{
            
            return new Envelope(
                
                subject: 'Order Placed #'.$this->order->cart_id,
                
            );
            
         }
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'frontend.mail.order_placed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    // public function attachments()
    // {
    //     $filePath = storage_path('app/public/orders/order-generate-' . $this->order->cart_id . '.pdf');
    //     return [
    //         Attachment::fromPath($filePath)
    //             ->as('Order Invoice.pdf')
    //             ->withMime('application/pdf'),
    //     ];
    // }

     public function attachments()
    {
        return [];
    }
}
