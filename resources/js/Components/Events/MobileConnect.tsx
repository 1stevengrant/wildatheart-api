import React from "react";

import { Link, usePage } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { whiteButtonClasses } from "@/helpers/whiteButtonClasses";
import { Event } from "@/types/Event";

const MobileConnect = () => {
    const page = usePage();
    const event = page.props.event as Event;
    const hasConnectedWithHost =
        (page.props.notifications as { connectedWithHost?: boolean })
            ?.connectedWithHost ?? false;

    return (
        <div
            id="mobile-connect-with-host"
            className="fixed right-0 bottom-0 left-0 border-t bg-black px-4 py-6 border-grey-darker md:hidden"
        >
            <div className="flex items-center space-x-6">
                {hasConnectedWithHost ? (
                    <PrimaryButton className={"w-full"} disabled>
                        You have connected with {event.host}
                    </PrimaryButton>
                ) : (
                    <Link
                        href={route("connect.index", event)}
                        className={`${whiteButtonClasses} w-full`}
                    >
                        Connect with {event.host}
                    </Link>
                )}
            </div>
        </div>
    );
};

export default MobileConnect;
