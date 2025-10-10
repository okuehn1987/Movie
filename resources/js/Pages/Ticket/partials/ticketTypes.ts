import { Canable, Customer, Relations, Ticket, TicketRecord, User } from '@/types/types';

export type TicketProp = Ticket & Canable &
    Pick<Relations<'ticket'>, 'user' | 'assignees' | 'customer'> & {
        records: (TicketRecord & Canable & Pick<Relations<'ticketRecord'>, 'files'> & {user:Relations<'ticketRecord'>['user'] })[];
    };
export type CustomerProp = Pick<Customer, 'id' | 'name'>;
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'job_role'> & Canable;

export type Tab = 'archive' | 'finishedTickets' | 'newTickets' | 'workingTickets' 
