<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BugReportController extends Controller
{
    public function store(Request $request, #[CurrentUser()] User $authUser)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:2000',
            'location' => 'required|string|max:255',
            'summary' => 'required|string',
            'attachments' => 'present|array',
            'attachments.*' => 'image|max:10240',
        ]);

        foreach ($validated['attachments'] as $file) {
            Storage::disk('bug_reports')->putFile(now() . ' ' . $authUser->first_name . '-' . $authUser->last_name, $file);
        }

        $mail = (new MailMessage)->line($validated['summary'])
            ->line($validated['description'])
            ->line('Ort: ' . $validated['location']);

        Mail::send([], [], function ($message) use ($mail, $authUser) {
            $message
                ->to(env('MAIL_FROM_ADDRESS'))
                ->subject('Fehlermeldung von ' . $authUser->first_name . ' ' . $authUser->last_name)
                ->cc(['d.kyrpychenko@mbd-team.de', 'm.staacken@mbd-team.de'])
                ->from(env('MAIL_FROM_ADDRESS'))
                ->html($mail->render() . '', 'text/html');
        });

        return back()->with('success', 'Fehler erfolgreich gemeldet.');
    }
}
