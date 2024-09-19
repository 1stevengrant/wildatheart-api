import React from "react";

import { PhotoCarousel } from "@/Components/Events/Detail/PhotoCarousel";
import { Event } from "@/types/Event";

export const Photos = ({ event }: { event: Event }) => {
    if (event.eventTypeData.photos && event.eventTypeData.photos.length > 0) {
        return (
            <PhotoCarousel
                className={`mt-8`}
                images={event?.eventTypeData.photos}
            />
        );
    }
};
