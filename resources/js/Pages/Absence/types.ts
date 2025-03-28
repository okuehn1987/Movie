import { Absence, AbsencePatch, Canable, RelationPick, User, Weekday } from '@/types/types';

export type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'absence_type_id' | 'user_id'> &
    RelationPick<'absence', 'absence_type', 'id' | 'abbreviation'> & {
        patches_exists: boolean;
        status: 'accepted' | 'created';
    };

export type AbsencePatchProp = Pick<AbsencePatch, 'id' | 'start' | 'end' | 'absence_type_id' | 'user_id' | 'absence_id'> &
    RelationPick<'absencePatch', 'absence_type', 'id' | 'abbreviation'> & {
        log: Pick<Absence, 'id' | 'user_id'> & {
            patches: AbsencePatch[];
        };
        status: 'accepted' | 'created';
    };
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'> &
    Canable &
    RelationPick<'user', 'user_working_weeks', 'id' | Weekday>;
