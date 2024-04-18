import { ExperiencesIcon } from "@/Components/Icons/NewIcons/ExperiencesIcon";
import { FiresIcon } from "@/Components/Icons/NewIcons/FiresIcon";
import { RetreatsIcon } from "@/Components/Icons/NewIcons/RetreatsIcon";
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

    const handleTypeGroup = (group: string) => {
        resetAllDates();
        setPopupInfo(null);
        setCurrentSex("");

        // check if the group is already selected
        if (typeGroup === group) {
            setTypeGroup("");
            setTypesTo([]);
        } else {
            setTypeGroup(group as GroupType);
            switch (group) {
                case fireTypeGroup:
                    setTypesTo(fireTypeIds);
                    break;
                case retreatTypeGroup:
                    setTypesTo(retreatTypeIds);
                    break;
                case experienceTypeGroup:
                    setTypesTo(experienceTypeIds);
                    break;
            }
        }
    };

    return (
        <>
            {typeGroups.map((group: string, index: number) => {
                return (
                    <button
                        onClick={() => handleTypeGroup(group)}
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
