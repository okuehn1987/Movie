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
            'title' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'nullable|string',
            'company' => 'nullable|string',
            'email' => 'required|email',
            'modules' => 'present|array',
            'modules.*' => 'nullable|string|in:tide,flow,isa',
            'message' => 'required|string|max:255',
            'preferredDates' => 'nullable|string',
        ]);

        $mail = (new MailMessage)
            ->greeting('Anfrage zu Herta')
            ->line('-------------------------------')
            ->line("Von: " . ($validated['title'] . ' ' . $validated['first_name'] . ' ' . $validated['last_name']))
            ->line("Email: " . $validated['email'])
            ->lineIf($validated['phone'], 'Telefon: ' . ($validated['phone']))
            ->lineIf($validated['company'], 'Unternehmen: ' . ($validated['company']))
            ->line('Interesse an: ' . (!empty($validated['modules'])
                ? implode(', ', $validated['modules'])
                : ''))
            ->line('Nachricht: ' . ($validated['message']))
            ->lineIf($validated['preferredDates'], 'Bevorzugte Termine: ' . ($validated['preferredDates']))
            ->line('-------------------------------')
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
