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
    is_admin: boolean;
};

export type Movie = DBObject<'movie'> & {
    id: number;
    title: string;
    genre: string;
    actor: string;
    rating: number;
    publicationDate: string;
    hidden: boolean;
    description: string;
};

export type Comment = {
    id: number;
    user_id: number;
    movie_id: number;
    comment: string;
    created_at: Date;
};
