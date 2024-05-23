import React from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, usePage } from "@inertiajs/react";
import Vimeo from "@u-wave/react-vimeo";
import Markdown from "react-markdown";

import { VideoPlayer } from "@/Components/Events/Detail/VideoPlayer";
import { EmptyLayoutBody } from "@/Components/Layout/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/EmptyLayoutFooter";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useRoute from "@/Hooks/useRoute";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const WelcomeEventTypePageFooter = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    const route = useRoute();

    return (
        <EmptyLayoutFooter>
            <Link
                href={route("host.eventType.select")}
                className={circularButtonClasses}
            >
                <ArrowLeftIcon className={`w-4 h-4`} />
            </Link>
            <Link
                className={primaryButtonClasses}
                href={route("host.eventType.before", eventType)}
            >
                Before
                <ArrowRightIcon className={`w-4 h-4 ml-2`} />
            </Link>
        </EmptyLayoutFooter>
    );
};

const WelcomeEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;

    const getVimeoId = (url: string) => {
        const parts = url.split("/");
        return parts[parts.length - 1];
    };

    return (
        <EmptyLayout title={"Welcome"}>
            <SidebarLayout footer={<WelcomeEventTypePageFooter />}>
                <EmptyLayoutBody>
                    <PageContainer>
                        {eventType.registration_welcome_video &&
                            (eventType.registration_welcome_video.includes(
                                "mp4"
                            ) ? (
                                <VideoPlayer
                                    src={eventType.registration_welcome_video}
                                />
                            ) : (
                                <Vimeo
                                    className={`rounded-2xl`}
                                    responsive={true}
                                    video={getVimeoId(
                                        eventType.registration_welcome_video
                                    )}
                                />
                            ))}
                        {eventType.registration_welcome_text && (
                            <Markdown>
                                {eventType.registration_welcome_text}
                            </Markdown>
                        )}
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default WelcomeEventTypePage;
