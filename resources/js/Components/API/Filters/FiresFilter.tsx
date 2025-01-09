import React from "react";

import { usePage } from "@inertiajs/react";

import { FilterTypeWrapper } from "@/Components/API/Filters/FilterTypeWrapper";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { SwitchComponentWrapper } from "@/Components/Forms/SwitchComponentWrapper";
import { FiresIcon } from "@/Components/Icons/FiresIcon";
import { fireTypeDescription } from "@/helpers/eventTypes";
import { useTypes } from "@/Hooks/useTypes";
import { FiresFilterProps } from "@/types/FiresFilterProps";

export const FiresFilter = () => {
    const { handleTypesChange, types } = useTypes();
    const props = usePage<FiresFilterProps>().props;
    const fireTypes = props.fireTypes;

    return (
        <div className={`border-b border-n400 pb-8`}>
            <FilterTypeWrapper
                icon={<FiresIcon className={`stroke-white w-8 h-8`} />}
                title={`Fires`}
                text={fireTypeDescription}
            />
            {fireTypes.map((eventType: any, index: number) => {
                return (
                    <SwitchComponentWrapper key={index}>
                        <span>{eventType.filter_name ?? eventType.name}</span>
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
