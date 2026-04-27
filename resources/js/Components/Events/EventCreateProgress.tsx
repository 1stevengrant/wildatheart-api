import React from "react";

import { usePage } from "@inertiajs/react";
import pluralize from "pluralize";

import { CircleIcon } from "@/Components/Icons/CircleIcon";
import { Image } from "@/Components/Image";

const EventStep = ({ step }: { step: number }) => {
    return <div className={`text-xxs text-n40`}>Step {step}</div>;
};

const EventStepBar = ({
    isActive,
    isComplete,
}: {
    isActive: boolean;
    isComplete: boolean;
}) => {
    return (
        <div
            className={`rounded h-[50px] border w-[5px] ${isActive ? `bg-white` : `${isComplete ? `bg-p200 border-p200` : ``}`} `}
        />
    );
};

const EventStepTitle = ({ title }: { title: string }) => {
    return <div className={`font-medium`}>{title}</div>;
};

const EventListItem = ({ children }: { children: React.ReactNode }) => {
    return (
        <li className={`flex items-center space-x-2 leading-tight`}>
            {children}
        </li>
    );
};

export const EventCreateProgress = () => {
    const { eventType, eventSteps } = usePage<{
        eventType: App.Data.EventTypeData;
        eventSteps: {
            guide: {
                welcome: boolean;
                before: boolean;
                schedule: boolean;
                faqs: boolean;
            };
            mailing_address: boolean;
            event_registration: boolean;
        };
    }>().props;

    const isEventGuideStep =
        route().current("host.eventType.index") ||
        route().current("host.eventType.before") ||
        route().current("host.eventType.faqs") ||
        route().current("host.eventType.schedule");
    const isMailingAddressStep = route().current("host.eventType.mailing");
    const isEventRegistrationStep = route().current("host.eventType.create");

    const isGuideComplete = (guide: any) => {
        return guide.welcome && guide.before && guide.schedule && guide.faqs;
    };

    const eventName = pluralize.singular(eventType.name);

    return (
        <>
            {eventType && (
                <>
                    <div
                        className={`border-2 p-2 border-n700 rounded-lg mt-4 flex items-center`}
                    >
                        <div>
                            <Image
                                src={eventType.image}
                                className={`rounded-lg md:hidden`}
                                alt={eventType.name}
                            />
                            <Image
                                src={eventType.image}
                                className={`hidden rounded-lg lg:block`}
                                alt={eventType.name}
                            />
                        </div>
                        <div className={`ml-6 leading-tight`}>
                            <h2 className={`text-sm font-medium`}>
                                {eventName}
                            </h2>
                            <p className={`text-sm`}>Event setup</p>
                        </div>
                    </div>
                    <ul className={`ml-10 mt-10 space-y-6`}>
                        <li>
                            <div className={`flex`}>
                                <EventStepBar
                                    isActive={isEventGuideStep}
                                    isComplete={isGuideComplete(
                                        eventSteps.guide
                                    )}
                                />
                                <div className={`ml-6`}>
                                    <EventStep step={1} />
                                    <EventStepTitle title={`Event Guide`} />
                                </div>
                            </div>
                            <ul
                                className={`text-sm mt-4 ml-14 space-y-2 font-medium ${isEventGuideStep ? `block` : `hidden`}`}
                            >
                                <EventListItem>
                                    <span>
                                        <CircleIcon
                                            className={`${route().current("host.eventType.index") ? `fill-white` : `${eventSteps.guide.welcome ? `fill-p200` : `stroke-white`}`}`}
                                        />
                                    </span>

                                    <span>
                                        {eventType.welcome_nav_text_override ??
                                            `Welcome`}
                                    </span>
                                </EventListItem>
                                <EventListItem>
                                    <span>
                                        <CircleIcon
                                            className={`${route().current("host.eventType.before") ? `fill-white` : `${eventSteps.guide.before ? `fill-p200` : `stroke-white`}`}`}
                                        />
                                    </span>
                                    <span>
                                        {eventType.before_event_nav_text_override ??
                                            `Before Your ${eventName}`}
                                    </span>
                                </EventListItem>
                                {eventType.event_schedule &&
                                    eventType.event_schedule.length > 0 && (
                                        <>
                                            <EventListItem>
                                                <span>
                                                    <CircleIcon
                                                        className={`${route().current("host.eventType.schedule") ? `fill-white` : `${eventSteps.guide.schedule ? `fill-p200` : `stroke-white`}`}`}
                                                    />
                                                </span>
                                                <span>
                                                    {eventType.schedule_nav_text_override ??
                                                        `${eventName} Schedule`}
                                                </span>
                                            </EventListItem>
                                        </>
                                    )}
                                {eventType.event_faqs &&
                                    eventType.event_faqs.length > 0 && (
                                        <>
                                            <EventListItem>
                                                <span>
                                                    <CircleIcon
                                                        className={`${route().current("host.eventType.faqs") ? `fill-white` : `${eventSteps.guide.faqs ? `fill-p200` : `stroke-white`}`}`}
                                                    />
                                                </span>
                                                <span>
                                                    {eventType.faq_nav_text_override ??
                                                        `${eventName} FAQs`}
                                                </span>
                                            </EventListItem>
                                        </>
                                    )}
                            </ul>
                        </li>
                        <li className={`flex`}>
                            <EventStepBar
                                isActive={isMailingAddressStep}
                                isComplete={eventSteps.mailing_address}
                            />
                            <div className={`ml-6`}>
                                <EventStep step={2} />
                                <EventStepTitle title={`Mailing Address`} />
                            </div>
                        </li>
                        <li className={`flex`}>
                            <EventStepBar
                                isActive={isEventRegistrationStep}
                                isComplete={eventSteps.event_registration}
                            />
                            <div className="ml-6">
                                <EventStep step={3} />
                                <EventStepTitle title={`Event Registration`} />
                            </div>
                        </li>
                    </ul>
                </>
            )}
        </>
    );
};
