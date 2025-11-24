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
use App\Notifications\HomeOfficeDeleteNotification;
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
                $newHomeOfficeDayGenerator,
                $validated['start'],
                $validated['end'],
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

    public function destroy(HomeOfficeDay $homeOfficeDay, #[CurrentUser] User $authUser)
    // WIP TODO: Löschung beantragen Gate muss eingerichtete werden, sonst kann jeder User selber löschen
    {
        // Gate::authorize('deleteRequestable',  $homeOfficeDay);

        $homeOfficeDayGenerator = $homeOfficeDay->homeOfficeDayGenerator;

        // if (Gate::allows('delete', $homeOfficeDay)) {
        if ($homeOfficeDay) {
            $homeOfficeDay->delete();

            DB::table('notifications')->where('type', HomeOfficeDeleteNotification::class)
                ->where('data->status', Status::Created)
                ->where('data->home_office_day_generator_id', $homeOfficeDayGenerator->id)
                ->update([
                    'read_at' => now(),
                    'data->status' => Status::Accepted
                ]);

            if ($homeOfficeDay->user->id !== $authUser->id)
                $homeOfficeDay->user->notify(new HomeOfficeDayDisputeStatusNotification($homeOfficeDayGenerator, $homeOfficeDayGenerator->start, $homeOfficeDayGenerator->end, Status::Accepted, 'delete'));


            return back()->with('success', 'Die Abwesenheit wurde erfolgreich gelöscht.');
        } else {
            $authUser->supervisor->notify(new HomeOfficeDeleteNotification($authUser, $homeOfficeDay, $homeOfficeDay->date));
            return back()->with('success', 'Der Antrag auf Löschung wurder erfolgreich eingeleitet.');
        }
    }

    public function destroyDispute(HomeOfficeDay $homeOfficeDay)
    // Works except for gate
    {
        // Gate::authorize('deleteDispute', $homeOfficeDay);

        $homeOfficeDayGenerator_id = $homeOfficeDay->homeOfficeDayGenerator->id;

        if ($homeOfficeDay->deleteQuietly()) {
            DB::table('notifications')->where('type', HomeOfficeDayNotification::class)
                ->where('data->status', Status::Created)
                ->where('data->home_office_day_generator_id', $homeOfficeDayGenerator_id)
                ->delete();
        }

        if ($homeOfficeDay->deleteQuietly()) {
            DB::table('home_office_days')->where('home_office_day_generator_id', $homeOfficeDayGenerator_id)->delete();
        }

        return back()->with('success', 'Antrag auf HomeOffice erfolgreich zurückgezogen');
    }

    public function denyDestroy(HomeOfficeDay $homeOfficeDay, #[CurrentUser] User $authUser)
    // WIP not tested/started TODO:Gate
    {
        // Gate::allows('delete', $homeOfficeDay);

        $homeOfficeDayGenerator = $homeOfficeDay->homeOfficeDayGenerator;

        DB::table('notifications')->where('type', HomeOfficeDeleteNotification::class)
            ->where('data->status', Status::Created)
            ->where('data->home_office_day_generator_id', $homeOfficeDayGenerator->id)
            ->update([
                'read_at' => now(),
                'data->status' => Status::Declined
            ]);

        if ($homeOfficeDay->user->id !== $authUser->id)
            $homeOfficeDay->user->notify(new HomeOfficeDayDisputeStatusNotification($homeOfficeDayGenerator, $homeOfficeDayGenerator->start, $homeOfficeDayGenerator->end, Status::Declined, 'delete'));


        return back()->with('success', 'Der Antrag auf Löschung wurde abgelehnt.');
    }
}
