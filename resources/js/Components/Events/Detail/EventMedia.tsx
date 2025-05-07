import Vimeo from "@u-wave/react-vimeo";

export const EventMedia = ({ event }: { event: App.Data.EventData }) => {
    if (
        !event.event_type_data?.hero_video &&
        !event.event_type_data?.hero_image
    ) {
        return null;
    }

    return (
        <div className="mt-[48px]">
            {event.event_type_data.hero_video ? (
                <Vimeo
                    responsive={true}
                    video={event.event_type_data.hero_video}
                    autoplay
                />
            ) : event.event_type_data.hero_image ? (
                <img
                    src={event.event_type_data.hero_image}
                    alt={`Hero image for ${event.name}`}
                />
            ) : null}
        </div>
    );
};
