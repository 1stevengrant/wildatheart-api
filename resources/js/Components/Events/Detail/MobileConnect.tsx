import { Link, usePage } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { whiteButtonClasses } from "@/helpers/whiteButtonClasses";

export const MobileConnect = () => {
    const page = usePage();
    const event = page.props.event as App.Data.EventData;
    const hasConnectedWithHost =
        (page.props.notifications as { connectedWithHost?: boolean })
            ?.connectedWithHost ?? false;

    const managersArray = Object.values(event.managers || {});
    const hasHosts = managersArray.length > 0;
    const connectLabel = hasHosts ? "hosts" : event.host;

    return (
        <div
            id="mobile-connect-with-host"
            className="fixed right-0 bottom-0 left-0 border-t bg-black px-4 py-6 border-n400 md:hidden"
        >
            <div className="flex items-center space-x-6">
                {hasConnectedWithHost ? (
                    <PrimaryButton className={"w-full"} disabled>
                        You have connected with {connectLabel}
                    </PrimaryButton>
                ) : (
                    <Link
                        href={route("connect.index", event)}
                        className={`${whiteButtonClasses} w-full`}
                    >
                        Connect with {hasHosts ? "hosts" : event.host}
                    </Link>
                )}
            </div>
        </div>
    );
};
