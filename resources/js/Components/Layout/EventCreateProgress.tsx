import React from "react";

import { XMarkIcon } from "@heroicons/react/24/solid";
import { usePage } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import { CircleIcon } from "@/Components/Icons/NewIcons/CircleIcon";
import Logo from "@/Components/Logo";
import { imageGenerator } from "@/helpers/user";
import { eventCreateProgressState } from "@/store/uiAtom";

const EventStep = ({ step }: { step: number }) => {
    return <div className={`text-xs text-grey-200`}>Step {step}</div>;
};

const EventStepBar = ({ isActive }: { isActive: boolean }) => {
    return (
        <div
            className={`rounded h-[50px] border border-white w-[5px] ${isActive ? `bg-white` : `bg-transparent`}`}
        />
    );
};

const EventStepTitle = ({ title }: { title: string }) => {
    return <div className={`font-medium`}>{title}</div>;
};

const EventListItem = ({ children }: { children: React.ReactNode }) => {
    return <li className={`flex items-center space-x-2`}>{children}</li>;
};

export const EventCreateProgress = () => {
    const [eventCreateProgressShow, setEventCreateProgressShow] =
        useRecoilState(eventCreateProgressState);
    const { eventType } = usePage().props as any;

    const isEventGuideStep =
        route().current("host.eventType.index") ||
        route().current("host.eventType.before") ||
        route().current("host.eventType.faqs") ||
        route().current("host.eventType.schedule");
    const isMailingAddressStep = route().current("host.eventType.mailing");
    const isEventRegistrationStep = route().current("host.eventType.create");

    return (
        <div
            className={`z-50 p-4 absolute top-0 left-0 bg-black h-[100dvh] w-full ${eventCreateProgressShow ? `block` : `hidden`}`}
        >
            <div className={`flex justify-between`}>
                <Logo
                    className={
                        "w-[125px] flex-shrink-0 md:w-[100px] lg:w-[150px] mr-2"
                    }
                />
                <XMarkIcon
                    onClick={() => setEventCreateProgressShow(false)}
                    className={`w-8 h-8`}
                />
            </div>
            {eventType && (
                <>
                    <div
                        className={`border-2 p-2 border-grey-border rounded-lg mt-4 flex items-center`}
                    >
                        <div>
                            <img
                                className={`rounded-lg`}
                                src={imageGenerator(eventType.image, {
                                    width: 100,
                                    height: 100,
                                })}
                                alt={eventType.name}
                            />
                        </div>
                        <div className={`ml-6`}>
                            <h2 className={`text-sm font-medium`}>
                                {eventType.name}
                            </h2>
                            <p className={`text-sm`}>Event setup</p>
                        </div>
                    </div>
                    <ul className={`ml-10 mt-10 space-y-6`}>
                        <li>
                            <div className={`flex`}>
                                <EventStepBar isActive={isEventGuideStep} />
                                <div className={`ml-6`}>
                                    <EventStep step={1} />
                                    <EventStepTitle title={`Event Guide`} />
                                </div>
                            </div>
                            <ul
                                className={`text-sm mt-4 ml-14 space-y-2 font-medium ${isEventGuideStep ? `block` : `hidden`}`}
                            >
                                <EventListItem>
                                    <CircleIcon
                                        fill={`${route().current("host.eventType.index") ? `white` : ``}`}
                                    />
                                    <span>Welcome</span>
                                </EventListItem>
                                <EventListItem>
                                    <CircleIcon
                                        fill={`${route().current("host.eventType.before") ? `white` : ``}`}
                                    />
                                    <span>Before Your {eventType.name}</span>
                                </EventListItem>
                                <EventListItem>
                                    <CircleIcon
                                        fill={`${route().current("host.eventType.schedule") ? `white` : ``}`}
                                    />
                                    <span>{eventType.name} Schedule</span>
                                </EventListItem>
                                <EventListItem>
                                    <CircleIcon
                                        fill={`${route().current("host.eventType.faqs") ? `white` : ``}`}
                                    />
                                    <span>{eventType.name} FAQs</span>
                                </EventListItem>
                            </ul>
                        </li>
                        <li className={`flex`}>
                            <EventStepBar isActive={isMailingAddressStep} />
                            <div className={`ml-6`}>
                                <EventStep step={2} />
                                <EventStepTitle title={` Mailing Address`} />
                            </div>
                        </li>
                        <li className={`flex`}>
                            <EventStepBar isActive={isEventRegistrationStep} />
                            <div className="ml-6">
                                <EventStep step={3} />
                                <EventStepTitle title={`Event Registration`} />
                            </div>
                        </li>
                    </ul>
                </>
            )}
        </div>
    );
};
