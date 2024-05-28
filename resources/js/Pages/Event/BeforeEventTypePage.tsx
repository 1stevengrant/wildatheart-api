import React from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, usePage } from "@inertiajs/react";
import Markdown from "react-markdown";

import { EmptyLayoutBody } from "@/Components/Layout/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/EmptyLayoutFooter";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useRoute from "@/Hooks/useRoute";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const BeforeEventTypePageFooter = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    const route = useRoute();
    return (
        <EmptyLayoutFooter>
            <Link
                href={route("host.eventType.index", eventType)}
                className={circularButtonClasses}
            >
                <ArrowLeftIcon className={`w-4 h-4`} />
            </Link>
            <Link
                className={primaryButtonClasses}
                href={route("host.eventType.schedule", eventType)}
            >
                Schedule
                <ArrowRightIcon className={`w-4 h-4 ml-2`} />
            </Link>
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
                            <Markdown>
                                {eventType.registration_before_your_event_text}
                            </Markdown>
                        )}
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default BeforeEventTypePage;
