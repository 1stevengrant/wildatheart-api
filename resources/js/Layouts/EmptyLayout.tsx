import React, { PropsWithChildren } from "react";

import { Head } from "@inertiajs/react";

import { EmptyLayoutProps } from "@/types/EmptyLayoutProps";

export const EmptyLayout = ({
    title,
    children,
}: PropsWithChildren<EmptyLayoutProps>) => {
    return (
        <>
            <Head title={title} />
            {children}
        </>
    );
};
