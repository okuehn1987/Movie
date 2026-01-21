import { Address, Canable, Customer, CustomerOperatingSite, OperatingSite, RelationPick, Ticket, TicketRecord, User } from '@/types/types';

export type TicketProp = Ticket &
    Canable &
    RelationPick<'ticket', 'user', 'id' | 'first_name' | 'last_name'> &
    RelationPick<'ticket', 'assignees', 'id' | 'first_name' | 'last_name' | 'pivot'> &
    RelationPick<'ticket', 'customer', 'id' | 'name'> &
    RelationPick<'ticket', 'files', 'id' | 'original_name' | 'ticket_id'> & {
        records: (TicketRecord &
            Canable &
            RelationPick<'ticketRecord', 'files', 'id' | 'original_name' | 'ticket_record_id'> & {
                address: Pick<Address, 'id' | 'street' | 'house_number' | 'zip' | 'city' | 'country' | 'federal_state'> & {
                    addressable: Pick<Address, 'id'> & { name: string };
                };
            } & RelationPick<'ticketRecord', 'user', 'id' | 'first_name' | 'last_name'>)[];
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
