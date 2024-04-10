import React from "react";

import { FilterTypeWrapper } from "@/Components/API/Filters/FilterTypeWrapper";
import { RetreatsIcon } from "@/Components/Icons/NewIcons/RetreatsIcon";
import { SwitchComponent } from "@/Components/SwitchComponent";
import { SwitchComponentWrapper } from "@/Components/SwitchComponentWrapper";
import { useTypes } from "@/Hooks/useTypes";

export const RetreatsFilter = () => {
    const { types, handleTypesChange, retreatEventTypes } = useTypes();

    return (
        <div className={`border-b border-grey-darker py-8`}>
            <FilterTypeWrapper
                icon={<RetreatsIcon className={`stroke-white w-8 h-8`} />}
                title={`Multi-Day Retreats`}
                text={`Onsite group events with multiple sessions and lodging included.`}
            />
            {retreatEventTypes.map((eventType: any, index: number) => {
                return (
                    <SwitchComponentWrapper key={index}>
                        <span>{eventType.name}</span>
                        <SwitchComponent
                            checked={types.includes(eventType.id)}
                            onChange={() => handleTypesChange(eventType.id)}
                        />
                    </SwitchComponentWrapper>
                );
            })}
        </div>
    );
};
