import React from "react";

import { Disclosure } from "@headlessui/react";
import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon, ChevronRightIcon } from "@heroicons/react/20/solid";
import { ShieldCheckIcon } from "@heroicons/react/24/outline";
import { Link, usePage } from "@inertiajs/react";

import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { FlattenedType } from "@/interfaces/FlattenedType";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const FaqsEventTypePageFooter = ({ eventType }: { eventType: any }) => {
    return (
        <EmptyLayoutFooter>
            <Link
                className={circularButtonClasses}
                href={route("host.eventType.schedule", eventType)}
            >
                <ArrowLeftIcon className="h-4 w-4" />
            </Link>
            <Link
                className={primaryButtonClasses}
                href={route("host.eventType.mailing", eventType)}
            >
                Mailing Address
                <ArrowRightIcon className="ml-2 h-4 w-4" />
            </Link>
        </EmptyLayoutFooter>
    );
};
const FaqsEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    return (
        <EmptyLayout title={`Event FAQs`}>
            <SidebarLayout
                footer={<FaqsEventTypePageFooter eventType={eventType} />}
            >
                <EmptyLayoutBody>
                    <PageContainer>
                        {eventType.event_faqs &&
                            eventType.event_faqs.map(
                                (faq: FlattenedType, index: number) => {
                                    return (
                                        <Disclosure key={index}>
                                            {({ open }) => (
                                                /* Use the `open` state to conditionally change the direction of an icon. */
                                                <>
                                                    <Disclosure.Button className="flex w-full items-center justify-between border-t py-2 text-white border-n700">
                                                        <div
                                                            className={`flex items-center font-semibold`}
                                                        >
                                                            <ShieldCheckIcon className="mr-2 h-6 w-6 flex-shrink-0" />
                                                            <span
                                                                className={`text-left leading-tight`}
                                                            >
                                                                {faq.question}
                                                            </span>
                                                        </div>
                                                        <ChevronRightIcon
                                                            className={`w-6 h-6 flex-shrink-0 ${open ? `rotate-90 transform` : ``} `}
                                                        />
                                                    </Disclosure.Button>
                                                    <Disclosure.Panel className="border-t py-4  border-t-n700 text-white">
                                                        {faq.answer}
                                                    </Disclosure.Panel>
                                                </>
                                            )}
                                        </Disclosure>
                                    );
                                }
                            )}
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default FaqsEventTypePage;
