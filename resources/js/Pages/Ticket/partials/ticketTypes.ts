import { Address, Canable, Customer, CustomerOperatingSite, OperatingSite, Relations, Ticket, TicketRecord, User } from '@/types/types';

export type TicketProp = Ticket &
    Canable &
    Pick<Relations<'ticket'>, 'user' | 'assignees' | 'customer'> & {
        records: (TicketRecord & Canable & Pick<Relations<'ticketRecord'>, 'files'> & { user: Relations<'ticketRecord'>['user'] })[];
    };
export type CustomerProp = Pick<Customer, 'id' | 'name'>;
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'job_role'> & Canable;

export type OperatingSiteProp = {
    title: string;
    address: Address;
} & (
    | { value: { id: User['id']; type: 'App\\Models\\User' } }
    | { value: { id: OperatingSite['id']; type: 'App\\Models\\OperatingSite' } }
    | { value: { id: CustomerOperatingSite['id']; type: 'App\\Models\\CustomerOperatingSite' }; customer_id: Customer['id'] }
);

export type Tab = 'archive' | 'finishedTickets' | 'newTickets' | 'workingTickets';
