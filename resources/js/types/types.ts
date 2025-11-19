type Branded<T, Brand extends string> = T & { [x in `__${Brand}__`]: void };

export type DateString = Branded<string, 'date'>;
export type TimeString = Branded<string, 'time'>;
export type DateTimeString = Branded<string, 'dateTime'>;
export type Seconds = number;

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
    | 'customer'
    | 'group'
    | 'user'
    | 'ticket'
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

type ExtractBrand<T extends Branded<Record<string, unknown>, string>> = T extends DBObject<infer B> ? B : never;

/**
 * Count<T> adds the count of a related model
 *
 * @example
 * ```ts
 * type props = Organization & Count<User> // Organization & { users_count: number }
 * ```
 * */
export type Count<T extends Branded<unknown, string>> = { [x in ExtractBrand<T> as `${x}s_count`]: number };

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

export type AdressKeys = 'street' | 'house_number' | 'address_suffix' | 'country' | 'city' | 'zip' | 'federal_state';
export type Address = DBObject<'address'> & {
    street: string | null;
    house_number: string | null;
    address_suffix: string | null;
    country: Country | null;
    city: string | null;
    zip: string | null;
    federal_state: FederalState | null;
} & (
        | { addressable_type: 'App\\Models\\User'; addressable_id: User['id'] }
        | { addressable_type: 'App\\Models\\OperatingSite'; addressable_id: OperatingSite['id'] }
        | { addressable_type: 'App\\Models\\CustomAddress'; addressable_id: CustomAddress['id'] }
        | { addressable_type: 'App\\Models\\Customer'; addressable_id: Customer['id'] }
    );

export type Status = 'created' | 'declined' | 'accepted';

export type User = DBObject<'user'> &
    SoftDelete & {
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
        resignation_date: DateString | null;
        job_role: string | null;
        time_balance_yellow_threshold: number | null;
        time_balance_red_threshold: number | null;
        notification_channels: ('database' | 'mail')[];
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

export type Customer = DBObject<'customer'> &
    SoftDelete & {
        name: string;
        email: string | null;
        phone: string | null;
        reference_number: string | null;
        organization_id: Organization['id'];
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
        isa_active: boolean;
    };

export type OperatingSite = DBObject<'operatingSite'> &
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

export type CustomAddress = DBObject<'customAddress'> &
    SoftDelete & {
        organization_id: Organization['id'];
    };

export type Shift = DBObject<'shift'> & {
    user_id: User['id'];
    start: DateTimeString;
    end: DateTimeString;
};

type BaseLog = {
    user_id: User['id'];
    status: Status;
    accepted_at: DateTimeString;
    comment: string | null;
};

export type WorkLog = DBObject<'workLog'> &
    Omit<BaseLog, 'end'> &
    SoftDelete & {
        start: DateTimeString;
        end: DateTimeString | null;
        is_home_office: boolean;
        //** shift_id is null for all non accepted workLogs */
        shift_id: Shift['id'] | null;
    };

export type WorkLogPatch = DBObject<'workLogPatch'> &
    BaseLog &
    SoftDelete & {
        is_home_office: boolean;
        start: DateTimeString;
        end: DateTimeString;
        work_log_id: WorkLog['id'];
    };

export type TravelLog = DBObject<'travelLog'> &
    BaseLog &
    SoftDelete & {
        start: DateTimeString;
        end: DateTimeString;
        from_id: Address['id'];
        to_id: Address['id'];
    };
export type TravelLogPatch = DBObject<'travelLogPatch'> &
    BaseLog &
    SoftDelete & {
        start: DateTimeString;
        end: DateTimeString;
        travel_log_id: TravelLog['id'];
        from_id: Address['id'];
        to_id: Address['id'];
    };

export type ShiftEntries = WorkLog | WorkLogPatch | TravelLog | TravelLogPatch;

export type Absence = DBObject<'absence'> &
    BaseLog &
    SoftDelete & {
        absence_type_id?: AbsenceType['id'];
        start: DateString;
        end: DateString;
    };
export type AbsencePatch = DBObject<'absencePatch'> &
    BaseLog &
    SoftDelete & {
        start: DateString;
        end: DateString;
        absence_id: Absence['id'];
        absence_type_id?: AbsenceType['id'];
    };

export type AbsenceType = DBObject<'absenceType'> &
    SoftDelete & {
        name: string;
        abbreviation: string;
        type:
            | 'Unbezahlter Urlaub'
            | 'Abbau Gleitzeitkonto'
            | 'Ausbildung/ Berufsschule'
            | 'Fort- und Weiterbildung'
            | 'AZV-Tag'
            | 'Bildungsurlaub'
            | 'Sonderurlaub'
            | 'Elternzeit'
            | 'Urlaub'
            | 'Andere';
        requires_approval: boolean;
        organization_id: Organization['id'];
    };

export const DATETIME_LOCAL_FORMAT = "yyyy-MM-dd'T'HH:mm:ss";
export const TRUNCATION_CYCLES = [null, '1', '3', '6', '12'] as const;
export const PRIORITIES = [
    { value: 'highest', title: 'Höchste', priorityValue: 1, icon: 'mdi-chevron-double-up', color: 'red-darken-4' },
    { value: 'high', title: 'Hoch', priorityValue: 2, icon: 'mdi-chevron-up', color: 'orange-darken-4' },
    { value: 'medium', title: 'Mittel', priorityValue: 3, icon: 'mdi-equal', color: 'orange-lighten-1' },
    { value: 'low', title: 'Niedrig', priorityValue: 4, icon: 'mdi-chevron-down', color: 'blue-lighten-2' },
    { value: 'lowest', title: 'Niedrigste', priorityValue: 5, icon: 'mdi-chevron-double-down', color: 'blue-darken-2' },
] as const;

export type TimeAccountSetting = DBObject<'timeAccountSetting'> &
    SoftDelete & {
        organization_id: Organization['id'];
        type: string | null;
        truncation_cycle_length_in_months: (typeof TRUNCATION_CYCLES)[number];
    };

export type TimeAccount = DBObject<'timeAccount'> &
    SoftDelete & {
        user_id: User['id'];
        balance: Seconds;
        balance_limit: Seconds;
        time_account_setting_id: TimeAccountSetting['id'];
        name: string;
    };

export type TimeAccountTransaction = DBObject<'timeAccountTransaction'> &
    SoftDelete & {
        from_id: TimeAccount['id'] | null;
        from_previous_balance: TimeAccount['balance'] | null;
        to_id: TimeAccount['id'] | null;
        to_previous_balance: TimeAccount['balance'] | null;
        modified_by: User['id'] | null;
        amount: Seconds;
        description: string;
    };

export type UserAbsenceFilter = DBObject<'userAbcenceFilter'> & {
    user_id: User['id'];
    name: string;
    data: {
        version: 'v1';
        absence_type_ids: AbsenceType['id'][];
        user_ids: User['id'][];
        statuses: Status[];
    };
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
    read_at: DateTimeString | null;
} & (
        | {
              type: 'App\\Notifications\\WorkLogNotification';
              data: {
                  title: string;
                  work_log_id: WorkLog['id'];
                  status: Status;
              };
          }
        | {
              type: 'App\\Notifications\\WorkLogPatchNotification';
              data: {
                  title: string;
                  work_log_patch_id: WorkLogPatch['id'];
                  status: Status;
              };
          }
        | {
              type: 'App\\Notifications\\AbsenceNotification';
              data: {
                  title: string;
                  absence_id: Absence['id'];
                  status: Status;
              };
          }
        | {
              type: 'App\\Notifications\\AbsencePatchNotification';
              data: {
                  title: string;
                  absence_patch_id: AbsencePatch['id'];
                  status: Status;
              };
          }
        | {
              type: 'App\\Notifications\\AbsenceDeleteNotification';
              data: {
                  title: string;
                  absence_id: Absence['id'];
                  status: Status;
              };
          }
        | {
              type: 'App\\Notifications\\TicketCreationNotification';
              data: {
                  title: string;
                  ticket_id: Ticket['id'];
              };
          }
        | {
              type: 'App\\Notifications\\TicketUpdateNotification';
              data: {
                  title: string;
                  ticket_id: Ticket['id'];
              };
          }
        | {
              type: 'App\\Notifications\\TicketDeletionNotification';
              data: {
                  title: string;
                  ticket_id: Ticket['id'];
              };
          }
        | {
              type: 'App\\Notifications\\TicketFinishNotification';
              data: {
                  title: string;
                  ticket_id: Ticket['id'];
              };
          }
        | {
              type: 'App\\Notifications\\TicketRecordCreationNotification';
              data: {
                  title: string;
                  ticket_id: Ticket['id'];
              };
          }
        | {
              type: 'App\\Notifications\\DisputeStatusNotification';
              data: {
                  title: string;
                  type: 'delete' | 'create';
              } & (
                  | {
                        log_id: Absence['id'];
                        log_model: 'App\\Models\\Absence';
                    }
                  | {
                        log_id: AbsencePatch['id'];
                        log_model: 'App\\Models\\AbsencePatch';
                    }
                  | {
                        log_id: WorkLogPatch['id'];
                        log_model: 'App\\Models\\WorkLogPatch';
                    }
                  | {
                        log_id: WorkLog['id'];
                        log_model: 'App\\Models\\WorkLog';
                    }
              );
          }
    );

export type Ticket = DBObject<'ticket'> & {
    title: string;
    description: string | null;
    priority: (typeof PRIORITIES)[number]['value'];
    customer_id: Customer['id'];
    user_id: User['id'];
    accounted_at: DateTimeString | null;
    finished_at: DateTimeString | null;
    reference_prefix: string;
    readonly reference_number: string;
};

export type TicketRecord = DBObject<'record'> & {
    ticket_id: Ticket['id'];
    start: DateTimeString;
    duration: number;
    description: string | null;
    resources: string | null;
    accounted_at: DateTimeString | null;
};

export type PermissionValue = 'read' | 'write' | null;

export type Permission = {
    all:
        | 'user_permission'
        | 'workLog_permission'
        | 'workLogPatch_permission'
        | 'absence_permission'
        | 'timeAccount_permission'
        | 'timeAccountSetting_permission'
        | 'timeAccountTransaction_permission'
        | 'ticket_permission'
        | 'absenceType_permission';
    organization:
        | 'specialWorkingHoursFactor_permission'
        | 'organization_permission'
        | 'customer_permission'
        | 'chatAssistant_permission'
        | 'chatFile_permission'
        | 'isaPayment_permission';
    operatingSite: 'operatingSite_permission';
    group: 'group_permission';
};

export type UserPermission = {
    [key in keyof Permission]: { name: Permission[key]; label: string; except?: PermissionValue };
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

export type AppModule = DBObject<'appModule'> & {
    organization_id: Organization['id'];
    module: 'herta' | 'timesheets';
    activated_at: DateTimeString | null;
};

export type CustomerOperatingSite = DBObject<'customerOperatingSite'> & {
    customer_id: Customer['id'];
    name: string;
};

export type JSON = string | number | boolean | null | { [x: string]: JSON } | JSON[];

export type CustomerNote = DBObject<'customerNote'> & {
    customer_id: Customer['id'];
    modified_by: User['id'];
    parent_id: CustomerNote['id'] | null;
    type: 'complex' | 'primitive' | 'file';
    key: string | null;
    value: string;
    file: File | null;
};
export type TicketUser = DBObject<'ticketUser'> & {
    ticket_id: Ticket['id'];
    user_id: User['id'];
};

export type ChatAssistant = Prettify<
    DBObject<'chatAssistant'> &
        SoftDelete & {
            vector_store_id: string;
            organization_id: Organization['id'];
            monthly_cost_limit: number;
        }
>;

export type Chat = Prettify<
    DBObject<'chat'> &
        SoftDelete & {
            chat_assistant_id: ChatAssistant['id'];
            assistant_api_thread_id: string;
            open_ai_tokens_used: number;
            user_id: User['id'];
            last_response_id: string | null;
        }
>;

export type ChatMessage = Prettify<
    DBObject<'chatMessage'> &
        SoftDelete & {
            msg: string;
            role: 'user' | 'assistant' | 'system' | 'annotation';
            chat_id: Chat['id'];
            assistant_api_message_id: string;
            open_ai_tokens_used: number;
        }
>;

export type ChatFile = Prettify<
    DBObject<'chatFile'> &
        SoftDelete & {
            name: string;
            file_name: string;
            assistant_api_file_id: string;
            chat_assistant_id: ChatAssistant['id'] | null;
            organization_id: Organization['id'];
        }
>;

export type Year = string & { __year__: void };
export type Month = 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 | 12;
export type Minutes = number & { __minutes__: void };
export type MonthStats = {
    month: Month;
    year: Year;
    chat_cost: number;
    chats: number;
};

export type RelationMap = {
    absence: {
        absence_type?: AbsenceType;
        user: User;
        patches: AbsencePatch[];
        current_accepted_patch: AbsencePatch | null;
        latest_patch: AbsencePatch | null;
    };
    absencePatch: {
        absence: Absence;
        absence_type?: AbsenceType;
        user: User;
        log: Absence;
    };
    absenceType: {
        organization: Organization;
        absences: Absence[];
    };
    address: {
        addressable: User | OperatingSite | CustomAddress;
    };
    chat: {
        chat_assistant: ChatAssistant;
        user: User;
        chat_messages: ChatMessage[];
        latest_chat_message: ChatMessage | null;
    };
    chatAssistant: {
        organization: Organization;
        chats: Chat[];
        chat_messages: ChatMessage[];
        chat_files: ChatFile[];
    };
    chatFile: {
        organization: Organization;
        chat_assistant: ChatAssistant | null;
    };
    chatMessage: {
        chat: Chat;
    };
    customAddress: {
        organization: Organization;
        addresses: Address[];
        current_address: Address;
    };
    customer: {
        organization: Organization;
        tickets: Ticket[];
        customer_operating_sites: CustomerOperatingSite[];
        customer_notes: CustomerNote[];
    };
    customerNote: {
        customer: Customer;
        modified_by: User;
        parent?: CustomerNote;
    };
    customerOperatingSite: {
        customer: Customer;
        addresses: Address[];
        current_address: Address;
    };
    group: {
        organization: Organization;
        users: User[];
        group_users: GroupUser[];
    };
    groupUser: {
        group: Group;
        user: User;
    };
    operatingSite: {
        organization: Organization;
        users: User[];
        operating_site_users: OperatingSiteUser[];
        operating_times: OperatingTime[];
        addresses: (Omit<Address, 'country' | 'federal_state'> & { country: Country; federal_state: FederalState })[];
        current_address: Omit<Address, 'country' | 'federal_state'> & { country: Country; federal_state: FederalState };
    };
    operatingSiteUser: {
        operatings_site: OperatingSite;
        user: User;
    };
    operatingTime: {
        operating_site: OperatingSite;
    };
    organization: {
        customers: Customer[];
        operating_sites: OperatingSite[];
        operating_site_users: OperatingSiteUser[];
        users: User[];
        organization_users: OrganizationUser[];
        absence_types: AbsenceType[];
        groups: Group[];
        group_users: GroupUser[];
        special_working_hours_factors: SpecialWorkingHoursFactor[];
        time_account_settings: TimeAccountSetting[];
        owner: User;
        custom_addresses: CustomAddress[];
        modules: AppModule[];
    };
    organizationUser: {
        organization: Organization;
        user: User;
    };
    shift: {
        user: User;
        work_logs: WorkLog[];
        work_log_patches: WorkLogPatch[];
        travel_logs: TravelLog[];
        travel_log_patches: TravelLogPatch[];
    };
    specialWorkingHoursFactor: {
        organization: Organization;
    };
    ticket: {
        customer: Customer;
        user: User;
        assignees: (User & {
            pivot: TicketUser;
        })[];
        records: TicketRecord[];
    };
    ticketRecord: {
        ticket: Ticket;
        user: User;
    };
    timeAccount: {
        user: User;
        to_transactions: TimeAccountTransaction[];
        from_transactions: TimeAccountTransaction[];
        time_account_setting: TimeAccountSetting;
    };
    timeAccountSetting: {
        organization: Organization;
        time_accounts: TimeAccount[];
    };
    timeAccountTransaction: {
        from: TimeAccount | null;
        to: TimeAccount | null;
        user: User | null;
    };
    travelLog: {
        user: User;
        patches: TravelLogPatch[];
        current_accepted_patch: TravelLogPatch | null;
        latest_patch: TravelLogPatch | null;
        addresses: Address[];
        current_address: Address;
        from_address: Address;
        to_address: Address;
    };
    travelLogPatch: {
        user: User;
        log: TravelLog;
        addresses: Address[];
        current_address: Address;
        from_address: Address;
        to_address: Address;
    };
    user: {
        shifts: Shift[];
        current_shift: Shift | null;
        work_logs: WorkLog[];
        work_log_patches: WorkLogPatch[];
        travel_logs: TravelLog[];
        travel_log_patches: TravelLogPatch[];
        absences: Absence[];
        absence_patches: AbsencePatch[];
        supervisor: User;
        supervisees: User[];
        is_substituted_by: User[];
        is_substitution_for: User[];
        group: Group | null;
        group_user: GroupUser | null;
        operating_site: OperatingSite;
        operating_site_user: OperatingSiteUser;
        organization: Organization;
        organization_user: OrganizationUser;
        owns: Organization | null;
        user_working_hours: UserWorkingHours[];
        current_working_hours: UserWorkingHours | null;
        user_leave_days: UserLeaveDays[];
        current_leave_days: UserLeaveDays | null;
        user_working_weeks: UserWorkingWeek[];
        current_working_week: UserWorkingWeek | null;
        time_accounts: TimeAccount[];
        default_time_account: TimeAccount;
        latest_work_log: WorkLog | null;
        notifications: Notification[];
        read_notifications: Notification[];
        unread_notifications: Notification[];
        user_absence_filters: UserAbsenceFilter[];
        current_address: Address;
        addresses: Address[];
        tickets: (Ticket & { pivot: TicketUser })[];
    };
    userAbsenceFilter: {
        user: User;
        addresses: Address[];
        current_address: Address;
        tickets: (Ticket & { pivot: TicketUser })[];
    };
    userLeaveDays: {
        user: User;
    };
    userWorkingHours: {
        user: User;
    };
    userWorkingWeek: {
        user: User;
    };
    workLog: {
        user: User;
        shift: Shift | null;
        patches: WorkLogPatch[];
        current_accepted_patch: WorkLogPatch | null;
        latest_patch: WorkLogPatch | null;
    };
    workLogPatch: {
        user: User;
        log: WorkLog;
    };
};

/** returns all relations of the giving model */
export type Relations<TModel extends keyof RelationMap> = TModel extends keyof RelationMap ? RelationMap[TModel] : never;

/**
 * returns the relation object with picked keys
 *
 * @param TModel - the base model
 * @param TRelation - the relation of the base model
 * @param TKeys - the keys to pick from the relation
 * @param TOptional - if the relation is optional
 * @param TExtends - the type to extend the related object with
 *
 * @example
 * ```ts
 * type prop = WorkLog & RelationPick<'workLog', 'shift', 'id' | 'start'>
 * // WorkLog & { shift: Pick<Shift, 'id' | 'start'> }
 * ```
 *
 * @example
 * ```ts
 * type prop = WorkLog & RelationPick<'workLog', 'shift', 'id' | 'start', true>
 * // WorkLog & { shift?: Pick<Shift, 'id' | 'start'> | undefined }
 * ```
 *
 * @example
 * ```ts
 * type prop = WorkLog & RelationPick<'workLog', 'shift', 'id' | 'start', false , { user : User }>
 * // WorkLog & { shift: Pick<Shift, 'id' | 'start'> & { user: User } }
 * ```
 *  */
export type RelationPick<
    TModel extends keyof RelationMap,
    TRelation extends keyof RelationMap[TModel],
    TKeys extends keyof UnArray<NonNullable<RelationMap[TModel][TRelation]>>,
> = Prettify<{
    [x in TRelation]: RelationMap[TModel][TRelation] extends Array<unknown>
        ? Pick<UnArray<NonNullable<RelationMap[TModel][TRelation]>>, TKeys>[] | (null extends RelationMap[TModel][TRelation] ? null : never)
        : Pick<UnArray<NonNullable<RelationMap[TModel][TRelation]>>, TKeys> | (null extends RelationMap[TModel][TRelation] ? null : never);
}>;
type UnArray<T> = T extends Array<infer U> ? U : T;

export type Relation<
    TModel extends keyof RelationMap,
    TRelation extends keyof RelationMap[TModel],
    TKeys extends keyof UnArray<NonNullable<RelationMap[TModel][TRelation]>>,
> = Prettify<{
    [x in TRelation]: RelationMap[TModel][TRelation] extends Array<unknown>
        ? Pick<UnArray<NonNullable<RelationMap[TModel][TRelation]>>, TKeys>[] | (null extends RelationMap[TModel][TRelation] ? null : never)
        : Pick<UnArray<NonNullable<RelationMap[TModel][TRelation]>>, TKeys> | (null extends RelationMap[TModel][TRelation] ? null : never);
}>[TRelation];
