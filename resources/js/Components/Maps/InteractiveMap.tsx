import React, { useRef } from "react";

import Tippy from "@tippyjs/react/headless";
import { Marker, NavigationControl } from "react-map-gl";
import Map, { GeolocateControl } from "react-map-gl/maplibre";

import { EventCard } from "@/Components/Events/EventCard";
import { EventMarkerButton } from "@/Components/Events/EventMarkerButton";
import { commonMapProps } from "@/helpers/commonMapProps";
import { useEventList } from "@/Hooks/useEventList";
import { useMapData } from "@/Hooks/useMapData";
import { usePopupInfo } from "@/Hooks/usePopupInfo";

export const InteractiveMap = ({ className = "" }: { className?: string }) => {
    const { setPopupInfo } = usePopupInfo();
    const mapContainerRef = useRef<HTMLDivElement>(null);
    const { mapRef } = useMapData();
    const { data } = useEventList();
    return (
        <div className={`${className} relative flex-1`} ref={mapContainerRef}>
            <Map ref={mapRef} {...commonMapProps} minZoom={0} maxZoom={13}>
                {/*<GetUserLocationModal*/}
                {/*    open={openGetUserLocationModal}*/}
                {/*    setOpen={setOpenGetUserLocationModal}*/}
                {/*/>*/}

                <GeolocateControl position="top-left" />
                <NavigationControl showCompass={false} position="top-left" />
                {/*<EventList />*/}
                {/*{showLoader && (*/}
                {/*    <IsLoading*/}
                {/*        className={`relative mt-8`}*/}
                {/*        text={`Loading Events`}*/}
                {/*    />*/}
                {/*)}*/}
                {data.map((event: any) => (
                    <Marker
                        key={event.id}
                        longitude={event.longitude}
                        latitude={event.latitude}
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
            </Map>
        </div>
    );
};
