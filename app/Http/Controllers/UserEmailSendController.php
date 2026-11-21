<?php

namespace App\Http\Controllers;

use App\Jobs\SendUserEmailJob;
use Illuminate\Http\Request;

class UserEmailSendController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        return view('users.email.send', [
            'user' => $user,
            'templates' => $user->emailTemplates()->where('is_active', true)->latest()->get(),
            'patients' => $user->role === 2
                ? $user->patients()->where('is_active', true)->whereNotNull('email')->orderBy('name')->get()
                : collect(),
            'messages' => $user->emailMessages()->latest()->limit(40)->get(),
        ]);
    }

    public function send(Request $request)
    {
        $user = $request->user('user');
        $data = $request->validate([
            'template_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:30000'],
            'patient_ids' => ['nullable', 'array'],
            'patient_ids.*' => ['integer'],
            'manual_emails' => ['nullable', 'string', 'max:4000'],
            'action' => ['required', 'in:send,draft'],
        ]);

        $template = filled($data['template_id'] ?? null)
            ? $user->emailTemplates()->findOrFail($data['template_id'])
            : null;
        $recipients = [];

        if ($user->role === 2 && ! empty($data['patient_ids'])) {
            foreach ($user->patients()->whereIn('id', $data['patient_ids'])->whereNotNull('email')->get() as $patient) {
                $recipients[] = ['email' => $patient->email, 'name' => $patient->name, 'patient' => $patient];
            }
        }

        foreach (preg_split('/[\s,;]+/', trim($data['manual_emails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = ['email' => $email, 'name' => null, 'patient' => null];
            }
        }

        $recipients = collect($recipients)->unique('email')->values();
        if ($recipients->isEmpty()) {
            return back()->withErrors(['recipients' => 'Select at least one patient with an email or enter a valid manual email address.'])->withInput();
        }
        if ($data['action'] === 'send' && ! $user->smtpSettings) {
            return back()->withErrors(['recipients' => 'Configure SMTP Setup before sending emails.'])->withInput();
        }

        foreach ($recipients as $recipient) {
            $message = $user->emailMessages()->create([
                'user_email_template_id' => $template?->id,
                'user_patient_id' => $recipient['patient']?->id,
                'recipient_email' => $recipient['email'],
                'recipient_name' => $recipient['name'],
                'subject' => $data['subject'],
                'body' => $this->render($data['body'], $recipient['name'], $template?->image_path),
                'status' => $data['action'] === 'draft' ? 'draft' : 'queued',
            ]);

            if ($data['action'] === 'send') {
                // WAMP blocks outbound SMTP from the PHP CLI worker, while web PHP is permitted.
                // The job runs after the response through the working web process.
                SendUserEmailJob::dispatchAfterResponse($message->id);
            }
        }

        $text = $data['action'] === 'draft'
            ? 'Email draft saved for '.$recipients->count().' recipient(s).'
            : $recipients->count().' email(s) scheduled for background delivery.';

        return redirect()->route('user.email.send.index')->with('status', $text);
    }

    private function render(string $body, ?string $name, ?string $image): string
    {
        $body = str_replace(
            ['{{patient_name}}', '{{patient name}}', '{{template_image}}'],
            [
                e($name ?: 'Dear'),
                e($name ?: 'Dear'),
                $image ? '<img data-email-template-image="1" src="" alt="" style="max-width:100%;height:auto">' : '',
            ],
            $body
        );

        return nl2br($body);
    }
}
