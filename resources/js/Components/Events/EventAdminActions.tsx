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
import { reactNotificationState } from "@/store";
import { Event } from "@/types/Event";

export const EventAdminActions = ({ event }: { event: Event }) => {
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
        <>
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
        </>
    );
};
