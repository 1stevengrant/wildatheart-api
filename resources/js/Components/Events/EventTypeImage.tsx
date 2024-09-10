import { imageGenerator } from "@/helpers/user";
import { Event } from "@/types/Event";

export const EventTypeImage = ({ event }: { event: Event }) => {
    return (
        <>
            {event.eventTypeImage ? (
                <div>
                    <img
                        src={imageGenerator(event.eventTypeImage, {
                            height: 100,
                            width: 100,
                        })}
                        className="rounded-lg"
                        alt=""
                    />
                </div>
            ) : (
                <div className="rounded-lg border border-white bg-cover bg-center object-fill w-[100px] h-[100px]">
                    {event.eventType}
                </div>
            )}
        </>
    );
};
