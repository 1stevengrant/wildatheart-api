import React, { useState } from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

import { CircularButton } from "@/Components/Buttons/CircularButton";
import { EventGroupWrapper } from "@/Components/EventGroupWrapper";
import { EventTypeSelectButton } from "@/Components/EventTypeSelectButton";
import { EmptyLayoutBody } from "@/Components/Layout/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/EmptyLayoutFooter";
import { EmptyLayoutHeader } from "@/Components/Layout/EmptyLayoutHeader";
import { EmptyLayoutWrapper } from "@/Components/Layout/EmptyLayoutWrapper";
import { SnapGrid } from "@/Components/SnapGrid";
import HeadingOne from "@/Components/Type/HeadingOne";
import {
    experienceTypeDescription,
    fireTypeDescription,
    retreatTypeDescription,
} from "@/helpers/eventTypes";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useTypedPage from "@/Hooks/useTypedPage";
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
    return (
        <EmptyLayoutWrapper>
            <EmptyLayoutHeader>
                <HeadingOne className={`text-left`}>
                    Select an Event Type
                </HeadingOne>
            </EmptyLayoutHeader>
            <EmptyLayoutBody>
                <EventGroupWrapper
                    title={`Fires`}
                    description={fireTypeDescription}
                >
                    <SnapGrid>
                        {fireTypes.map((type: EventType, index: number) => (
                            <EventTypeSelectButton
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
                        {retreatTypes.map((type: EventType, index: number) => (
                            <EventTypeSelectButton
                                key={index}
                                type={type}
                                setSelectedType={setSelectedType}
                            />
                        ))}
                    </SnapGrid>
                </EventGroupWrapper>
            </EmptyLayoutBody>
            <EmptyLayoutFooter>
                <CircularButton>
                    <ArrowLeftIcon className={`w-4 h-4`} />
                </CircularButton>
                {selectedType && (
                    <Link
                        className={`${primaryButtonClasses}`}
                        href={route("host.eventType.index", selectedType.slug)}
                    >
                        Create Your {selectedType?.name}
                    </Link>
                )}
            </EmptyLayoutFooter>
        </EmptyLayoutWrapper>
    );
};

export default HostEventTypeSelectPage;
