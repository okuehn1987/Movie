type Date = string & { __date__: void };
type Time = string & { __time__: void };
type Timestamp = string & { __datetime__: void };

export type DBObject = {
    id: number;
    created_at: Timestamp;
    updated_at: Timestamp;
};

type SoftDelete = {
    deleted_at: Timestamp;
};

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

export type User = DBObject &
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
    } & Record<Permission, boolean>;

export type Notification = Omit<DBObject, 'id'> & {
    id: string;
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

export type UserWorkingHours = DBObject &
    SoftDelete & {
        user_id: User['id'];
        weekly_working_hours: number;
        active_since: Date;
    };

export type UserLeaveDays = DBObject &
    SoftDelete & {
        user_id: User['id'];
        leave_days: number;
    };

type Flags = {
    night_surcharges: boolean; // nachtzuschläge
    vacation_limitation_period: boolean; // verjährungsfrist bei urlaub
};

export type Organization = DBObject &
    SoftDelete &
    Flags & {
        name: string;
        owner_id: User['id'] | null;
        tax_registration_id: string | null;
        commercial_registration_id: string | null;
        website: string | null;
        logo: string | null;
    };

export type OperatingSite = DBObject &
    Address &
    SoftDelete & {
        name: string;
        email: string | null;
        phone_number: string | null;
        fax: string | null;
        organization_id: Organization['id'];
        is_head_quarter: boolean;
    };

export type OperatingTime = DBObject &
    SoftDelete & {
        type: Weekday;
        start: Time;
        end: Time;
        operating_site_id: OperatingSite['id'];
    };

export type Absence = DBObject &
    SoftDelete & {
        absence_type_id?: AbsenceType['id'];
        user_id: User['id'];
        start: Date;
        end: Date;
        status: Status;
    };

export type AbsenceType = DBObject &
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

export type Group = DBObject &
    SoftDelete & {
        name: string;
        organization_id: Organization['id'];
    };

export type SpecialWorkingHoursFactor = DBObject &
    SoftDelete & {
        organization_id: Organization['id'];
        type: Weekday | 'holiday' | 'nightshift';
        extra_charge: number;
    };

export type Substitute = DBObject &
    SoftDelete & {
        user_id: User['id'];
        substitute_id: User['id'];
    };

export type TravelLog = DBObject &
    SoftDelete & {
        user_id: User['id'];
        start_location_id: User['id'] | OperatingSite['id'] | CustomAddress['id'];
        start_location_type: 'user' | 'operating_site' | 'custom_address';
        start: Timestamp;
        end: Timestamp;
        end_location_type: 'user' | 'operating_site' | 'custom_address';
        end_location_id: User['id'] | OperatingSite['id'] | CustomAddress['id'];
    };

export type CustomAddress = DBObject &
    SoftDelete &
    Address & {
        organization_id: Organization['id'];
    };

export type WorkLog = DBObject &
    SoftDelete & {
        user_id: User['id'];
        start: Timestamp;
        end: Timestamp | null;
        is_home_office: boolean;
    };

export type WorkLogPatch = Omit<WorkLog, 'end'> & {
    end: Timestamp;
    status: Status;
    work_log_id: WorkLog['id'];
};

export type TimeAccountSetting = DBObject &
    SoftDelete & {
        organization_id: Organization['id'];
        type: string;
        truncation_cycle_length: 0 | 1 | 3 | 6 | 12;
    };

export type TimeAccount = DBObject &
    SoftDelete & {
        user_id: User['id'];
        balance: number;
        balance_limit: number;
        time_account_setting_id: TimeAccountSetting['id'];
    };

type Permission = 'work_log_patching' | 'user_administration';

export type UserPermission = { name: Permission; label: string };

export type UserWorkingWeek = DBObject &
    SoftDelete &
    Record<Weekday, boolean> & {
        user_id: User['id'];
        active_since: Date;
    };
