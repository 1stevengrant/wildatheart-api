import { PropsWithChildren } from "react";

import { Head } from "@inertiajs/react";

export const EmptyLayout = ({
    title,
    children,
}: PropsWithChildren<{
    title: string;
}>) => {
    return (
        <>
            <Head title={title} />
            {children}
        </>
    );
};
