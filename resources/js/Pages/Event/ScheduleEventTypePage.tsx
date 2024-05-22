import React from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, usePage } from "@inertiajs/react";
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
import { FlattenedType } from "@/interfaces/FlattenedType";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const ScheduleEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    let eventTypeSchedule;
    try {
        eventTypeSchedule =
            typeof eventType.event_schedule === "string"
                ? JSON.parse(eventType.event_schedule)
                : eventType.event_schedule;
    } catch (error) {
        console.error("Error parsing event_schedule:", error);
        eventTypeSchedule = [];
    }

    return (
        <EmptyLayout title={`Schedule`}>
            <EmptyLayoutWrapper>
                <EmptyLayoutHeader>
                    <EventCreateProgressMobile />
                </EmptyLayoutHeader>
                <EmptyLayoutBody>
                    <PageContainer>
                        {eventTypeSchedule &&
                            eventTypeSchedule.map(
                                (schedule: FlattenedType, index: number) => {
                                    switch (schedule.type) {
                                        case "video-block":
                                            return (
                                                <VideoPlayer
                                                    key={index}
                                                    src={
                                                        schedule.fields
                                                            .video_url
                                                    }
                                                />
                                            );
                                        case "text-block":
                                            return (
                                                <Markdown key={index}>
                                                    {schedule.fields.text}
                                                </Markdown>
                                            );
                                        default:
                                            return (
                                                <span
                                                    className={`unknown-block-type`}
                                                />
                                            );
                                    }
                                }
                            )}
                    </PageContainer>
                </EmptyLayoutBody>
                <EmptyLayoutFooter>
                    <Link
                        className={circularButtonClasses}
                        href={route("host.eventType.before", eventType)}
                    >
                        <ArrowLeftIcon className={`w-4 h-4`} />
                    </Link>
                    <Link
                        className={primaryButtonClasses}
                        href={route("host.eventType.faqs", eventType)}
                    >
                        FAQs
                        <ArrowRightIcon className={`w-4 h-4 ml-2`} />
                    </Link>
                </EmptyLayoutFooter>
            </EmptyLayoutWrapper>
        </EmptyLayout>
    );
};

export default ScheduleEventTypePage;
