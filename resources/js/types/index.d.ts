import type { Config } from "ziggy-js";

export type Nullable<T> = T | null;

/** The user shared with every page by HandleInertiaRequests::transformUser(). */
export interface SharedUser {
    id: number;
    given_name: string | null;
    family_name: string | null;
    fullname: string;
    email: string;
    phone: string | null;
    opted_into_marketing: boolean;
    profile_step: number | null;
    image: string;
    has_complete_profile: boolean;
}

export interface FlashNotification {
    message: string;
    type?: "success" | "error" | "info";
}

declare module "@inertiajs/core" {
    interface PageProps {
        auth: {
            user: SharedUser | null;
        };
        ziggy: Config & { location: string };
        notifications?: FlashNotification | null;
        meta: App.Data.SocialMetaData | null;
    }
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
