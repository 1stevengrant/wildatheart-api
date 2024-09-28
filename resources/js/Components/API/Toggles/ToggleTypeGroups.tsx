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

export const ToggleTypeGroups = () => {
    const { setPopupInfo } = usePopupInfo();
    const { setCurrentSex } = useSex();
    const { typeGroup, setTypeGroup } = useTypeGroup();
    const { resetAllDates } = useDates();

    const groups = [fireTypeGroup, retreatTypeGroup, experienceTypeGroup];

    const handleTypeGroup = (group: any) => {
        resetAllDates();
        setPopupInfo(null);
        setCurrentSex("");
        if (typeGroup === group) {
            setTypeGroup("");
        } else {
            setTypeGroup(group);
        }
    };

    return (
        <>
            {groups.map((group: string, index: number) => {
                return (
                    <button
                        className={`${quickFilterButtonClasses} ${typeGroup === group ? activeQuickFilterClasses : inactiveQuickFilterClasses}`}
                        onClick={() => handleTypeGroup(group)}
                        key={index}
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
