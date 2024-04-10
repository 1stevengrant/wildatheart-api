import { Link } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import { EventCardContent } from "./EventCardContent";

import { currentEventState } from "@/store";
import { IEventCardMobileProps } from "@/types/IEventCardMobileProps";

export const EventCardMobile = ({ className }: IEventCardMobileProps) => {
    const [currentEvent] = useRecoilState(currentEventState);

    return (
        <>
            {currentEvent && (
                <Link
                    href={currentEvent.url}
                    className={`fixed bottom-[96px] right-4 left-4 text-white rounded-md shadow-lg p-2 md:p-4 bg-black ${className}`}
                >
                    <div className="clear-both flex items-stretch space-x-4">
                        <EventCardContent event={currentEvent} />
                    </div>
                </Link>
            )}
        </>
    );
};
