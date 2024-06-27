import { Link } from "@inertiajs/react";

import { EventAdminActions } from "@/Components/Events/EventAdminActions";
import { EventTypeFires } from "@/helpers/eventNames";
import { imageGenerator } from "@/helpers/user";
import { Event } from "@/types/Event";

export const EventAdminCard = ({ event }: { event: Event }) => {
    return (
        <div>
            <Link href={event.url} className="relative z-40">
                <div className="rounded-md border bg-black p-2 shadow-lg border-grey-darker md:p-4">
                    <div className="flex items-stretch space-x-4">
                        {event.eventTypeImage ? (
                            <img
                                src={imageGenerator(event.eventTypeImage, {
                                    height: 100,
                                    width: 100,
                                })}
                                className="rounded-lg"
                                alt=""
                            />
                        ) : (
                            <div className="rounded-lg border border-white bg-cover bg-center object-fill w-[100px] h-[100px]">
                                {event.eventType}
                            </div>
                        )}
                        <div className="relative flex flex-col justify-between">
                            <div>
                                <p className="text-white">
                                    {event.name}{" "}
                                    {event.eventType === EventTypeFires && (
                                        <span>Fire</span>
                                    )}
                                </p>

                                <p className="text-sm text-white">
                                    {event.location}
                                </p>
                            </div>
                            <div className="flex items-center space-x-2">
                                <div
                                    className="h-6 w-6 flex-shrink-0 rounded-full bg-cover bg-center object-fill"
                                    style={{
                                        backgroundImage: `url('/svgs/user.svg')`,
                                    }}
                                ></div>
                                <div className="text-sm leading-tighter text-grey-200">
                                    Hosted by {event.host}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Link>
            <div className="relative -mt-2 flex justify-end rounded-lg bg-white/10 p-6 text-sm space-x-4">
                <EventAdminActions event={event} />
            </div>
        </div>
    );
};
