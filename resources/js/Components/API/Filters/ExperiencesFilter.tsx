import React from "react";

import { FilterTypeWrapper } from "@/Components/API/Filters/FilterTypeWrapper";
import { ExperiencesIcon } from "@/Components/Icons/NewIcons/ExperiencesIcon";
import { SwitchComponent } from "@/Components/SwitchComponent";
import { SwitchComponentWrapper } from "@/Components/SwitchComponentWrapper";
import { useTypes } from "@/Hooks/useTypes";
import { experienceTypeDescription } from "@/helpers/eventTypes";

export const ExperiencesFilter = () => {
    const { types, handleTypesChange, experienceEventTypes } = useTypes();
    return (
        <div className={`pt-8`}>
            <FilterTypeWrapper
                icon={<ExperiencesIcon className={`stroke-white w-8 h-8`} />}
                title={`Group Experiences`}
                text={experienceTypeDescription}
            />
            {experienceEventTypes.map((eventType: any, index: number) => {
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
