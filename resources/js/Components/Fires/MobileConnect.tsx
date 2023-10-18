import React from "react";
import {ShareIcon} from "@heroicons/react/24/outline";
import {defaultIconSize} from "@/helpers/defaultIconSize";
import {Link, usePage} from "@inertiajs/react";
import primaryButtonClasses from "@/helpers/primaryButtonClasses";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {Fire} from "@/types/Fire";

const MobileConnect = () => {
    const page = usePage();
    const fire = page.props.campfire as Fire;
    // @ts-ignore
    const hasConnectedWithHost = page.props.notifications?.connectedWithHost ?? false;

    const shareFire = async () => {
        try {
            await navigator.share({
                title: fire.name,
                text: fire.location,
                url: fire.url,
            });
        } catch (err) {
            console.error(err);
        }
    }

    return (
        <div
            id="mobile-connect-with-host"
            className="fixed bottom-0 left-0 right-0 border-t border-grey-darker bg-black px-4 py-6 md:hidden"
        >
            <div className="flex items-center space-x-6">
                <button onClick={shareFire}
                        className={`flex items-center justify-end share-btn space-x-2 text-white`}>
                    <ShareIcon className={defaultIconSize}/>
                    <span>Share</span>
                </button>
                {hasConnectedWithHost ? (
                    <PrimaryButton className={'w-full'} disabled>
                        You have connected with {fire.host}
                    </PrimaryButton>

                ) : (
                    <Link href={route('connect.index', fire)}
                          className={`${primaryButtonClasses} w-full`}>
                        Connect with {fire.host}
                    </Link>
                )}
            </div>
        </div>
    )
}

export default MobileConnect
