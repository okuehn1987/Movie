import { Absence, AbsencePatch, Canable, RelationPick, User, Weekday } from '@/types/types';

export type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'absence_type_id' | 'user_id'> &
    RelationPick<'absence', 'absence_type', 'id' | 'abbreviation'> & {
        patches_exists: boolean;
        status: 'accepted' | 'created';
    } & Canable;

export type AbsencePatchProp = Pick<AbsencePatch, 'id' | 'start' | 'end' | 'absence_type_id' | 'user_id' | 'absence_id'> &
    RelationPick<'absencePatch', 'absence_type', 'id' | 'abbreviation'> & {
        log: Pick<Absence, 'id' | 'user_id'> & {
            patches_exists: boolean;
        };
        status: 'accepted' | 'created';
    } & Canable;
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'> &
    Canable &
    RelationPick<'user', 'user_working_weeks', 'id' | 'active_since' | Weekday> & { leaveDaysForYear: number; usedLeaveDaysForYear: number };

export function getEntryState(entry: AbsenceProp | AbsencePatchProp) {
    if ('patches_exists' in entry && entry.patches_exists) return 'hasOpenPatch';
    if ('log' in entry && entry.log.patches_exists) return 'hasOpenPatch';
    if (entry.status === 'created') return 'created';
    return 'accepted';
}
