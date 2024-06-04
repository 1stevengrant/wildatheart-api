import React, { useEffect, useState } from "react";

import {
    ClipboardDocumentCheckIcon,
    PencilIcon,
} from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import EventDeleteModal from "@/Components/Events/EventDeleteModal";
import { EventPublishModal } from "@/Components/Events/EventPublishModal";
import { EventUnpublishModal } from "@/Components/Events/EventUnpublishModal";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import { EventTypeFires } from "@/helpers/eventNames";
import { imageGenerator } from "@/helpers/user";
import { reactNotificationState } from "@/store";
import { Event } from "@/types/Event";

export const EventAdminCard = ({ event }: { event: Event }) => {
    const [reactNotifications, setReactNotifications] = useRecoilState(
        reactNotificationState
    );

    const copyToClipboard = async (): Promise<void> => {
        try {
            await navigator.clipboard.writeText(event.url);
            const newNotification = {
                message: "URL copied to clipboard!",
                type: "success",
            };
            setReactNotifications([...reactNotifications, newNotification]);
        } catch (error) {
            console.error(error);
            const newErrorNotification = {
                message: "Oops, unable to copy",
                type: "error",
            };
            setReactNotifications([
                ...reactNotifications,
                newErrorNotification,
            ]);
        }
    };

    const [openEventDeleteModal, setOpenEventDeleteModal] = useState(false);
    const [openEventUnpublishModal, setOpenEvenUnpublishModal] =
        useState(false);
    const [openEventPublishModal, setOpenEventPublishModal] = useState(false);

    useEffect(() => {
        const params = route().params;
        if (params && params.event === event.id) {
            setOpenEventPublishModal(true);
        }
    }, [event]);

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
                            <div className="border border-white rounded-lg w-[100px] h-[100px] bg-cover bg-center object-fill">
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
                {event.approved_at ? (
                    <EventUnpublishModal
                        open={openEventUnpublishModal}
                        setOpen={setOpenEvenUnpublishModal}
                        event={event}
                    />
                ) : (
                    <EventPublishModal
                        open={openEventPublishModal}
                        setOpen={setOpenEventPublishModal}
                        event={event}
                    />
                )}
                <EventDeleteModal
                    open={openEventDeleteModal}
                    setOpen={setOpenEventDeleteModal}
                    event={event}
                />
                <Link
                    href={route("my.events.edit", event)}
                    className="flex items-center space-x-2 hover:text-orange"
                >
                    <PencilIcon className={defaultIconSize} />

                    <span className="hidden md:inline">Edit Event</span>
                </Link>
                <button
                    className="flex items-center space-x-2 hover:text-orange"
                    onClick={copyToClipboard}
                >
                    <ClipboardDocumentCheckIcon className={defaultIconSize} />
                    <span className={`hidden md:inline`}>Copy Link</span>
                </button>
            </div>
        </div>
    );
};
