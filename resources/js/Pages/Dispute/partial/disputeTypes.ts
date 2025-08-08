import { Absence, AbsencePatch, AbsenceType, RelationPick, User, WorkLogPatch } from '@/types/types';

export type WorkLogPatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id' | 'comment'> &
    RelationPick<'workLogPatch', 'log', 'id' | 'start' | 'end' | 'is_home_office'> &
    RelationPick<'workLogPatch', 'user', 'id' | 'first_name' | 'last_name'>;

export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id' | 'operating_site_id'>;

export type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'>;

export type AbsencePatchProp = Pick<AbsencePatch, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id' | 'absence_id'> & {
    absence_type: Pick<AbsenceType, 'id' | 'name'>;
    usedDays: number;
    user: Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id' | 'operating_site_id'> & {
        usedLeaveDaysForYear: number;
        leaveDaysForYear: number;
    };
};
