import React from "react";

import {
    FolderPlusIcon,
    LinkIcon,
    VideoCameraIcon,
} from "@heroicons/react/24/outline";

import { ListItem } from "@/Components/EventResources/ListItem";
import { eventTypeBackground } from "@/helpers/eventTypeBackground";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { EventType } from "@/types/EventType";
import { EventTypeActionButton } from "@/types/EventTypeActionButton";
import { HeadingOne } from "@/Components/Type/HeadingOne";

const ResourcesPage = () => {
    const page = useTypedPage();
    const eventTypes = page.props.eventTypes as EventType[];
    const pageTitle = "Event Resources";
    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>
            <div
                className={`grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-4 max-w-5xl mt-12`}
            >
                {eventTypes.map((eventType: EventType) => (
                    <div
                        key={eventType.id}
                        className={`p-6 bg-gradient-to-br ${eventTypeBackground(eventType.slug)} end-zinc-900 rounded-2xl shadow-xl flex space-x-4 md:flex-col md:space-x-0`}
                    >
                        {eventType.image && (
                            <img
                                alt={eventType.name}
                                className={`rounded-xl`}
                                src={imageGenerator(eventType.image, {
                                    width: 200,
                                    height: 200,
                                })}
                            />
                        )}
                        <ul className={`text-sm font-medium mt-4 space-y-3`}>
                            <ListItem
                                icon={
                                    <FolderPlusIcon
                                        className={`w-6 h-6 mr-2`}
                                    />
                                }
                                text={`Event Guide`}
                                url={``}
                            />

                            <ListItem
                                icon={
                                    <VideoCameraIcon
                                        className={`w-6 h-6 mr-2`}
                                    />
                                }
                                text={`Videos`}
                                url={``}
                            />
                            {eventType.action_buttons &&
                                eventType.action_buttons.length > 0 && (
                                    <>
                                        {eventType.action_buttons.map(
                                            (button: EventTypeActionButton) => (
                                                <ListItem
                                                    key={button.id}
                                                    url={
                                                        button.button_url ?? ``
                                                    }
                                                    text={button.button_label}
                                                    icon={
                                                        <LinkIcon
                                                            className={`w-6 h-6 mr-2`}
                                                        />
                                                    }
                                                />
                                            )
                                        )}
                                    </>
                                )}
                        </ul>
                    </div>
                ))}
            </div>
        </HostDashboardLayout>
    );
};

export default ResourcesPage;
