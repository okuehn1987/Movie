<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

trait ScopeInOrganization
{
    public static function scopeInOrganization(Builder $builder)
    {
        $org = Organization::getCurrent();
        if (!$org) return $builder;
        if (new self instanceof \App\Models\Absence || new self instanceof \App\Models\AbsencePatch) {
            return $builder->whereIn('absence_type_id', AbsenceType::select('id')->withTrashed()->inOrganization());
        }
        if (new self instanceof \App\Models\OperatingTime || new self instanceof \App\Models\User) {
            return  $builder->whereIn(
                'operating_site_id',
                OperatingSite::select('id')->inOrganization()
            );
        }
        if (
            new self instanceof \App\Models\TravelLog || new self instanceof \App\Models\TravelLogPatch ||
            new self instanceof \App\Models\WorkLog || new self instanceof \App\Models\WorkLogPatch ||
            new self instanceof \App\Models\TimeAccount || new self instanceof \App\Models\Ticket ||
            new self instanceof \App\Models\HomeOfficeDay || new self instanceof \App\Models\HomeOfficeDayGenerator

        ) {
            return $builder->whereIn('user_id', User::select('id')->inOrganization());
        }
        if (
            new self instanceof \App\Models\OperatingSite || new self instanceof \App\Models\AbsenceType ||
            new self instanceof \App\Models\Group ||  new self instanceof \App\Models\SpecialWorkingHoursFactor ||
            new self instanceof \App\Models\TimeAccountSetting || new self instanceof \App\Models\Customer ||
            new self instanceof \App\Models\ChatAssistant || new self instanceof \App\Models\ChatFile
        ) {
            return $builder->where('organization_id', $org->id);
        }
        if (
            new self instanceof \App\Models\CustomerOperatingSite || new self instanceof \App\Models\CustomerNoteFolder ||
            new self instanceof \App\Models\Ticket
        ) {
            return $builder->whereIn('customer_id', Customer::select('id')->inOrganization());
        }
        if (
            new self instanceof \App\Models\Chat
        ) {
            return $builder->whereIn('chat_assistant_id', ChatAssistant::select('id')->inOrganization());
        }
        if (
            new self instanceof \App\Models\ChatMessage
        ) {
            return $builder->whereIn('chat_id', Chat::select('id')->inOrganization());
        if (new self instanceof \App\Models\CustomerNoteEntry) {
            return $builder->whereIn('customer_note_folder_id', CustomerNoteFolder::select('id')->inOrganization());
        }
        abort(501);
    }
}
