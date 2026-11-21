<?php

namespace App\Jobs;

use App\Models\UserEmailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SendUserEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $messageId) {}

    public function handle(): void
    {
        $message = UserEmailMessage::with(['user.smtpSettings', 'template'])->find($this->messageId);
        if (! $message || $message->status !== 'queued') return;
        if (! $message->user?->smtpSettings) {
            $message->update(['status' => 'failed', 'failure_reason' => 'SMTP settings are no longer available.']);
            return;
        }

        $settings = $message->user->smtpSettings;
        $mailer = new Mailer('user-bulk-email', app('view'), app('mail.manager')->createSymfonyTransport([
            'transport' => 'smtp', 'host' => $settings->host, 'port' => $settings->port,
            // Let Laravel select STARTTLS for TLS accounts and SMTPS for SSL accounts.
            'scheme' => $settings->encryption === 'ssl' ? 'smtps' : null,
            'encryption' => $settings->encryption === 'none' ? null : $settings->encryption,
            'username' => $settings->username, 'password' => $settings->password, 'timeout' => 15,
        ]), app('events'));
        $mailer->alwaysFrom($settings->from_email, $settings->from_name);

        try {
            $mailer->send([], [], function ($mail) use ($message): void {
                $body = $message->body;
                $imagePath = $message->template?->image_path;

                // Remote/local URLs such as 127.0.0.1 cannot be loaded by a recipient's
                // email client. Embed the uploaded template image directly into the email.
                if ($imagePath && File::isFile(public_path($imagePath))) {
                    $cid = $mail->embed(public_path($imagePath));
                    $body = preg_replace(
                        '/<img\\b[^>]*data-email-template-image="1"[^>]*>/i',
                        '<img src="'.e($cid).'" alt="" style="max-width:100%;height:auto">',
                        $body
                    );
                }

                $mail->html($body);
                $mail->to($message->recipient_email, $message->recipient_name)->subject($message->subject);
            });
            $message->update(['status' => 'sent', 'sent_at' => now(), 'failure_reason' => null]);
        } catch (Throwable $exception) {
            $detail = Str::limit(preg_replace('/(password|secret)=\S+/i', '$1=[hidden]', $exception->getMessage()), 220);
            Log::warning('Queued user email failed.', ['message_id' => $message->id, 'exception' => get_class($exception), 'detail' => $detail]);
            $message->update([
                'status' => 'failed',
                'failure_reason' => 'SMTP connection or authentication failed. Verify host, port, encryption and credentials in SMTP Setup.',
            ]);
        }
    }
}
