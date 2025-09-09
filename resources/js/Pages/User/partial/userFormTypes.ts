import {
    Country,
    DateString,
    Group,
    GroupUser,
    OperatingSite,
    OperatingSiteUser,
    OrganizationUser,
    Permission,
    RelationPick,
    Relations,
    User,
    UserLeaveDays,
    UserWorkingHours,
    UserWorkingWeek,
    Weekday,
} from '@/types/types';

export type UserProp = User &
    RelationPick<'user', 'supervisor', 'id'> &
    Pick<Relations<'user'>, 'organization_user' | 'operating_site_user' | 'group_user' | 'user_working_weeks' | 'user_leave_days'> & {
        user_working_hours?: Relations<'user'>['user_working_hours'];
    };

export type FormData = {
    first_name: string;
    last_name: string;
    email: string;
    date_of_birth: null | string;
    city: string;
    zip: string;
    street: string;
    house_number: string;
    address_suffix: string;
    country: Country;
    federal_state: string;
    phone_number: string;
    staff_number: null | string;
    job_role: null | string;
    password: string;
    group_id: null | Group['id'];
    operating_site_id: null | OperatingSite['id'];
    supervisor_id: null | User['id'];
    is_supervisor: boolean;
    home_office: boolean;
    home_office_hours_per_week: null | number; //TODO: check if we need active_since

    user_working_hours: (Pick<UserWorkingHours, 'weekly_working_hours'> & { active_since: string; id: UserWorkingHours['id'] | null })[];

    user_leave_days: (Pick<UserLeaveDays, 'leave_days'> & { active_since: string; id: UserLeaveDays['id'] | null })[];

    user_working_weeks: { id: UserWorkingWeek['id'] | null; active_since: string; weekdays: Weekday[] }[];
    initialRemainingLeaveDays: 0;

    overtime_calculations_start: DateString;
    organizationUser: Pick<OrganizationUser, Permission[keyof Permission]>;
    groupUser: Pick<GroupUser, Permission['all' | 'group']>;
    operatingSiteUser: Pick<OperatingSiteUser, Permission['all' | 'operatingSite']>;
};
