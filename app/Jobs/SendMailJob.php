<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Recipient email(s) — string or array of strings.
     */
    public string|array $to;

    /**
     * Fully constructed Mailable instance.
     */
    public Mailable $mailable;

    /**
     * Optional BCC recipients.
     */
    public array $bcc;

    /**
     * Optional CC recipients.
     */
    public array $cc;

    /**
     * Create a new job instance.
     *
     * Usage examples:
     *   SendMailJob::dispatch('user@example.com', new GuestWelcomeMail($user, $password));
     *   SendMailJob::dispatch(['a@x.com', 'b@x.com'], new SendOrderPlaced($order, 0), bcc: ['admin@x.com']);
     *   SendMailJob::dispatch($user->email, new ContactUs($data), cc: ['support@x.com']);
     */
    public function __construct(string|array $to, Mailable $mailable, array $bcc = [], array $cc = [])
    {
        $this->to = $to;
        $this->mailable = $mailable;
        $this->bcc = $bcc;
        $this->cc = $cc;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mail = Mail::to($this->to);

        if (!empty($this->bcc)) {
            $mail->bcc($this->bcc);
        }

        if (!empty($this->cc)) {
            $mail->cc($this->cc);
        }

        $mail->send($this->mailable);
    }
}
