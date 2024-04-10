import React from "react";

import { usePage } from "@inertiajs/react";

import { FilterTypeWrapper } from "@/Components/API/Filters/FilterTypeWrapper";
import { FiresIcon } from "@/Components/Icons/NewIcons/FiresIcon";
import { SwitchComponent } from "@/Components/SwitchComponent";
import { SwitchComponentWrapper } from "@/Components/SwitchComponentWrapper";
import { useTypes } from "@/Hooks/useTypes";
import { FiresFilterProps } from "@/types/FiresFilterProps";

export const FiresFilter = () => {
    const { handleTypesChange, types } = useTypes();
    const props = usePage<FiresFilterProps>().props;
    const fireTypes = props.fireTypes;

    return (
        <div className={`border-b border-grey-darker pb-8`}>
            <FilterTypeWrapper
                icon={<FiresIcon className={`stroke-white w-8 h-8`} />}
                title={`Fires`}
                text={`A casual, organic meetup to share life and stories.`}
            />
            {fireTypes.map((eventType: any, index: number) => {
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
