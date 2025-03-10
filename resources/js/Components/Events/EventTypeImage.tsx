import { imageGenerator } from "@/helpers/user";
import { Event } from "@/types/Event";

export const EventTypeImage = ({ event }: { event: Event }) => {
    return (
        <>
            {event.event_type_image ? (
                <div>
                    <img
                        src={imageGenerator(event.event_type_image, {
                            height: 100,
                            width: 100,
                        })}
                        className="rounded-lg"
                        alt=""
                    />
                </div>
            ) : (
                <div className="rounded-lg border border-white bg-cover bg-center object-fill w-[100px] h-[100px]">
                    {event.event_type}
                </div>
            )}
        </>
    );
};
