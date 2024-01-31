import React from 'react';
import EventCardContent from "./EventCardContent";
import {useRecoilState} from "recoil";
import {currentEventAtom} from "@/store";
import {Link} from "@inertiajs/react";

interface EventCardMobileProps {
    className?: string;
}

const EventCardMobile: React.FC<EventCardMobileProps> = ({className}) => {
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventAtom)

    return (
        <div
            className={`fixed bottom-[96px] right-4 left-4 text-white rounded-md shadow-lg p-2 md:p-4 bg-black ${className}`}>
            { /*  @ts-ignore */ }
            <Link href={currentEvent?.url}
               className="clear-both flex items-stretch space-x-4">
                <EventCardContent event={currentEvent}/>
            </Link>
        </div>
    );
};
export default EventCardMobile
