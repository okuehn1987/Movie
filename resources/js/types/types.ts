type Branded<T, Brand extends string> = T & { [x in `__${Brand}__`]: void };

export type DateString = Branded<string, 'date'>;
export type TimeString = Branded<string, 'time'>;
export type DateTimeString = Branded<string, 'dateTime'>;

export type Writeable<T> = { -readonly [P in keyof T]: T[P] };

export type Country = Branded<string, 'country'>;

export type FederalState = Branded<string, 'state'>;

export type CountryProp = { title: string; value: Country; regions: Record<FederalState, string> };

export type DBObject<Brand extends string> = {
    id: Branded<number, Brand>;
    created_at: DateTimeString;
    updated_at: DateTimeString;
};

type SoftDelete = {
    deleted_at: DateTimeString | null;
};

export type Model =
    | 'organization'
    | 'operatingSite'
    | 'group'
    | 'user'
    | 'timeAccountSetting'
    | 'absence'
    | 'absenceType'
    | 'operatingTime'
    | 'specialWorkingHoursFactors'
    | 'timeAccount'
    | 'timeAccountTransaction'
    | 'workLogPatch'
    | (string & NonNullable<unknown>);
export type CanMethod = 'viewIndex' | 'viewShow' | 'create' | 'update' | 'delete' | (string & NonNullable<unknown>);

export type Canable = {
    /**can the auth user execute this action in the current scope */
    can: Record<Model, Record<CanMethod, boolean>>;
};

export type Count<T extends Branded<Record<string, unknown>, string>> = T extends DBObject<infer B> ? { [x in B as `${x}s_count`]: number } : never;

export type Prettify<T> = {
    [K in keyof T]: T[K];
} & NonNullable<unknown>;

type _Tree<T, K extends string> = T & { [x in K]: _Tree<T, K>[] };
export type Tree<T, K extends string> = Prettify<_Tree<T, K>>;

export type Paginator<T> = {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    from: number;
    to: number;
    data: T[];
};

export type Weekday = 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' | 'sunday';

type Address = {
    street: string | null;
    house_number: string | null;
    address_suffix: string | null;
    country: Country;
    city: string | null;
    zip: string | null;
    federal_state: FederalState;
};

export type Status = 'created' | 'declined' | 'accepted';

export type User = DBObject<'user'> &
    SoftDelete &
    Address & {
        first_name: string;
        last_name: string;
        email: string;
        role: 'super-admin' | 'employee';
        password: string;
        operating_site_id: OperatingSite['id'];
        group_id: Group['id'] | null;
        supervisor_id: User['id'] | null;
        organization_id: Organization['id'] | null;
        staff_number: string | null;
        date_of_birth: DateString;
        phone_number: string | null;
        email_verified_at: string | null;
        weekly_working_hours: number;
        overtime_calculations_start: DateString;
        is_supervisor: boolean;
    } & (
        | {
              home_office: true;
              home_office_hours_per_week: number;
          }
        | {
              home_office: false;
              home_office_hours_per_week: null;
          }
    );

export type UserAppends = {
    readonly name: string;
};

export type UserWorkingHours = DBObject<'userWorkingHours'> &
    SoftDelete & {
        user_id: User['id'];
        weekly_working_hours: number;
        active_since: DateString;
    };

export type UserLeaveDays = DBObject<'userLeaveDays'> &
    SoftDelete & {
        user_id: User['id'];
        leave_days: number;
        active_since: DateString;
        type: 'annual' | 'remaining';
    };

export type Flag = 'auto_accept_travel_logs' | 'christmas_vacation_day' | 'new_year_vacation_day' | 'vacation_limitation_period' | 'night_surcharges';

export type Organization = DBObject<'organization'> &
    SoftDelete &
    Record<Flag, boolean> & {
        name: string;
        owner_id: User['id'] | null;
        tax_registration_id: string | null;
        commercial_registration_id: string | null;
        website: string | null;
        logo: string | null;
    };

export type OperatingSite = DBObject<'operatingSite'> &
    Address &
    SoftDelete & {
        name: string;
        email: string | null;
        phone_number: string | null;
        fax: string | null;
        organization_id: Organization['id'];
        is_headquarter: boolean;
    };

export type OperatingTime = DBObject<'operatingTime'> &
    SoftDelete & {
        type: Weekday;
        start: TimeString;
        end: TimeString;
        operating_site_id: OperatingSite['id'];
    };

export type Absence = DBObject<'absence'> &
    SoftDelete & {
        absence_type_id?: AbsenceType['id'];
        user_id: User['id'];
        start: DateString;
        end: DateString;
        status: Status;
    };

export type AbsenceType = DBObject<'absenceType'> &
    SoftDelete & {
        name: string;
        abbreviation: string;
        type:
            | 'Unbezahlter Urlaub'
            | 'Ausbildung/ Berufsschule'
            | 'Fort- und Weiterbildung'
            | 'AZV-Tag'
            | 'Bildungsurlaub'
            | 'Sonderurlaub'
            | 'Elternzeit'
            | 'Urlaub'
            | 'Andere';
        organization_id: Organization['id'];
    };

export type Group = DBObject<'group'> &
    SoftDelete & {
        name: string;
        organization_id: Organization['id'];
    };

export type SpecialWorkingHoursFactor = DBObject<'specialWorkingHoursFactor'> &
    SoftDelete & {
        organization_id: Organization['id'];
        type: Weekday | 'holiday' | 'nightshift';
        extra_charge: number;
    };

export type Substitute = DBObject<'substitute'> &
    SoftDelete & {
        user_id: User['id'];
        substitute_id: User['id'];
    };

export type TravelLog = DBObject<'travelLog'> &
    SoftDelete & {
        user_id: User['id'];
        start_location_id: User['id'] | OperatingSite['id'] | CustomAddress['id'];
        start_location_type: 'user' | 'operating_site' | 'custom_address';
        start: DateTimeString;
        end: DateTimeString;
        end_location_type: 'user' | 'operating_site' | 'custom_address';
        end_location_id: User['id'] | OperatingSite['id'] | CustomAddress['id'];
    };

export type CustomAddress = DBObject<'customAddress'> &
    SoftDelete &
    Address & {
        organization_id: Organization['id'];
    };

export type WorkLog = DBObject<'workLog'> &
    SoftDelete & {
        user_id: User['id'];
        start: DateTimeString;
        end: DateTimeString | null;
        is_home_office: boolean;
    };

export type WorkLogPatch = DBObject<'workLogPatch'> &
    SoftDelete & {
        user_id: User['id'];
        start: DateTimeString;
        is_home_office: boolean;
    } & {
        end: DateTimeString;
        status: Status;
        work_log_id: WorkLog['id'];
        accepted_at: DateTimeString;
        is_accounted: boolean;
        comment: string | null;
    };

export const TRUNCATION_CYCLES = [null, '1', '3', '6', '12'] as const;

export type TimeAccountSetting = DBObject<'timeAccountSetting'> &
    SoftDelete & {
        organization_id: Organization['id'];
        type: string | null;
        truncation_cycle_length_in_months: (typeof TRUNCATION_CYCLES)[number];
    };

export type TimeAccount = DBObject<'timeAccount'> &
    SoftDelete & {
        user_id: User['id'];
        balance: number;
        balance_limit: number;
        time_account_setting_id: TimeAccountSetting['id'];
        name: string;
    };

export type TimeAccountTransaction = DBObject<'timeAccountTransaction'> &
    SoftDelete & {
        from_id: TimeAccount['id'] | null;
        to_id: TimeAccount['id'] | null;
        modified_by: User['id'] | null;
        amount: number;
        description: string;
    };

export type UserWorkingWeek = DBObject<'userWorkingWeek'> &
    SoftDelete &
    Record<Weekday, boolean> & {
        user_id: User['id'];
        active_since: DateString;
    };

export type Notification = Omit<DBObject<'notification'>, 'id'> & {
    id: Branded<string, 'notification'>;
    notifiable_type: 'App\\Models\\User';
    notifiable_id: User['id'];
    read_at: DateTimeString;
} & (
        | {
              type: 'App\\Notifications\\PatchNotification';
              data: {
                  title: `${User['first_name']} ${User['last_name']} hat eine Zeitkorrektur beantragt.`;
                  patch_id: WorkLogPatch['id'];
              };
          }
        | {
              type: 'App\\Notifications\\AbsenceNotification';
              data: {
                  title: `${User['first_name']} ${User['last_name']} hat eine Abwesenheit beantragt.`;
                  absence_id: Absence['id'];
              };
          }
    );

export type PermissionValue = 'read' | 'write' | null;

export type Permission = {
    all:
        | 'user_permission'
        | 'workLogPatch_permission'
        | 'absence_permission'
        | 'timeAccount_permission'
        | 'timeAccountSetting_permission'
        | 'timeAccountTransaction_permission';
    organization: 'absenceType_permission' | 'specialWorkingHoursFactor_permission' | 'organization_permission';
    operatingSite: 'operatingSite_permission';
    group: 'group_permission';
};

export type UserPermission = {
    [key in keyof Permission]: { name: Permission[key]; label: string };
};

export type OrganizationUser = DBObject<'organizationUser'> &
    SoftDelete & {
        organization_id: Organization['id'];
        user_id: User['id'];
    } & Record<Permission['all' | 'group' | 'operatingSite' | 'organization'], PermissionValue>;

export type OperatingSiteUser = DBObject<'operatingSiteUser'> &
    SoftDelete & {
        operating_site_id: OperatingSite['id'];
        user_id: User['id'];
    } & Record<Permission['all' | 'operatingSite'], PermissionValue>;

export type GroupUser = DBObject<'groupUser'> &
    SoftDelete & {
        group_id: Group['id'];
        user_id: User['id'];
    } & Record<Permission['all' | 'group'], PermissionValue>;
