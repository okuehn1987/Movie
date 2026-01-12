<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Absence;
use App\Models\AbsenceType;
use App\Models\Shift;
use App\Models\User;
use App\Services\AppModuleService;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(#[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $visibleUsers = User::inOrganization()
            ->with([
                'operatingSite.currentAddress:id,country,federal_state,addresses.addressable_id,addresses.addressable_type',
                'absences' =>
                fn($q) => $q->where(
                    fn($q) => $q->whereIn('status', [Status::Accepted, Status::Created])
                        ->doesntHave('currentAcceptedPatch')
                        ->whereDate('end', '>=', now())
                )->orWhereHas(
                    'currentAcceptedPatch',
                    fn($q) => $q->whereDate('end', '>=', now())->whereNot('type', 'delete')
                ),
                'absences.currentAcceptedPatch:id,start,end,absence_type_id,status,user_id,absence_patches.absence_id',
                'userWorkingWeeks'
            ])
            ->get(['id', 'supervisor_id', 'group_id', 'operating_site_id', 'first_name', 'last_name'])
            ->filter(fn($u) => $authUser->can('viewShow', [Absence::class, $u]));

        $currentAbsences = $visibleUsers->map(fn($u) => [...self::currentAbsencePeriod($u), 'last_name' => $u->last_name, 'first_name' => $u->first_name])
            ->filter(fn($a) => $a['end'] !== null && $a['start'] !== null)
            ->values();
        $tideProps = [];
        if (AppModuleService::hasAppModule('tide')) {
            $authUser->load([
                'shifts' => fn($q) => $q->with('user:id,date_of_birth')
                    ->whereDate('start', '>=', now()->subDays(7))
                    ->with('workLogs', 'workLogPatches')
            ]);

            $lastEntries = $authUser->workLogs()
                ->whereIn('shift_id', $authUser->shifts()->whereDate('start', '>=', now()->subDays(7))->select('id'))
                ->with('currentAcceptedPatch:id,start,end,work_log_patches.work_log_id')
                ->whereDoesntHave('currentAcceptedPatch', fn($q) => $q->where('type', 'delete'))
                ->whereNotNull('end')
                ->limit(5)
                ->get()
                ->map(fn($e) => $e->currentAcceptedPatch ?? $e)
                ->each
                ->append('duration')
                ->sortByDesc('start')
                ->map(fn($e) => collect($e)->only(['id', 'start', 'end', 'duration']))
                ->values();

            $tideProps = [
                'overtime' => $authUser->overtime,
                'workingHours' => self::getCurrentWeekWorkingHours($authUser),
                'lastEntries' => $lastEntries,
            ];
        }

        return Inertia::render('Dashboard/Dashboard', [
            'user' => [
                ...$authUser->load(['latestWorkLog', 'currentTrustWorkingHours:id,user_trust_working_hours.user_id'])->toArray(),
                'current_shift' => $authUser->currentShift()->first(['id', 'user_id', 'start', 'end'])?->append('current_work_duration')
            ],
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($authUser->supervisor_id),
            'currentAbsences' => $currentAbsences,
            ...$tideProps,
        ]);
    }

    private function currentAbsencePeriod(User $user)
    {
        $futureAbsences = $user->absences->map(fn($a) => $a->currentAcceptedPatch ?? $a);

        $currentAbsence = $futureAbsences->firstWhere(
            fn($a) =>
            Carbon::parse($a['start'])->lte(now()->startOfDay()) && Carbon::parse($a['end'])->gte(now()->startOfDay())
        );
        $firstDay = $currentAbsence ? Carbon::parse($currentAbsence['start']) : now();
        $lastDay = null;
        $absenceTypes = collect();

        if ($user->workingWeeks->count() > 0) {
            for ($day = now()->startOfDay();; $day->addDay()) {
                $absencesForDay = $futureAbsences->filter(fn($a) => Carbon::parse($a['start'])->lte($day) && Carbon::parse($a['end'])->gte($day));

                if ($absencesForDay->count() > 0 || !$user->shouldWork($day)) {
                    $absenceTypes = $absenceTypes->merge($absencesForDay->pluck('absence_type_id'));
                    $lastDay = $day->copy();
                } else {
                    break;
                }
            }
        }
        return [
            'start' => $firstDay->format('d.m.Y'),
            'end' => $lastDay?->format('d.m.Y'),
            'type' => request()->user()->can(
                'viewShow',
                [AbsenceType::class, $user]
            ) ?
                $absenceTypes->unique()->map(fn($t) => request()->user()->allAbsenceTypes->find($t)->abbreviation)->join(' / ') :
                null
        ];
    }

    private function getCurrentWeekWorkingHours(User $user)
    {
        $shifts = $user->shifts
            ->filter(fn($s) => Carbon::parse($s->start)->gte(now()->startOfWeek()))
            ->map(function ($shift) use ($user) {
                $shift->user = $user;
                return $shift;
            });

        $totalHours = $shifts->map(function (Shift $s) {
            $stats = $s->accountableDuration();
            return $stats['workDuration'] - $stats['missingBreakDuration'];
        })->sum();

        $partialShifts = $user->shifts()
            ->where('end', '>=', now()->startOfWeek())
            ->where('start', '<', now()->startOfWeek())
            ->get();

        $partialShiftEntries = collect($partialShifts)->flatMap(
            fn($s) => $s->entries->filter(
                fn($e) => Carbon::parse($e->start)->gte(now()->startOfWeek())
            )
        );

        $totalHours += Shift::workDuration($partialShiftEntries) - $partialShiftEntries->sum('missingBreakDuration');

        $homeOfficeShifts = $shifts->filter(
            fn($s) => $s->entries->filter(
                fn($e) => !isset($e->is_home_office) || !$e->is_home_office
            )->count() == 0
        );

        $homeOfficeHours = $homeOfficeShifts->map(function (Shift $s) {
            $stats = $s->accountableDuration($s->entries->filter(
                fn($e) => isset($e->is_home_office) && $e->is_home_office
            ));
            return $stats['workDuration'] - $stats['missingBreakDuration'];
        })->sum();

        $otherHomeOfficeEntries = $shifts->filter(fn($s) => !$homeOfficeShifts->pluck('id')->contains($s->id))->flatMap->entries->filter(
            fn($e) => isset($e->is_home_office) && $e->is_home_office
        );

        $homeOfficeHours += Shift::workDuration($otherHomeOfficeEntries);

        return [
            'totalHours' => $totalHours,
            'homeOfficeHours' => $homeOfficeHours,
        ];
    }
}
