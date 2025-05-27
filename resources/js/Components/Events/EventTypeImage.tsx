import { Image } from "@/Components/Image";

export const EventTypeImage = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            {event.event_type_image ? (
                <div>
                    <Image
                        src={event.event_type_image}
                        className={`rounded-lg`}
                    />
                </div>
            ) : (
                <div className="rounded-lg border border-white bg-cover bg-center object-fill w-[100px] h-[100px]">
                    {/*{event.event_type}*/}
                </div>
            )}
        </>
    );
};
