import React from "react";

import { FolderPlusIcon, VideoCameraIcon } from "@heroicons/react/24/outline";

import { MyProfileWrapper } from "@/Components/Layout/Elements/MyProfileWrapper";
import { eventTypeBackground } from "@/helpers/eventTypeBackground";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { AppLayout } from "@/Layouts/AppLayout";
import { EventType } from "@/types/EventType";

const ResourcesPage = () => {
    const page = useTypedPage();
    const eventTypes = page.props.eventTypes as EventType[];
    const pageTitle = "Event Resources";
    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                <div
                    className={`grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-4 max-w-5xl`}
                >
                    {eventTypes.map((eventType: EventType) => (
                        <div
                            className={`p-6 bg-gradient-to-br ${eventTypeBackground(eventType.slug)} end-zinc-900 rounded-2xl shadow-xl`}
                        >
                            <img
                                alt={eventType.name}
                                className={`rounded-xl`}
                                src={imageGenerator(eventType.image, {
                                    width: 200,
                                    height: 200,
                                })}
                            />
                            <ul
                                className={`text-sm font-medium mt-4 space-y-3`}
                            >
                                <li>
                                    <a href="" className={`flex items-center`}>
                                        <FolderPlusIcon
                                            className={`w-6 h-6 mr-2`}
                                        />
                                        <div>Event Guide</div>
                                    </a>
                                </li>
                                <li>
                                    <a href="" className={`flex items-center`}>
                                        <VideoCameraIcon
                                            className={`w-6 h-6 mr-2`}
                                        />
                                        <div>Videos</div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    ))}
                </div>
            </MyProfileWrapper>
        </AppLayout>
    );
};

export default ResourcesPage;
