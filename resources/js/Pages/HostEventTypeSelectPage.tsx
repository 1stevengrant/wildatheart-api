import React, { useState } from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import { EventGroupWrapper } from "@/Components/EventGroupWrapper";
import { EventTypeSelectButton } from "@/Components/EventTypeSelectButton";
import HeadingOne from "@/Components/Type/HeadingOne";
import {
    experienceTypeDescription,
    fireTypeDescription,
    retreatTypeDescription,
} from "@/helpers/eventTypes";
import useTypedPage from "@/Hooks/useTypedPage";
import { EventType } from "@/types/EventType";
import { SnapGrid } from "@/Components/SnapGrid";
import { Link } from "@inertiajs/react";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";

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
        <div className={`flex flex-col h-full`}>
            <header
                className={`bg-black text-white text-center p-4 fixed top-0 inset-x-0 z-10 border-b border-grey-border`}
            >
                <HeadingOne className={`text-left`}>
                    Select an Event Type
                </HeadingOne>
            </header>
            <main className={`pt-[73px] pb-[63px] px-4`}>
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
            </main>
            <footer
                className={`border-t border-grey-border bg-black text-white text-center p-4 fixed bottom-0 inset-x-0 z-10 flex items-center justify-between`}
            >
                <button
                    className={`inline-flex items-center text-sm md:text-base border w-10 h-10 justify-center
        border-transparent rounded-full bg-white hover:bg-orange text-black hover:text-white text-center font-medium transition no-underline
        ease-in-out duration-150 disabled:bg-grey-dark disabled:text-grey-white disabled:cursor-not-allowed focus:ring-0`}
                >
                    <ArrowLeftIcon className={`w-4 h-4`} />
                </button>
                {selectedType && (
                    <Link
                        className={`${primaryButtonClasses}`}
                        href={route("host.eventType.index", selectedType.slug)}
                    >
                        Create Your {selectedType?.name}
                    </Link>
                )}
            </footer>
        </div>
    );
};

export default HostEventTypeSelectPage;
