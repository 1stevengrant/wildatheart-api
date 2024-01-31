import React from "react";
import {ShareIcon} from "@heroicons/react/24/outline";
import {defaultIconSize} from "@/helpers/defaultIconSize";
import {Link, usePage} from "@inertiajs/react";
import primaryButtonClasses from "@/helpers/primaryButtonClasses";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {Event} from "@/types/Event";
import {whiteButtonClasses} from "@/helpers/whiteButtonClasses";

const MobileConnect = () => {
    const page = usePage();
    const event = page.props.event as Event;
    // @ts-ignore
    const hasConnectedWithHost = page.props.notifications?.connectedWithHost ?? false;

    const shareEvent = async () => {
        try {
            await navigator.share({
                title: event.name,
                text: event.location,
                url: event.url,
            });
        } catch (err) {
            console.error(err);
        }
    }

    return (
        <div
            id="mobile-connect-with-host"
            className="fixed right-0 bottom-0 left-0 border-t bg-black px-4 py-6 border-grey-darker md:hidden"
        >
            <div className="flex items-center space-x-6">
                <button onClick={shareEvent}
                        className={`flex items-center justify-end share-btn space-x-2 text-white`}>
                    <ShareIcon className={defaultIconSize}/>
                    <span>Share</span>
                </button>
                {hasConnectedWithHost ? (
                    <PrimaryButton className={'w-full'} disabled>
                        You have connected with {event.host}
                    </PrimaryButton>

                ) : (
                    <Link href={route('connect.index', event)}
                          className={`${whiteButtonClasses} w-full`}>
                        Connect with {event.host}
                    </Link>
                )}
            </div>
        </div>
    )
}

export default MobileConnect
