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
    title: string;
    genre: string;
    rating: number;
    publication_date: string;
    hidden: boolean;
    description: string;
    thumbnail_file_path: string;
};

export type Comment = DBObject<'comment'> & {
    name: string;
    user_id: number;
    movie_id: number;
    comment: string;
    created_at: Date;
};

export type Actor = DBObject<'actor'> & {
    first_name: string;
    last_name: string;
};
