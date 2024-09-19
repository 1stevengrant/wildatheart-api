import React from "react";

import { Link } from "@inertiajs/react";

import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { UserAvatar } from "@/Components/UserAvatar";
import { dateFormatter } from "@/helpers/dateFormatter";
import { EVENT_FOR_MEN } from "@/helpers/eventSex";
import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import { imageGenerator } from "@/helpers/user";
import { Event } from "@/types/Event";

export const MobileEventListCard = ({ event }: { event: Event }) => {
    return (
        <li className={`border border-grey-darker rounded-lg p-4`}>
            <Link className={`flex space-x-4`} href={event.url}>
                <div>
                    <img
                        src={imageGenerator(event.event_type_image)}
                        alt={event.name}
                        className={`w-24 h-24 rounded-lg flex-shrink-0`}
                    />
                </div>
                <div className={`flex-1 flex flex-col justify-between`}>
                    <div className={`leading-tighter text-xs`}>
                        <p className={`text-grey-200`}>
                            {event.type_group === experienceTypeGroup &&
                                `6-part video study for ${event.sex === EVENT_FOR_MEN ? `Men` : `Women`}`}
                            {event.type_group === fireTypeGroup && (
                                <span>Monthly Gathering For Men</span>
                            )}
                            {event.type_group === retreatTypeGroup &&
                                `Multi-Day retreat for ${event.sex === EVENT_FOR_MEN ? `Men` : `Women`}`}
                        </p>
                        <p className={`font-medium text-base mb-3`}>
                            {event.location}
                        </p>
                        <span
                            className={`px-4 py-0.5 rounded-full border border-white inline-block mb-1`}
                        >
                            {event.type_group === fireTypeGroup && `Monthly`}
                            {event.type_group === retreatTypeGroup &&
                                `${dateFormatter(event.start_date, "MMM D")} - ${dateFormatter(event.end_date, "MMM D")}`}
                            {event.type_group === experienceTypeGroup &&
                                `Starts on ${dateFormatter(event.start_date)}`}
                        </span>
                    </div>
                    <div className={`flex items-center space-x-2 mt-1`}>
                        <>
                            {event?.host_photo ? (
                                <UserAvatar image={event.host_photo} />
                            ) : (
                                <FallbackAvatar
                                    size={30}
                                    name={event.hostObject.full_name}
                                    md5email={event.hostObject.md5email}
                                />
                            )}
                        </>
                        <div
                            className={`flex flex-col text-grey-200 leading-tighter text-sm`}
                        >
                            <div>{event.name}</div>
                            <div>{event.host_name}</div>
                        </div>
                    </div>
                </div>
            </Link>
        </li>
    );
};
