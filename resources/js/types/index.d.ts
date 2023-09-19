export interface Errors {
    [key: string]: string[];
}

export interface Ziggy {
    [key: string]: string[];
}

export interface User {
    id: number;
    given_name: string;
    family_name: string;
    email: string;
    email_verified_at: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
};
