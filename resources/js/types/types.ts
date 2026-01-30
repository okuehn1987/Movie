type Branded<T, Brand extends string> = T & { [x in `__${Brand}__`]: void };

export type DateString = Branded<string, 'date'>;
export type TimeString = Branded<string, 'time'>;
export type DateTimeString = Branded<string, 'dateTime'>;

export type DBObject<Brand extends string> = {
    id: Branded<number, Brand>;
    created_at: DateTimeString;
    updated_at: DateTimeString;
};

export type User = DBObject<'user'> & {
    name: string;
};
