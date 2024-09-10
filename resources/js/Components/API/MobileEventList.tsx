import { useRecoilState } from "recoil";

import { MobileEventListCard } from "@/Components/Events/MobileEventListCard";
import { ShowMapButton } from "@/Components/Maps/ShowMapButton";
import { eventsListState } from "@/store/eventsAtom";
import { Event } from "@/types/Event";

export const MobileEventList = ({ ...props }) => {
    const [events] = useRecoilState(eventsListState);
    const { className } = props;
    return (
        <div
            className={`${className} md:hidden absolute z-[100000] px-4 py-2 bg-black w-full rounded-t-2xl flex flex-col max-h-[100dvh]`}
        >
            <div className={`text-center text-base mb-2`}>
                {events.length} Events
            </div>
            <div className={`overflow-y-scroll flex-1`}>
                <ul className={`flex flex-col space-y-4`}>
                    {events.map((event: Event, index: number) => {
                        return (
                            <MobileEventListCard event={event} key={index} />
                        );
                    })}
                </ul>
            </div>
            <ShowMapButton />
        </div>
    );
};
