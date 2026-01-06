<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'company' => 'nullable|string',
            'email' => 'required|email',
            'modules' => 'present|array',
            'modules.*' => 'nullable|string|in:tide,flow,isa',
            'message' => 'required|string|max:255',
        ]);

        $mail = (new MailMessage)
            ->greeting('Anfrage zu Herta')
            ->line('-------------------------------')
            ->line("Von: " . ($validated['name']))
            ->line("Email: " . $validated['email'])
            ->line('Unternehmen: ' . ($validated['company'] ?? '—'))
            ->line('Interesse an: ' . (!empty($validated['modules'])
                ? implode(', ', $validated['modules'])
                : ''))
            ->line('Nachricht: ' . ($validated['message']))
            ->salutation(' ');

        Mail::send([], [], function ($message) use ($mail) {
            $message->to('info@mbd-team.de')
                ->subject('Kontaktanfrage bezüglich Herta')
                ->from(env('MAIL_FROM_ADDRESS'))
                ->html($mail->render() . '', 'text/html');
        });

        return back();
    }
}
