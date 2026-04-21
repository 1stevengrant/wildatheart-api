import React from "react";

import { BackToEvents } from "@/Components/Buttons/BackToEvents";
import { Logo } from "@/Components/Ui/Logo";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const errors: Record<
    number,
    { title: string; heading: string; description: string }
> = {
    401: {
        title: "401 Unauthorized",
        heading: "Unauthorized",
        description: "You need to be signed in to access this page.",
    },
    403: {
        title: "403 Forbidden",
        heading: "Forbidden",
        description: "Sorry, you don't have permission to access this page.",
    },
    404: {
        title: "404 Not Found",
        heading: "Page Not Found",
        description:
            "Sorry, the page you're looking for doesn't exist or has been moved.",
    },
    419: {
        title: "419 Session Expired",
        heading: "Session Expired",
        description:
            "Your session has expired. Please refresh the page and try again.",
    },
    429: {
        title: "429 Too Many Requests",
        heading: "Too Many Requests",
        description:
            "You've made too many requests. Please wait a moment and try again.",
    },
    500: {
        title: "500 Server Error",
        heading: "Server Error",
        description:
            "Something went wrong on our end. Our team has been notified.",
    },
    503: {
        title: "503 Service Unavailable",
        heading: "Under Maintenance",
        description:
            "We're doing some maintenance right now. Please check back soon.",
    },
};

const fallback = {
    title: "Error",
    heading: "Something Went Wrong",
    description: "An unexpected error occurred. Please try again.",
};

const Error = ({ status }: { status: number }) => {
    const { title, heading, description } = errors[status] ?? fallback;

    return (
        <EmptyLayout title={title}>
            <div className="flex h-screen flex-col items-center justify-center px-6">
                <div className="w-full max-w-lg text-center">
                    <Logo className="mx-auto mb-10 w-[200px] md:w-[260px]" />

                    <span className="mb-3 block text-[5rem] font-bold leading-none tracking-tight text-p200 md:text-[7rem]">
                        {status}
                    </span>

                    <h1 className="mb-3 text-h2 font-semibold md:text-h1">
                        {heading}
                    </h1>

                    <p className="mb-10 text-body-base text-n400 md:text-body-large">
                        {description}
                    </p>

                    <div className="flex justify-center">
                        <BackToEvents positionClasses="static" />
                    </div>
                </div>
            </div>
        </EmptyLayout>
    );
};

export default Error;
