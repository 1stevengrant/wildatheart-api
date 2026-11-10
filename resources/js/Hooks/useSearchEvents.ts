import { useDates } from "@/Hooks/useDates";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";

export const useSearchEvents = () => {
    const { resetAllDates } = useDates();
    const { setCurrentSex } = useSex();
    const { resetTypes } = useTypes();
    const { setTypeGroup } = useTypeGroup();

    const resetAll = () => {
        resetAllDates();
        setCurrentSex("");
        setTypeGroup("");
        resetTypes();
    };

    return { resetAll };
};
