import React, { useState } from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

import { EventTypeSelectButton } from "@/Components/Buttons/EventTypeSelectButton";
import { EventGroupWrapper } from "@/Components/EventGroupWrapper";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { EmptyLayoutHeader } from "@/Components/Layout/Elements/EmptyLayoutHeader";
import { EmptyLayoutWrapper } from "@/Components/Layout/Elements/EmptyLayoutWrapper";
import { SnapGrid } from "@/Components/SnapGrid";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
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
    const title = `Select an Event Type`;
    return (
        <EmptyLayout title={title}>
            <EmptyLayoutWrapper>
                <EmptyLayoutHeader>
                    <HeadingOne className={`text-left`}>{title}</HeadingOne>
                </EmptyLayoutHeader>
                <EmptyLayoutBody className={`px-4 pt-20`}>
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
                            href={route(
                                "host.eventType.index",
                                selectedType.slug
                            )}
                        >
                            Create Your {selectedType?.name}
                            <ArrowRightIcon className={`w-4 h-4 ml-2`} />
                        </Link>
                    )}
                </EmptyLayoutFooter>
            </EmptyLayoutWrapper>
        </EmptyLayout>
    );
};

export default HostEventTypeSelectPage;
