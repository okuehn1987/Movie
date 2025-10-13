import { Canable, Customer, CustomerOperatingSite, OperatingSite, Relations, Ticket, TicketRecord, User } from '@/types/types';

export type TicketProp = Ticket & Canable &
    Pick<Relations<'ticket'>, 'user' | 'assignees' | 'customer'> & {
        records: (TicketRecord & Canable & Pick<Relations<'ticketRecord'>, 'files'> & {user:Relations<'ticketRecord'>['user'] })[];
    };
export type CustomerProp = Pick<Customer, 'id' | 'name'>;
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'job_role'> & Canable;

export type OperatingSiteProp= OperatingSite & Pick<Relations<'operatingSite'>,'current_address'>;

export type CustomerOperatingSiteProp= CustomerOperatingSite & Pick<Relations<'customerOperatingSite'>,'current_address'>;

export type Tab = 'archive' | 'finishedTickets' | 'newTickets' | 'workingTickets' 
