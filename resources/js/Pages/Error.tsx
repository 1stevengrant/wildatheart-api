import React from "react";

import { BackToEvents } from "@/Components/Buttons/BackToEvents";
import { Logo } from "@/Components/Ui/Logo";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

type Status = 503 | 500 | 404 | 403;
type ErrorProps = {
    status: Status;
};
const Error = ({ status }: ErrorProps) => {
    const title = {
        503: "503: Service Unavailable",
        500: "500: Server Error",
        404: "404: Page Not Found",
        403: "403: Forbidden",
    }[status];

    const description = {
        503: "Sorry, we are doing some maintenance. Please check back soon.",
        500: "Whoops, something went wrong on our servers. Our engineers are on it.",
        404: "Sorry, the page you are looking for could not be found.",
        403: "Sorry, you are forbidden from accessing this page.",
    }[status];

    return (
        <EmptyLayout title={title}>
            <div className="flex h-screen flex-col items-center justify-center">
                <div className="text-center text-2xl font-semibold text-white">
                    <div className="flex items-center">
                        <Logo className={`mx-auto mb-4 w-[300px]`} />
                    </div>
                    <h2>{title}</h2>
                    <p>{description}</p>
                    <div className={`flex justify-center`}>
                        <BackToEvents positionClasses={"static"} />
                    </div>
                </div>
            </div>
        </EmptyLayout>
    );
};

export default Error;
