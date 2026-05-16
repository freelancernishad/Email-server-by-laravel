<?php

namespace App\Http\Controllers;

use App\Models\EmailConfiguration;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'config_key' => 'required|exists:email_configurations,key',
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file',
        ]);

        $config = EmailConfiguration::where('key', $validated['config_key'])->firstOrFail();

        $fromEmail = $validated['from_email'] ?? $config->from_address;
        $fromName = $validated['from_name'] ?? $config->from_name;

        try {
            if ($config->type === 'brevo') {
                $apiKey = $config->api_key ?? config('services.brevo.key');
                if (!$apiKey) {
                    throw new \Exception('Brevo API key is not configured.');
                }

                $payload = [
                    'sender' => ['name' => $fromName, 'email' => $fromEmail],
                    'to' => [['email' => $validated['to']]],
                    'subject' => $validated['subject'],
                    'htmlContent' => $validated['body'],
                ];

                if (isset($validated['attachments']) && $request->hasFile('attachments')) {
                    $payload['attachment'] = [];
                    foreach ($request->file('attachments') as $file) {
                        $payload['attachment'][] = [
                            'content' => base64_encode(file_get_contents($file->getRealPath())),
                            'name' => $file->getClientOriginalName(),
                        ];
                    }
                }

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', $payload);

                if (!$response->successful()) {
                    throw new \Exception('Brevo API Error: ' . $response->body());
                }
            } else {
                // Dynamically configure the mailer
                $mailerName = 'dynamic_smtp_' . $config->id;

                Config::set("mail.mailers.{$mailerName}", [
                    'transport' => 'smtp',
                    'host' => $config->host,
                    'port' => $config->port,
                    'encryption' => $config->encryption,
                    'username' => $config->username,
                    'password' => $config->password,
                    'timeout' => null,
                    'local_domain' => env('MAIL_EHLO_DOMAIN'),
                ]);

                Mail::mailer($mailerName)->send([], [], function ($message) use ($validated, $fromEmail, $fromName) {
                    $message->to($validated['to'])
                        ->from($fromEmail, $fromName)
                        ->subject($validated['subject'])
                        ->html($validated['body']);

                    if (isset($validated['attachments']) && request()->hasFile('attachments')) {
                        foreach (request()->file('attachments') as $file) {
                            $message->attach($file->getRealPath(), [
                                'as' => $file->getClientOriginalName(),
                                'mime' => $file->getClientMimeType(),
                            ]);
                        }
                    }
                });
            }

            // Log Success
            EmailLog::create([
                'config_key' => $validated['config_key'],
                'from_email' => $fromEmail,
                'to_email' => $validated['to'],
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'status' => 'success',
                'ip_address' => request()->ip(),
            ]);

            return response()->json(['message' => 'Email sent successfully'], 200);
        } catch (\Exception $e) {
            // Log Failure
            EmailLog::create([
                'config_key' => $validated['config_key'],
                'from_email' => $fromEmail,
                'to_email' => $validated['to'],
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'ip_address' => request()->ip(),
            ]);

            return response()->json(['message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|in:smtp,brevo',
            'api_key' => 'nullable|string',
            'host' => 'required_if:type,smtp|string|nullable',
            'port' => 'required_if:type,smtp|integer|nullable',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
        ]);

        $config = EmailConfiguration::create($validated);

        return response()->json(['message' => 'Configuration created successfully', 'data' => $config], 201);
    }

    public function index()
    {
        $configs = EmailConfiguration::orderBy('id', 'desc')->get();
        return response()->json($configs);
    }

    public function update(Request $request, $id)
    {
        $config = EmailConfiguration::findOrFail($id);

        $validated = $request->validate([
            'type' => 'nullable|string|in:smtp,brevo',
            'api_key' => 'nullable|string',
            'host' => 'required_if:type,smtp|string|nullable',
            'port' => 'required_if:type,smtp|integer|nullable',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
        ]);

        $config->update($validated);

        return response()->json(['message' => 'Configuration updated successfully', 'data' => $config]);
    }

    public function testBrevo(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string',
            'to' => 'required|email',
            'from_email' => 'required|email',
            'from_name' => 'nullable|string',
        ]);

        $payload = [
            'sender' => ['name' => $validated['from_name'] ?? 'Test Sender', 'email' => $validated['from_email']],
            'to' => [['email' => $validated['to']]],
            'subject' => 'Brevo API Test Email',
            'htmlContent' => '<h1>Brevo API Test</h1><p>This is a test email sent via Brevo API.</p>',
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'api-key' => $validated['api_key'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $payload);

            if ($response->successful()) {
                return response()->json(['message' => 'Test email sent successfully via Brevo API', 'response' => $response->json()], 200);
            } else {
                return response()->json(['message' => 'Failed to send test email', 'error' => $response->body()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error occurred while testing Brevo API', 'error' => $e->getMessage()], 500);
        }
    }
}
