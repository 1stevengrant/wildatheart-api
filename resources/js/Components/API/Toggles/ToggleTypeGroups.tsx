import { ExperiencesIcon } from "@/Components/Icons/ExperiencesIcon";
import { FiresIcon } from "@/Components/Icons/FiresIcon";
import { RetreatsIcon } from "@/Components/Icons/RetreatsIcon";
import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import {
    activeQuickFilterClasses,
    inactiveQuickFilterClasses,
    quickFilterButtonClasses,
} from "@/helpers/quickFilterButtonClasses";
import { useDates } from "@/Hooks/useDates";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";

type GroupType =
    | typeof fireTypeGroup
    | typeof retreatTypeGroup
    | typeof experienceTypeGroup;

export const ToggleTypeGroups = () => {
    const { setPopupInfo } = usePopupInfo();
    const { setCurrentSex } = useSex();
    const { experienceTypeIds, retreatTypeIds, fireTypeIds, setTypesTo } =
        useTypes();
    const { typeGroups, typeGroup, setTypeGroup } = useTypeGroup();
    const { resetAllDates } = useDates();

    const handleTypeGroup = (group: GroupType) => {
        resetAllDates();
        setPopupInfo(null);
        setCurrentSex("");

        // Define a mapping from group to type IDs with explicit typing to satisfy TypeScript.
        const groupToTypeIds: Record<
            GroupType,
            | typeof fireTypeIds
            | typeof retreatTypeIds
            | typeof experienceTypeIds
        > = {
            [fireTypeGroup]: fireTypeIds,
            [retreatTypeGroup]: retreatTypeIds,
            [experienceTypeGroup]: experienceTypeIds,
        };

        if (typeGroup === group) {
            setTypeGroup(""); // Clears the type group if it's already selected
        } else {
            setTypeGroup(group);
            // Use the mapping to set types, with an assertion that group is a key of groupToTypeIds.
            setTypesTo(groupToTypeIds[group]);
        }
    };

    return (
        <>
            {typeGroups.map((group: string, index: number) => {
                return (
                    <button
                        onClick={() => handleTypeGroup(group as GroupType)}
                        key={index}
                        className={`${quickFilterButtonClasses} ${typeGroup === group ? activeQuickFilterClasses : inactiveQuickFilterClasses}`}
                    >
                        {group === fireTypeGroup && <FiresIcon />}
                        {group === retreatTypeGroup && <RetreatsIcon />}
                        {group === experienceTypeGroup && <ExperiencesIcon />}
                        <div>{group}s</div>
                    </button>
                );
            })}
        </>
    );
};
