import React from "react";

import { Link, usePage } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { blackButtonClasses } from "@/helpers/blackButtonClasses";

type ConnectWithHostProps = {
    className?: string;
    hostName: string;
    managers?: { [key: number]: App.Data.EventManagerData };
};

export const ConnectWithHost = ({
    className,
    hostName,
    managers = {},
}: ConnectWithHostProps) => {
    const page = usePage();
    const event = page.props.event as App.Data.EventData;
    const hasConnectedWithHost =
        (page.props.notifications as { connectedWithHost?: boolean })
            ?.connectedWithHost ?? false;

    const managersArray = Object.values(managers);
    const hasHosts = managersArray.length > 0;
    const connectLabel = hasHosts ? "hosts" : hostName;
    const pronoun = hasHosts ? "they'll" : "he'll";

    return (
        <div className={className + " rounded-lg border border-n400 p-8"}>
            {hasConnectedWithHost ? (
                <>
                    <PrimaryButton className={"w-full"} disabled={true}>
                        You have connected with {connectLabel}
                    </PrimaryButton>
                    <p className="mt-2 text-sm">
                        Thank you! We've forwarded your details to the{" "}
                        {hasHosts ? "hosts" : hostName} and {pronoun} be in
                        touch directly.
                    </p>
                </>
            ) : (
                <>
                    <p className={`text-center text-lg font-medium mb-4`}>
                        Want More Info?
                    </p>
                    <Link
                        className={`${blackButtonClasses} w-full`}
                        href={route("connect.index", event.id)}
                    >
                        Connect With {hasHosts ? "Hosts" : hostName}
                    </Link>
                    <p className="mt-4 text-center text-sm">
                        <span className={`capitalize`}>
                            {hasHosts ? "The hosts" : hostName}
                        </span>{" "}
                        will send out updates regarding the event after sign-up.
                    </p>
                </>
            )}
        </div>
    );
};
