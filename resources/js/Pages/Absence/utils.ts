import { Absence, AbsencePatch, Canable, HomeOfficeDay, RelationPick, Status, User, Weekday } from '@/types/types';

export type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'absence_type_id' | 'user_id'> &
    RelationPick<'absence', 'absence_type', 'id' | 'abbreviation'> & {
        patches_exists: boolean;
        status: Status;
    } & Canable;

export type AbsencePatchProp = Pick<AbsencePatch, 'id' | 'start' | 'end' | 'absence_type_id' | 'user_id' | 'absence_id'> &
    RelationPick<'absencePatch', 'absence_type', 'id' | 'abbreviation'> & {
        log: Pick<Absence, 'id' | 'user_id'> & {
            patches_exists: boolean;
        };
        status: Status;
    } & Canable;
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id' | 'home_office'> &
    Canable &
    RelationPick<'user', 'user_working_weeks', 'id' | 'active_since' | Weekday> & {
        leaveDaysForYear: Record<string, number>;
        usedLeaveDaysForYear: Record<string, Record<Exclude<Status, 'declined'>, number>>;
    };

export type HomeOfficeDayProp = Pick<HomeOfficeDay, 'id' | 'user_id' | 'date' | 'status' | 'home_office_day_generator_id'> &
    RelationPick<'homeOfficeDay', 'home_office_day_generator', 'start' | 'end' | 'id' | 'user_id' | 'created_as_request'>;

export function getEntryState(entry: AbsenceProp | AbsencePatchProp) {
    if ('patches_exists' in entry && entry.patches_exists) return 'hasOpenPatch';
    if ('log' in entry && entry.log.patches_exists) return 'hasOpenPatch';
    if (entry.status === 'created') return 'created';
    if (entry.status === 'declined') return 'declined';
    return 'accepted';
}
