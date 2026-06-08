import { useRecoilState } from "recoil";

import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { selectedEventTypeState } from "@/store/eventsAtom";

export const NoEvents = ({ type }: { type: string }) => {
    const { eventTypes } = useTypedPage().props;
    const typedEventTypes = eventTypes as App.Data.EventTypeData[];
    const [selectedEventType, setSelectedEventType] = useRecoilState(
        selectedEventTypeState
    );
    return (
        <>
            <p className="clear-both text-center text-2xl">
                You have no {type} events currently
            </p>
            <ul
                className={`grid grid-cols-3 gap-6 md:gap-0 md:flex md:flex-row md:space-x-6 md:justify-center mt-8`}
            >
                {typedEventTypes.map((eventType: App.Data.EventTypeData, index: number) => (
                    <li
                        key={index}
                        className={`flex flex-col justify-center items-center`}
                    >
                        <button onClick={() => setSelectedEventType(eventType)}>
                            {eventType.image ? (
                                <img
                                    className={`rounded-xl hover:opacity-100 transition-opacity ease-in-out duration-300 ${selectedEventType?.id === eventType.id ? "opacity-100" : "opacity-30"}`}
                                    srcSet={`${imageGenerator(eventType.image, {
                                        width: 100,
                                        height: 100,
                                    })} 100w, ${imageGenerator(eventType.image, { width: 100, height: 100 })} 100w`}
                                    sizes="(max-width: 640px) 100px, 100px"
                                    src={imageGenerator(eventType.image, {
                                        width: 100,
                                        height: 100,
                                    })}
                                    alt={eventType.name}
                                />
                            ) : (
                                <div className={`w-[100px] h-[100px]`}>
                                    {eventType.name}
                                </div>
                            )}
                        </button>
                    </li>
                ))}
            </ul>
        </>
    );
};
