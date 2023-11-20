import React from 'react';
import EventContent from "./EventContent";
import {XMarkIcon} from "@heroicons/react/24/solid";
import {useRecoilState} from "recoil";
import {currentEventAtom} from "@/store";

const EventPopup = (props: any) => {
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventAtom)
    return (
        <div className="rounded-md shadow-lg p-2 md:p-4 bg-black relative hidden md:block mt-6">
            <button
                className="float-right text-white hover:text-orange transition ease-in-out duration-75"
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
export default EventPopup
