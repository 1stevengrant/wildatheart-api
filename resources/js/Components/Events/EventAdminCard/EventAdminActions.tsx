import { useEffect, useState } from "react";

import {
    ClipboardDocumentCheckIcon,
    EllipsisHorizontalIcon,
    PencilIcon,
    UserIcon,
} from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import { EventDeleteModal } from "@/Components/Events/EventAdminCard/Modals/EventDeleteModal";
import { EventPublicPrivateModal } from "@/Components/Events/EventAdminCard/Modals/EventPublicPrivateModal";
import { EventPublishModal } from "@/Components/Events/EventAdminCard/Modals/EventPublishModal";
import { EventUnpublishModal } from "@/Components/Events/EventAdminCard/Modals/EventUnpublishModal";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import {
    eventAdminActionButtonClass,
    eventAdminActionButtonTextClass,
} from "@/helpers/eventAdminActionButtonClass";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";
import { useOnClickOutside } from "@/Hooks/useOnClickOutside";
import { reactNotificationState } from "@/store";

export const EventAdminActions = ({ event }: { event: App.Data.EventData }) => {
    const [actionDrawerOpen, setActionDrawerOpen] = useState(false);
    const actionDrawerRef = useOnClickOutside(() => setActionDrawerOpen(false));
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
        } catch {
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
    const [openEventPublicPrivateModal, setOpenEventPublicPrivateModal] =
        useState(false);

    useEffect(() => {
        const params = route().params;
        if (params && params.event === event.id) {
            setOpenEventPublishModal(true);
        }
    }, [event]);
    return (
        <div className={`relative z-50 flex items-center justify-center `}>
            <button
                className={`flex items-center space-x-1`}
                onClick={() => setActionDrawerOpen(true)}
            >
                <EllipsisHorizontalIcon className={defaultIconSize} />
                <span>Options</span>
            </button>

            <div ref={actionDrawerRef}>
                <div
                    className={`border-black border bg-n900 p-4 rounded-lg z-20 w-[200px] shadow-lg absolute right-0 space-y-1.5 ${event.type_group !== fireTypeGroup ? `-bottom-[191px]` : `-bottom-[145px]`} ${actionDrawerOpen ? `block` : `hidden`}`}
                >
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
                        className={eventAdminActionButtonClass}
                    >
                        <PencilIcon className={defaultIconSize} />

                        <span className={eventAdminActionButtonTextClass}>
                            Edit Event
                        </span>
                    </Link>
                    <Link
                        href={`${route("my.events.edit", event)}#hosts`}
                        className={eventAdminActionButtonClass}
                    >
                        <UserIcon className={defaultIconSize} />

                        <span className={eventAdminActionButtonTextClass}>
                            Add Host
                        </span>
                    </Link>
                    <button
                        className={eventAdminActionButtonClass}
                        onClick={() => {
                            copyToClipboard().then(() => {});
                            setActionDrawerOpen(false);
                        }}
                    >
                        <ClipboardDocumentCheckIcon
                            className={defaultIconSize}
                        />
                        <span className={eventAdminActionButtonTextClass}>
                            Copy Link
                        </span>
                    </button>
                    {event.type_group !== fireTypeGroup && (
                        <EventPublicPrivateModal
                            open={openEventPublicPrivateModal}
                            setOpen={setOpenEventPublicPrivateModal}
                            event={event}
                        />
                    )}
                </div>
            </div>
        </div>
    );
};
