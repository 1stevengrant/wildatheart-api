import { Config } from "ziggy-js";

export type Nullable<T> = T | null;

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: App.Data.UserData;
    };
    ziggy: Config & { location: string };
};

export interface Auth {
    user: Nullable<App.Data.UserData>;
}

export type InertiaSharedProps<T = unknown> = T & {
    auth: Auth;
    errors: any;
};

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
