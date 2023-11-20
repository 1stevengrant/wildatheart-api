import React from "react";
import {Event} from "@/types/Event";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {Link, usePage} from "@inertiajs/react";

interface ConnectWithHostProps {
    className?: string;
}

const ConnectWithHost = ({className}: ConnectWithHostProps) => {
    const page = usePage();
    const event = page.props.event as Event;
    // @ts-ignore
    const hasConnectedWithHost = page.props.notifications?.connectedWithHost ?? false;
    return (
        <div className={className + ' rounded-lg border border-grey-darker p-8'}>
            {hasConnectedWithHost ? (
                <>
                    <PrimaryButton className={'w-full'}
                                   disabled={true}>
                        You have connected with {event.host}
                    </PrimaryButton>
                    <p className="mt-2 text-sm">
                        Thank you! We've forwarded your details to {event.host} and he'll be in touch directly.
                    </p>
                </>
            ) : (
                <>
                    <Link
                        className={`w-full px-2 py-1 md:px-4 md:py-2  text-sm md:text-base border border-transparent rounded-md bg-orange hover:bg-transparent-orange text-white text-center font-medium transition ease-in-out duration-150 disabled:bg-grey-dark disabled:text-grey-white disabled:cursor-not-allowed focus:ring-0 block`}
                        href={`/connect/${event.id}`}>
                        Connect With {event.host}
                    </Link>
                    <p className="mt-4 text-center text-sm">for more information about this Fire</p>
                </>
            )}
        </div>
    )
};

export default ConnectWithHost;
