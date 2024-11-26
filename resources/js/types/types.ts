type Date = string & { __date__: void };
type Time = string & { __time__: void };
type Timestamp = string & { __datetime__: void };

type Branded<T, Brand extends string> = T & { [x in `__${Brand}__`]: void };

export type DBObject<Brand extends string> = {
    id: Branded<number, Brand>;
    created_at: Timestamp;
    updated_at: Timestamp;
};

type SoftDelete = {
    deleted_at: Timestamp;
};

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
    country: string | null;
    city: string | null;
    zip: string | null;
    federal_state: string | null;
};

export type Status = 'created' | 'declined' | 'accepted';

export type User = DBObject<'User'> &
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
        staff_number: number | null;
        date_of_birth: Date;
        home_office: boolean;
        home_office_ratio: number | null;
        phone_number: string | null;
        email_verified_at: string | null;
        weekly_working_hours: number;
        is_supervisor: boolean;
    };

export type UserWorkingHours = DBObject<'UserWorkingHours'> &
    SoftDelete & {
        user_id: User['id'];
        weekly_working_hours: number;
        active_since: Date;
    };

export type UserLeaveDays = DBObject<'UserLeaveDays'> &
    SoftDelete & {
        user_id: User['id'];
        leave_days: number;
    };

type Flags = {
    night_surcharges: boolean; // nachtzuschläge
    vacation_limitation_period: boolean; // verjährungsfrist bei urlaub
};

export type Organization = DBObject<'Organization'> &
    SoftDelete &
    Flags & {
        name: string;
        owner_id: User['id'] | null;
        tax_registration_id: string | null;
        commercial_registration_id: string | null;
        website: string | null;
        logo: string | null;
    };

export type OperatingSite = DBObject<'OperatingSite'> &
    Address &
    SoftDelete & {
        name: string;
        email: string | null;
        phone_number: string | null;
        fax: string | null;
        organization_id: Organization['id'];
        is_head_quarter: boolean;
    };

export type OperatingTime = DBObject<'OperatingTime'> &
    SoftDelete & {
        type: Weekday;
        start: Time;
        end: Time;
        operating_site_id: OperatingSite['id'];
    };

export type Absence = DBObject<'Absence'> &
    SoftDelete & {
        absence_type_id?: AbsenceType['id'];
        user_id: User['id'];
        start: Date;
        end: Date;
        status: Status;
    };

export type AbsenceType = DBObject<'AbsenceType'> &
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

export type Group = DBObject<'Group'> &
    SoftDelete & {
        name: string;
        organization_id: Organization['id'];
    };

export type SpecialWorkingHoursFactor = DBObject<'SpecialWorkingHoursFactor'> &
    SoftDelete & {
        organization_id: Organization['id'];
        type: Weekday | 'holiday' | 'nightshift';
        extra_charge: number;
    };

export type Substitute = DBObject<'Substitute'> &
    SoftDelete & {
        user_id: User['id'];
        substitute_id: User['id'];
    };

export type TravelLog = DBObject<'TravelLog'> &
    SoftDelete & {
        user_id: User['id'];
        start_location_id: User['id'] | OperatingSite['id'] | CustomAddress['id'];
        start_location_type: 'user' | 'operating_site' | 'custom_address';
        start: Timestamp;
        end: Timestamp;
        end_location_type: 'user' | 'operating_site' | 'custom_address';
        end_location_id: User['id'] | OperatingSite['id'] | CustomAddress['id'];
    };

export type CustomAddress = DBObject<'CustomAddress'> &
    SoftDelete &
    Address & {
        organization_id: Organization['id'];
    };

export type WorkLog = DBObject<'WorkLog'> &
    SoftDelete & {
        user_id: User['id'];
        start: Timestamp;
        end: Timestamp | null;
        is_home_office: boolean;
    };

export type WorkLogPatch = DBObject<'WorkLogPatch'> &
    SoftDelete & {
        user_id: User['id'];
        start: Timestamp;
        is_home_office: boolean;
    } & {
        end: Timestamp;
        status: Status;
        work_log_id: WorkLog['id'];
    };

export const TRUNCATION_CYCLES = [null, '1', '3', '6', '12'] as const;

export type TimeAccountSetting = DBObject<'TimeAccountSetting'> &
    SoftDelete & {
        organization_id: Organization['id'];
        type: string | null;
        truncation_cycle_length_in_months: (typeof TRUNCATION_CYCLES)[number];
    };

export type TimeAccount = DBObject<'TimeAccount'> &
    SoftDelete & {
        user_id: User['id'];
        balance: number;
        balance_limit: number;
        time_account_setting_id: TimeAccountSetting['id'];
        name: string;
    };

export type TimeAccountTransaction = DBObject<'TimeAccountTransaction'> &
    SoftDelete & {
        from_id: TimeAccount['id'] | null;
        to_id: TimeAccount['id'] | null;
        modified_by: User['id'] | null;
        amount: number;
        description: string;
    };

export type UserWorkingWeek = DBObject<'UserWorkingWeek'> &
    SoftDelete &
    Record<Weekday, boolean> & {
        user_id: User['id'];
        active_since: Date;
    };

export type Notification = Omit<DBObject<'Notification'>, 'id'> & {
    id: Branded<string, 'Notification'>;
    notifiable_type: 'App\\Models\\User';
    notifiable_id: User['id'];
    read_at: Timestamp;
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

type PermissionValue = 'read' | 'write' | null;

type Permission = {
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

export type OrganizationUser = DBObject<'OrganizationUser'> &
    SoftDelete & {
        organization_id: Organization['id'];
        user_id: User['id'];
    } & Record<Permission['all'] & Permission['organization'] & Permission['group'] & Permission['operatingSite'], PermissionValue>;

export type OperatingSiteUser = DBObject<'OperatingSiteUser'> &
    SoftDelete & {
        operating_site_id: OperatingSite['id'];
        user_id: User['id'];
    } & Record<Permission['all'] & Permission['operatingSite'], PermissionValue>;

export type GroupUser = DBObject<'GroupUser'> &
    SoftDelete & {
        group_id: Group['id'];
        user_id: User['id'];
    } & Record<Permission['all'] & Permission['group'], PermissionValue>;
