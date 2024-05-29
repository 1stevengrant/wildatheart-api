import React, { Fragment } from "react";

import { Transition } from "@headlessui/react";
import { XCircleIcon } from "@heroicons/react/24/outline";
import Markdown from "react-markdown";

import { TagGroup } from "@/Components/TagGroup";
import { TagItem } from "@/Components/TagItem";
import { eventTypeBackground } from "@/helpers/eventTypeBackground";
import { imageGenerator } from "@/helpers/user";
import { EventType } from "@/types/EventType";

export const SelectedEventType = ({
    eventType,
    setSelectedType,
}: {
    eventType: EventType;
    setSelectedType: (type: EventType | null) => void;
}) => {
    const tags = eventType.tags ? eventType.tags.split(",") : [];

    return (
        <Transition.Root show={true} as={Fragment}>
            <Transition.Child
                as={Fragment}
                enter="ease-out duration-300"
                enterFrom="opacity-0"
                enterTo="opacity-100"
                leave="ease-in duration-200"
                leaveFrom="opacity-100"
                leaveTo="opacity-0"
            >
                <div
                    className={`right-6 left-6 absolute bottom-0 p-6 rounded-2xl z-[100] shadow-xl bg-gradient-to-br ${eventTypeBackground(eventType.slug)} to-zinc-800`}
                >
                    <XCircleIcon
                        className={`absolute top-6 right-6 cursor-pointer w-8`}
                        onClick={() => setSelectedType(null)}
                    />
                    <h2 className={`font-bold`}>{eventType.name}</h2>
                    {eventType.image && (
                        <img
                            className={`rounded-xl my-4`}
                            src={imageGenerator(eventType.image, {
                                width: 200,
                                height: 200,
                            })}
                            alt={eventType.name}
                        />
                    )}

                    {tags && tags.length > 0 && (
                        <TagGroup>
                            {tags.map((tag: string) => (
                                <TagItem tag={tag} key={tag} />
                            ))}
                        </TagGroup>
                    )}
                    <Markdown className={`mt-4`}>
                        {eventType.card_teaser_text}
                    </Markdown>
                </div>
            </Transition.Child>
        </Transition.Root>
    );
};
