import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, usePage } from "@inertiajs/react";

import { CustomMarkdown } from "@/Components/CustomMarkdown";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { EventFaqsLink } from "@/Components/Links/EventFaqsLink";
import { EventScheduleLink } from "@/Components/Links/EventScheduleLink";
import { MailingAddressLink } from "@/Components/Links/MailingAddressLink";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { eventTypeHasFaqs } from "@/helpers/eventTypeHasFaqs";
import { eventTypeHasSchedule } from "@/helpers/eventTypeHasSchedule";
import useRoute from "@/Hooks/useRoute";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const BeforeEventTypePageFooter = () => {
    const page = usePage();
    const eventType = page.props.eventType as App.Data.EventTypeData;
    const route = useRoute();
    return (
        <EmptyLayoutFooter>
            <Link
                href={route("host.eventType.index", eventType)}
                className={circularButtonClasses}
            >
                <ArrowLeftIcon className={`w-4 h-4`} />
            </Link>
            {eventTypeHasSchedule(eventType) && (
                <EventScheduleLink eventType={eventType} />
            )}
            {!eventTypeHasSchedule(eventType) &&
                eventTypeHasFaqs(eventType) && (
                    <EventFaqsLink eventType={eventType} />
                )}
            {!eventTypeHasSchedule(eventType) &&
                !eventTypeHasFaqs(eventType) && (
                    <MailingAddressLink eventType={eventType} />
                )}
        </EmptyLayoutFooter>
    );
};

const BeforeEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    return (
        <EmptyLayout title={`Before The Event`}>
            <SidebarLayout footer={<BeforeEventTypePageFooter />}>
                <EmptyLayoutBody>
                    <PageContainer>
                        {eventType.registration_before_your_event_text && (
                            <CustomMarkdown>
                                {eventType.registration_before_your_event_text}
                            </CustomMarkdown>
                        )}
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default BeforeEventTypePage;
