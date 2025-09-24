import Markdown from "react-markdown";

import { KeyDetails } from "@/Components/Events/Detail/KeyDetails";
import { PhotoCarousel } from "@/Components/Events/Detail/PhotoCarousel";
import { proseClasses } from "@/helpers/proseClasses";

export const EventContent = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            <KeyDetails event={event} />

            <div className={proseClasses}>
                <Markdown>{event.event_type_data.description}</Markdown>
            </div>

            {event.event_type_data.photos &&
                event.event_type_data.photos.length > 0 && (
                    <PhotoCarousel
                        className="mt-8"
                        images={event.event_type_data.photos}
                    />
                )}
        </>
    );
};
