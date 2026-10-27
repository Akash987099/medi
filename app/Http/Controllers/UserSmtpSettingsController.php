<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class UserSmtpSettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('users.smtp.index', [
            'user' => $request->user('user'),
            'settings' => $request->user('user')->smtpSettings,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1024'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $settings = $request->user('user')->smtpSettings()->firstOrNew();
        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }
        $settings->fill($validated);
        $settings->user()->associate($request->user('user'));
        $settings->save();

        return redirect()->route('user.smtp.index')->with('status', 'Your private SMTP settings were saved.');
    }

    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'recipient' => ['required', 'email', 'max:255'],
        ]);
        $user = $request->user('user');
        $settings = $user->smtpSettings;

        if (! $settings) {
            return redirect()->route('user.smtp.index')->withErrors([
                'smtp' => 'Save your SMTP settings before sending a test email.',
            ]);
        }

        $mailer = new Mailer(
            'user-smtp-test',
            app('view'),
            app('mail.manager')->createSymfonyTransport([
                'transport' => 'smtp',
                'host' => $settings->host,
                'port' => $settings->port,
                'scheme' => $settings->encryption === 'ssl' ? 'smtps' : 'smtp',
                'username' => $settings->username,
                'password' => $settings->password,
                'timeout' => 10,
            ]),
            app('events')
        );
        $mailer->alwaysFrom($settings->from_email, $settings->from_name);

        try {
            $mailer->raw(
                'This is a test email from your MediCare SMTP settings. If you received this message, your SMTP configuration is working.',
                function ($message) use ($validated): void {
                    $message->to($validated['recipient'])
                        ->subject('MediCare SMTP test');
                }
            );
        } catch (Throwable $exception) {
            Log::warning('Per-account SMTP test email failed.', [
                'user_id' => $user->id,
                'exception' => get_class($exception),
            ]);

            return redirect()->route('user.smtp.index')->withErrors([
                'smtp' => 'The test email could not be sent. Verify your SMTP host, port, encryption and credentials.',
            ]);
        }

        return redirect()->route('user.smtp.index')->with('status', 'Test email sent successfully.');
    }
}
