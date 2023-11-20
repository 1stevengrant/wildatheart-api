import React from 'react';
import EventContent from "./EventContent";
import {XMarkIcon} from "@heroicons/react/24/solid";
import {useRecoilState} from "recoil";
import {currentEventAtom} from "@/store";

interface EventPopupProps {
    className?: string;
}

const EventPopupMobile: React.FC<EventPopupProps> = ({className}) => {
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventAtom)
    return (
        <div
            className={'fixed bottom-[96px] right-4 left-4 text-white rounded-md shadow-lg p-2 md:p-4 bg-black ' + className}>
            <div className={'bg-white mx-auto h-[5px] w-[75px] rounded-full opacity-30 mt-1'}/>
            <button
                className="float-right hover:text-orange transition ease-in-out duration-75"
                onClick={() => setCurrentEvent(undefined)}>
                <XMarkIcon className="h-5 w-5"/>
            </button>
            <a href={currentEvent?.url}
               className="flex items-stretch space-x-4 clear-both">
                <EventContent event={currentEvent}/>
            </a>
        </div>
    );
};
export default EventPopupMobile
