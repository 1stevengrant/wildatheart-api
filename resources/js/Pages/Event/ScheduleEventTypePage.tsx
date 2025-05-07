import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, usePage } from "@inertiajs/react";
import Vimeo from "@u-wave/react-vimeo";

import { CustomMarkdown } from "@/Components/CustomMarkdown";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { EventFaqsLink } from "@/Components/Links/EventFaqsLink";
import { MailingAddressLink } from "@/Components/Links/MailingAddressLink";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { eventTypeHasFaqs } from "@/helpers/eventTypeHasFaqs";
import { getVimeoId } from "@/helpers/getVimeoId";
import { FlattenedType } from "@/interfaces/FlattenedType";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const ScheduleEventTypePageFooter = ({ eventType }: { eventType: any }) => {
    return (
        <EmptyLayoutFooter>
            <Link
                className={circularButtonClasses}
                href={route("host.eventType.before", eventType)}
            >
                <ArrowLeftIcon className={`w-4 h-4`} />
            </Link>
            {eventTypeHasFaqs(eventType) && (
                <EventFaqsLink eventType={eventType} />
            )}
            {!eventTypeHasFaqs(eventType) && (
                <MailingAddressLink eventType={eventType} />
            )}
        </EmptyLayoutFooter>
    );
};
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
        <EmptyLayout title={`Event Schedule`}>
            <SidebarLayout
                footer={<ScheduleEventTypePageFooter eventType={eventType} />}
            >
                <EmptyLayoutBody>
                    <PageContainer>
                        {eventTypeSchedule &&
                            eventTypeSchedule.map(
                                (schedule: FlattenedType, index: number) => {
                                    switch (schedule.type) {
                                        case "video":
                                            return (
                                                <Vimeo
                                                    key={index}
                                                    className={`rounded-2xl`}
                                                    responsive={true}
                                                    video={getVimeoId(
                                                        schedule.video_url
                                                    )}
                                                />
                                            );
                                        case "text":
                                            return (
                                                <CustomMarkdown key={index}>
                                                    {schedule.text_block}
                                                </CustomMarkdown>
                                            );
                                        default:
                                            return (
                                                <span
                                                    key={index}
                                                    className={`unknown-block-type`}
                                                />
                                            );
                                    }
                                }
                            )}
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default ScheduleEventTypePage;
