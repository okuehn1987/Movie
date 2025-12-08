import { Absence, AbsencePatch, AbsenceType, HomeOfficeDay, Relation, User, WorkLog, WorkLogPatch } from '@/types/types';

export type WorkLogPatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id' | 'comment'> & {
    log: Relation<'workLogPatch', 'log', 'id' | 'start' | 'end' | 'is_home_office'>;
    user: Relation<'workLogPatch', 'user', 'id' | 'first_name' | 'last_name'>;
};

export type WorkLogProp = Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'comment'> & {
    user: Relation<'workLog', 'user', 'id' | 'first_name' | 'last_name'>;
};

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

export type HomeOfficeDayProp = Pick<HomeOfficeDay, 'id' | 'user_id' | 'date' | 'home_office_day_generator_id'>;
