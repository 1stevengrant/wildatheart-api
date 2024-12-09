import React from "react";

import { Link, usePage } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { whiteButtonClasses } from "@/helpers/whiteButtonClasses";
import { Event } from "@/types/Event";

export const ConnectWithHost = ({ className }: { className?: string }) => {
    const page = usePage();
    const event = page.props.event as Event;
    const hasConnectedWithHost =
        (page.props.notifications as { connectedWithHost?: boolean })
            ?.connectedWithHost ?? false;
    return (
        <div className={className + " rounded-lg border border-n400 p-8"}>
            {hasConnectedWithHost ? (
                <>
                    <PrimaryButton className={"w-full"} disabled={true}>
                        You have connected with {event.host}
                    </PrimaryButton>
                    <p className="mt-2 text-sm">
                        Thank you! We've forwarded your details to {event.host}{" "}
                        and he'll be in touch directly.
                    </p>
                </>
            ) : (
                <>
                    <p className={`text-center text-lg font-medium mb-4`}>
                        Want More Info?
                    </p>
                    <Link
                        className={`${whiteButtonClasses} block capitalize`}
                        href={route("connect.index", event.id)}
                    >
                        Connect With {event.host}
                    </Link>
                    <p className="mt-4 text-center text-sm">
                        <span className={`capitalize`}>{event.host}</span> will
                        send out updates regarding the event after sign-up.
                    </p>
                </>
            )}
        </div>
    );
};
