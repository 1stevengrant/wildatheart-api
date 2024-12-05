import React, { useState } from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

import { EventTypeSelectButton } from "@/Components/Buttons/EventTypeSelectButton";
import { EventGroupWrapper } from "@/Components/Events/EventGroupWrapper";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { EmptyLayoutHeader } from "@/Components/Layout/Elements/EmptyLayoutHeader";
import { EmptyLayoutWrapper } from "@/Components/Layout/Elements/EmptyLayoutWrapper";
import { SnapGrid } from "@/Components/SnapGrid";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import {
    EventTypeCaptivatingFires,
    EventTypeFires,
} from "@/helpers/eventNames";
import {
    experienceTypeDescription,
    fireTypeDescription,
    retreatTypeDescription,
} from "@/helpers/eventTypes";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { EventType } from "@/types/EventType";

interface HostEventTypeSelectPageProps {
    experienceTypes: Array<EventType>;
    retreatTypes: Array<EventType>;
    fireTypes: Array<EventType>;
}

const HostEventTypeSelectPage = () => {
    const { props } = useTypedPage<HostEventTypeSelectPageProps>();
    const { experienceTypes, retreatTypes, fireTypes } = props;

    const [selectedType, setSelectedType] = useState<EventType | null>(null);
    const pageTitle = `Select an Event Type`;

    const getRouteForEventType = (eventType: EventType) => {
        switch (eventType.slug) {
            case EventTypeFires:
                return route("host.fire");
            case EventTypeCaptivatingFires:
                return route("host.captivating-fire");
            default:
                return route("host.eventType.index", eventType.slug);
        }
    };

    const getEventTypeDisplay = (eventType: EventType) => {
        switch (eventType.slug) {
            case EventTypeFires:
                return "Fire";
            case EventTypeCaptivatingFires:
                return "Captivating Fire";
            default:
                return eventType.name;
        }
    };

    return (
        <EmptyLayout title={pageTitle}>
            <EmptyLayoutWrapper>
                <EmptyLayoutHeader>
                    <HeadingOne className={`text-left`}>{pageTitle}</HeadingOne>
                </EmptyLayoutHeader>
                <EmptyLayoutBody className={`px-4 pt-20 relative`}>
                    <EventGroupWrapper
                        title={`Fires`}
                        description={fireTypeDescription}
                    >
                        <SnapGrid>
                            {fireTypes.map((type: EventType, index: number) => (
                                <EventTypeSelectButton
                                    isSelected={
                                        selectedType?.slug === type.slug
                                    }
                                    key={index}
                                    type={type}
                                    setSelectedType={setSelectedType}
                                />
                            ))}
                        </SnapGrid>
                    </EventGroupWrapper>
                    <EventGroupWrapper
                        title={`Experiences`}
                        description={experienceTypeDescription}
                    >
                        <SnapGrid>
                            {experienceTypes.map(
                                (type: EventType, index: number) => (
                                    <EventTypeSelectButton
                                        isSelected={
                                            selectedType?.slug === type.slug
                                        }
                                        key={index}
                                        type={type}
                                        setSelectedType={setSelectedType}
                                    />
                                )
                            )}
                        </SnapGrid>
                    </EventGroupWrapper>
                    <EventGroupWrapper
                        title={`Retreats`}
                        description={retreatTypeDescription}
                    >
                        <SnapGrid>
                            {retreatTypes.map(
                                (type: EventType, index: number) => (
                                    <EventTypeSelectButton
                                        isSelected={
                                            selectedType?.slug === type.slug
                                        }
                                        key={index}
                                        type={type}
                                        setSelectedType={setSelectedType}
                                    />
                                )
                            )}
                        </SnapGrid>
                    </EventGroupWrapper>
                </EmptyLayoutBody>
                <EmptyLayoutFooter>
                    <Link
                        className={circularButtonClasses}
                        href={route("home")}
                    >
                        <ArrowLeftIcon className={`w-4 h-4`} />
                    </Link>

                    {selectedType && (
                        <Link
                            className={`${primaryButtonClasses}`}
                            href={getRouteForEventType(selectedType)}
                        >
                            Create Your {getEventTypeDisplay(selectedType)}
                            <ArrowRightIcon className={`w-4 h-4 ml-2`} />
                        </Link>
                    )}
                </EmptyLayoutFooter>
            </EmptyLayoutWrapper>
        </EmptyLayout>
    );
};

export default HostEventTypeSelectPage;
