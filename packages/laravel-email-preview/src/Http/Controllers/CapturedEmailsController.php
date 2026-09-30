<?php

namespace Ghijk\EmailPreview\Http\Controllers;

use Throwable;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Ghijk\EmailPreview\Mail\TestEmail;
use Ghijk\EmailPreview\Models\CapturedEmail;

class CapturedEmailsController extends Controller
{
    public function index(Request $request): Response
    {
        $emails = CapturedEmail::query()
            ->when($request->search, fn ($query) => $query->search($request->search))
            ->when($request->mailable_class, fn ($query) => $query->mailableClass($request->mailable_class))
            ->when($request->from || $request->to, fn ($query) => $query->dateRange($request->from, $request->to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $mailableClasses = CapturedEmail::query()
            ->whereNotNull('mailable_class')
            ->distinct()
            ->pluck('mailable_class')
            ->sort()
            ->values();

        return Inertia::render('Admin/CapturedEmails/Index', [
            'emails' => $emails,
            'mailableClasses' => $mailableClasses,
            'filters' => $request->only(['search', 'mailable_class', 'from', 'to']),
            'mailConfig' => $this->mailConfig(),
            'testResult' => $request->session()->get('email-preview.test_result'),
        ]);
    }

    public function show(string $uuid): Response
    {
        $email = CapturedEmail::where('uuid', $uuid)->firstOrFail();

        return Inertia::render('Admin/CapturedEmails/Show', [
            'email' => $email,
        ]);
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $email = CapturedEmail::where('uuid', $uuid)->firstOrFail();
        $email->delete();

        $routeName = config('email-preview.routes.name') . '.index';

        return to_route($routeName)->with('success', 'Email deleted successfully.');
    }

    public function clear(): RedirectResponse
    {
        CapturedEmail::truncate();

        $routeName = config('email-preview.routes.name') . '.index';

        return to_route($routeName)->with('success', 'All emails cleared successfully.');
    }

    /**
     * Send a test email through the default mailer and report whether it
     * landed in the captured emails table.
     */
    public function sendTest(Request $request): RedirectResponse
    {
        $mailConfig = $this->mailConfig();
        $token = Str::lower(Str::random(8));
        $recipient = $request->user()->email;

        try {
            Mail::to($recipient)->send(new TestEmail($token, $mailConfig['mailer']));
        } catch (Throwable $exception) {
            return $this->redirectWithTestResult([
                'status' => 'failed',
                'message' => "Sending through the \"{$mailConfig['mailer']}\" mailer failed: {$exception->getMessage()}",
            ]);
        }

        $captured = CapturedEmail::query()
            ->where('subject', "Test email [{$token}]")
            ->latest()
            ->first();

        if ($captured) {
            return $this->redirectWithTestResult([
                'status' => 'captured',
                'message' => "Test email to {$recipient} was captured successfully.",
                'uuid' => $captured->uuid,
            ]);
        }

        return $this->redirectWithTestResult([
            'status' => 'not_captured',
            'message' => "Test email to {$recipient} was sent through the \"{$mailConfig['mailer']}\" mailer ({$mailConfig['transport']} transport), so it was delivered rather than captured. Set MAIL_MAILER=database to capture emails here.",
        ]);
    }

    /**
     * @return array{mailer: string, transport: ?string, captures: bool}
     */
    protected function mailConfig(): array
    {
        $mailer = (string) config('mail.default');
        $transport = config("mail.mailers.{$mailer}.transport");

        return [
            'mailer' => $mailer,
            'transport' => $transport,
            'captures' => $transport === 'database',
        ];
    }

    /**
     * @param  array{status: string, message: string, uuid?: string}  $result
     */
    protected function redirectWithTestResult(array $result): RedirectResponse
    {
        $routeName = config('email-preview.routes.name') . '.index';

        return to_route($routeName)->with('email-preview.test_result', $result);
    }
}
