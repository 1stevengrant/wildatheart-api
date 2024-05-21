import React from "react";

import { Disclosure } from "@headlessui/react";
import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon, ChevronRightIcon } from "@heroicons/react/20/solid";
import { ShieldCheckIcon } from "@heroicons/react/24/outline";
import { Link, usePage } from "@inertiajs/react";

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

const FaqsEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    return (
        <EmptyLayout title={`FAQs`}>
            <EmptyLayoutWrapper>
                <EmptyLayoutHeader>
                    <EventCreateProgressMobile />
                </EmptyLayoutHeader>
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
                                                    <Disclosure.Button className="border-t border-grey-border py-2 text-white flex items-center justify-between w-full">
                                                        <div
                                                            className={`flex items-center text-grey font-semibold`}
                                                        >
                                                            <ShieldCheckIcon className="h-6 w-6 mr-2 flex-shrink-0" />
                                                            <span
                                                                className={`text-left leading-tight`}
                                                            >
                                                                {
                                                                    faq.fields
                                                                        .question
                                                                }
                                                            </span>
                                                        </div>
                                                        <ChevronRightIcon
                                                            className={`w-6 h-6 text-grey flex-shrink-0 ${open ? `rotate-90 transform` : ``} `}
                                                        />
                                                    </Disclosure.Button>
                                                    <Disclosure.Panel className="text-grey py-4 text-sm border-t border-t-grey-border">
                                                        {faq.fields.answer}
                                                    </Disclosure.Panel>
                                                </>
                                            )}
                                        </Disclosure>
                                    );
                                }
                            )}
                    </PageContainer>
                </EmptyLayoutBody>
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
                        <ArrowRightIcon className="h-4 w-4 ml-2" />
                    </Link>
                </EmptyLayoutFooter>
            </EmptyLayoutWrapper>
        </EmptyLayout>
    );
};

export default FaqsEventTypePage;
