import React from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, usePage } from "@inertiajs/react";
import Vimeo from "@u-wave/react-vimeo";
import Markdown from "react-markdown";

import { VideoPlayer } from "@/Components/Events/Detail/VideoPlayer";
import { EmptyLayoutBody } from "@/Components/Layout/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/EmptyLayoutFooter";
import { EmptyLayoutHeader } from "@/Components/Layout/EmptyLayoutHeader";
import { EmptyLayoutWrapper } from "@/Components/Layout/EmptyLayoutWrapper";
import { EventCreateProgressMobile } from "@/Components/Layout/EventCreateProgressMobile";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useRoute from "@/Hooks/useRoute";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const WelcomeEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    const route = useRoute();

    const getVimeoId = (url: string) => {
        const parts = url.split("/");
        return parts[parts.length - 1];
    };

    return (
        <EmptyLayout title={`Welcome`}>
            <EmptyLayoutWrapper>
                <EmptyLayoutHeader>
                    <EventCreateProgressMobile />
                </EmptyLayoutHeader>
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
            </EmptyLayoutWrapper>
        </EmptyLayout>
    );
};

export default WelcomeEventTypePage;
