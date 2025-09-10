import { Customer, Relations, Ticket, User } from '@/types/types';

export type TicketProp = Ticket & Pick<Relations<'ticket'>, 'user' | 'assignee' | 'customer' | 'records'>;
export type CustomerProp = Pick<Customer, 'id' | 'name'>;
export type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'job_role'>;
