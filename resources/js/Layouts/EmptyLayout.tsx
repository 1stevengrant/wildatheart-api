import React, { PropsWithChildren } from "react";

import { Head } from "@inertiajs/react";

import { EventCreateProgress } from "@/Components/Layout/EventCreateProgress";
import { EmptyLayoutProps } from "@/types/EmptyLayoutProps";

export const EmptyLayout = ({
    title,
    children,
}: PropsWithChildren<EmptyLayoutProps>) => {
    return (
        <>
            <Head title={title} />
            {children}
            <EventCreateProgress />
        </>
    );
};
