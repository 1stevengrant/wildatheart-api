import { Config } from "ziggy-js";

export type Nullable<T> = T | null;

export interface Ziggy {
    [key: string]: string[];
}

export interface User {
    id: number;
    given_name: string;
    family_name: string;
    fullname: string;
    email: string;
    opted_into_marketing: boolean;
    image: string;
    profile_step: number;
    phone: string;
    city: string;
    state: string;
    postal_code: string;
    md5email: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};

export interface Auth {
    user: Nullable<User>;
}

export type InertiaSharedProps<T = unknown> = T & {
    auth: Auth;
    errors: any;
};

export interface Attendee {
    id: number;
    event_id: number | null;
    user_id: number | null;
    family_name: string;
    given_name: string;
    email: string;
    country: string;
    country_code: string;
    phone: string;
    city: string;
    state: string;
    marketing_updates: boolean;
    contacted_on: string;
    created_at: string;
    updated_at: string;
    event_type_name: string;
    event_name: string;
    fullname: string;
}

export type PaginatedData<T> = {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };

    meta: {
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;

        links: {
            url: null | string;
            label: string;
            active: boolean;
        }[];
    };
};
