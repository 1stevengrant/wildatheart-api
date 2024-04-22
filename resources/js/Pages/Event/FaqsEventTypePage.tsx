import { Disclosure } from "@headlessui/react";
import { ChevronRightIcon } from "@heroicons/react/20/solid";
import { ShieldCheckIcon } from "@heroicons/react/24/outline";
import { Link, usePage } from "@inertiajs/react";

import { ButtonContainer } from "@/Components/Page/Event/ButtonContainer";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { FlattenedType } from "@/interfaces/FlattenedType";

const FaqsEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    return (
        <PageContainer>
            <h1>FAQs {eventType.name}</h1>

            {eventType.event_faqs &&
                eventType.event_faqs.map((faq: FlattenedType) => {
                    return (
                        <Disclosure>
                            {({ open }) => (
                                /* Use the `open` state to conditionally change the direction of an icon. */
                                <>
                                    <Disclosure.Button className="border-t border-grey-border py-2 text-white flex items-center justify-between w-full">
                                        <div
                                            className={`flex items-center text-grey font-semibold`}
                                        >
                                            <ShieldCheckIcon className="h-6 w-6 mr-6" />
                                            <span>{faq.fields.question}</span>
                                        </div>
                                        <ChevronRightIcon
                                            className={`w-6 h-6 text-grey  ${open ? `rotate-90 transform` : ``} `}
                                        />
                                    </Disclosure.Button>
                                    <Disclosure.Panel className="text-grey py-4 text-sm border-t border-t-grey-border">
                                        {faq.fields.answer}
                                    </Disclosure.Panel>
                                </>
                            )}
                        </Disclosure>
                    );
                })}

            <ButtonContainer>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.schedule", eventType)}
                >
                    Schedule
                </Link>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.mailing", eventType)}
                >
                    Mailing Address
                </Link>
            </ButtonContainer>
        </PageContainer>
    );
};

export default FaqsEventTypePage;
