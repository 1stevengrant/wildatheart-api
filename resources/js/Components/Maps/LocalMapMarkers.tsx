import Tippy from "@tippyjs/react/headless";
import { Marker } from "react-map-gl";
import { useRecoilState } from "recoil";

import { EventCard } from "@/Components/Events/EventCard";
import { EventMarkerButton } from "@/Components/Events/EventMarkerButton";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { eventsListState } from "@/store/eventsAtom";

export const LocalMapMarkers = () => {
    const [events] = useRecoilState(eventsListState);
    const { setPopupInfo } = usePopupInfo();
    return (
        <>
            {events.length > 0 &&
                events.map((event: App.Data.EventData) => (
                    <Marker
                        key={event.id}
                        longitude={event.display_longitude}
                        latitude={event.display_latitude}
                        anchor="bottom"
                        onClick={(e) => {
                            e.originalEvent.stopPropagation();
                            setPopupInfo(event);
                        }}
                    >
                        <div className={`hidden lg:block`}>
                            <Tippy
                                trigger="click mouseenter"
                                key={event.id}
                                placement={`bottom`}
                                interactive={true}
                                touch={true}
                                delay={250}
                                appendTo={() => document.body}
                                render={(attrs) => (
                                    <EventCard event={event} attrs={attrs} />
                                )}
                            >
                                <span>
                                    {" "}
                                    <EventMarkerButton event={event} />
                                </span>
                            </Tippy>
                        </div>
                        <div className={`md:hidden`}>
                            <span>
                                <EventMarkerButton event={event} />
                            </span>
                        </div>
                    </Marker>
                ))}
        </>
    );
};
