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

    // Sort events so featured events render last (appear on top)
    const sortedEvents = [...events].sort((a, b) => {
        if (a.is_featured === b.is_featured) return 0;
        return a.is_featured ? 1 : -1;
    });

    return (
        <>
            {sortedEvents.length > 0 &&
                sortedEvents.map((event: App.Data.EventData) => (
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
                        <div className={`hidden lg:block ${event.is_featured ? 'relative z-10' : ''}`}>
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
                                <span className={event.is_featured ? 'inline-block relative' : ''}>
                                    {event.is_featured && (
                                        <div className="absolute inset-0 rounded-full ring-2 ring-p200 ring-offset-2" />
                                    )}
                                    <EventMarkerButton event={event} />
                                </span>
                            </Tippy>
                        </div>
                        <div className={`md:hidden ${event.is_featured ? 'relative z-10' : ''}`}>
                            <span className={event.is_featured ? 'inline-block relative' : ''}>
                                {event.is_featured && (
                                    <div className="absolute inset-0 rounded-full ring-2 ring-p200 ring-offset-2" />
                                )}
                                <EventMarkerButton event={event} />
                            </span>
                        </div>
                    </Marker>
                ))}
        </>
    );
};
