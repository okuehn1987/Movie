<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\HomeOfficeDay;
use App\Models\HomeOfficeDayGenerator;
use App\Models\OperatingSite;
use App\Models\User;
use App\Notifications\DisputeStatusNotification;
use App\Notifications\HomeOfficeDayDisputeStatusNotification;
use App\Notifications\HomeOfficeDayNotification;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class HomeOfficeDayController extends Controller
{
    public function store(Request $request, #[CurrentUser()] User $authUser)
    {
        $user = User::find($request['user_id']);
        Gate::authorize('create', [Absence::class, $user]);

        $validated = $request->validate([
            'start' => ['required', 'date', function ($attr, $val, $fail) use ($user, $request) {
                if (
                    AbsencePatch::getCurrentEntries($user, true)
                    ->where('start', '<=', $request['end'])
                    ->where('end', '>=', $request['start'])
                    ->count() > 0
                )
                    $fail('In diesem Zeitraum besteht bereits eine Abwesenheit.');
            }],
            'end' => 'required|date|after_or_equal:start',

            'user_id' => [
                'required',
                Rule::exists('users', 'id')->whereIn('operating_site_id', OperatingSite::inOrganization()->select('id'))
            ]

        ]);

        $requires_approval = !!$authUser->supervisor_id;

        $newHomeOfficeDays = collect();

        $newHomeOfficeDayGenerator = HomeOfficeDayGenerator::create([
            'user_id' => $validated['user_id'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'created_as_request' => true,
        ]);


        for ($date = Carbon::parse($validated['start'])->copy(); $date->lte(Carbon::parse($validated['end'])); $date->addDay()) {
            $newHomeOfficeDays->push(HomeOfficeDay::create([
                'user_id' => $validated['user_id'],
                'date' => $date,
                'status' => Status::Created,
                'home_office_day_generator_id' => $newHomeOfficeDayGenerator->id,
            ]));
        };

        if ($authUser->id !== $validated['user_id']) {
            $user->notify(new HomeOfficeDayDisputeStatusNotification(
                $validated['start'],
                $validated['end'],
                $newHomeOfficeDayGenerator,
                $requires_approval ? Status::Created : Status::Accepted
            ));
        }

        if ($requires_approval)
            $authUser->supervisor->notify(new HomeOfficeDayNotification(
                $validated['start'],
                $validated['end'],
                $newHomeOfficeDayGenerator,
                $authUser
            ));
        else $newHomeOfficeDays->each->update(['status' => Status::Accepted]);

        return back()->with('success', 'Abwesenheit erfolgreich beantragt.');
    }

    public function updateStatus(Request $request, HomeOfficeDayGenerator $homeOfficeDayGenerator, #[CurrentUser()] User $authUser)
    {
        Gate::authorize('update', [Absence::class, $homeOfficeDayGenerator->user]);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];


        DB::table('notifications')->where('type', HomeOfficeDayNotification::class)
            ->where('data->status', Status::Created)
            ->where('data->home_office_day_generator_id', $homeOfficeDayGenerator->id)
            ->update([
                'read_at' => now(),
                'data->status' => $is_accepted ? Status::Accepted : Status::Declined
            ]);

        if ($homeOfficeDayGenerator->user->id !== $authUser->id)
            $homeOfficeDayGenerator->user->notify(new HomeOfficeDayDisputeStatusNotification($homeOfficeDayGenerator, $homeOfficeDayGenerator->start, $homeOfficeDayGenerator->end, $is_accepted ? Status::Accepted : Status::Declined));

        if ($is_accepted) {
            DB::table('home_office_days')->where('home_office_day_generator_id', $homeOfficeDayGenerator->id)
                ->update(['status' => Status::Accepted]);
        } else {
            DB::table('home_office_days')->where('home_office_day_generator_id', $homeOfficeDayGenerator->id)
                ->update(['status' => Status::Declined]);
        }

        return back()->with('success',  "Abwesenheit erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }
}
