type DBObject = {
    id: number;
    created_at: string;
    updated_at: string;
};

type SoftDelete = {
    deleted_at: string;
};

export type Paginator<T> = {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string;
    prev_page_url: string | null;
    path: string;
    from: number;
    to: number;
    data: T;
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
        operating_site_id: number;
        group_id: Group['id'] | null;
        supervisor_id: User['id'];
        organization_id: Organization['id'];
        staff_number: number;
        date_of_birth: string;
        home_office: boolean;
        home_office_ratio: number | null;
        phone_number: string | null;
        email_verified_at: string;
        notificationCount: number;
    } & Record<Permission, boolean>;

export type UserWorkingHours = DBObject &
    SoftDelete & {
        user_id: User['id'];
        weekly_working_hours: number;
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
        owner_id: User['id'];
        tax_registration_id: string | null;
        commercial_registration_id: string | null;
        website: string | null;
        logo: string | null;
    };

export type OperatingSite = DBObject &
    Address &
    SoftDelete & {
        name: string | null;
        email: string | null;
        phone_number: string | null;
        fax: string | null;
        organization_id: Organization['id'];
        is_head_quarter: boolean;
    };

export type OperatingTime = DBObject &
    SoftDelete & {
        type: Weekday;
        start: string;
        end: string;
        operating_site_id: OperatingSite['id'];
    };

export type Absence = DBObject &
    SoftDelete & {
        absence_type_id: AbsenceType['id'];
        user_id: User['id'];
        start: string;
        end: string;
        date: string;
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
        start_location: User['id'] | OperatingSite['id'] | CustomAddress['id'];
        start_time: string;
        end_time: string;
        end_location: User['id'] | OperatingSite['id'] | CustomAddress['id'];
    };

export type CustomAddress = DBObject &
    SoftDelete &
    Address & {
        organization_id: Organization['id'];
    };

export type WorkLog = DBObject &
    SoftDelete & {
        user_id: User['id'];
        start: string;
        end: string | null;
        is_home_office: boolean;
    };

export type WorkLogPatch = Omit<WorkLog, 'end'> & {
    end: string;
    status: Status;
    work_log_id: WorkLog['id'];
};

type Permission = 'work_log_patching';

export type UserPermission = { name: Permission; label: string };
