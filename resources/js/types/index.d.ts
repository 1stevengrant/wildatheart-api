import { Connection } from "@/types/Connection";

export type Nullable<T> = T | null;

export interface Ziggy {
    [key: string]: string[];
}

export interface User {
    id: number;
    given_name: string;
    family_name: string;
    full_name: string;
    email: string;
    opted_into_marketing: boolean;
    image: string;
    profile_step: number;
    phone: string;
    city: string;
    state: string;
    postal_code: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    allConnections: Connection[] | [];
};

export interface Auth {
    user: Nullable<User>;
}

export type InertiaSharedProps<T = unknown> = T & {
    auth: Auth;
    errors: any;
};
