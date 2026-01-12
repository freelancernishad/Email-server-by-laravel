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
            'config_key' => 'required|exists:email_configurations,key', // Now finding by key
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file',
        ]);

        $config = EmailConfiguration::where('key', $validated['config_key'])->firstOrFail();

        // Dynamically configure the mailer
        $mailerName = 'dynamic_smtp_' . $config->id; // Unique name to avoid conflicts if needed, though 'dynamic' is fine per request
        
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

        // Send email using the dynamic mailer
        try {
            Mail::mailer($mailerName)->send([], [], function ($message) use ($validated, $config) {
                // Determine From details: API Request > Database Config
                $fromEmail = $validated['from_email'] ?? $config->from_address;
                $fromName = $validated['from_name'] ?? $config->from_name;

                $message->to($validated['to'])
                        ->from($fromEmail, $fromName)
                        ->subject($validated['subject'])
                        ->html($validated['body']); // Assuming body is HTML

                if (isset($validated['attachments']) && request()->hasFile('attachments')) {
                    foreach (request()->file('attachments') as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                            'mime' => $file->getClientMimeType(),
                        ]);
                    }
                }
            });

            // Log Success
            EmailLog::create([
                'config_key' => $validated['config_key'],
                'from_email' => $validated['from_email'] ?? $config->from_address,
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
                'from_email' => $validated['from_email'] ?? $config->from_address,
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
            'host' => 'required|string',
            'port' => 'required|integer',
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
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
        ]);

        $config->update($validated);

        return response()->json(['message' => 'Configuration updated successfully', 'data' => $config]);
    }
}
