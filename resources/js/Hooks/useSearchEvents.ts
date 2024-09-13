import { useDates } from "@/Hooks/useDates";
import { useMapData } from "@/Hooks/useMapData";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";

export const useSearchEvents = () => {
    const { resetAllDates } = useDates();
    const { setPopupInfo } = usePopupInfo();
    const { setCurrentSex } = useSex();
    const { resetTypes } = useTypes();
    const { setTypeGroup } = useTypeGroup();
    const { setSelectedPlace } = useMapData();

    const resetAll = () => {
        setSelectedPlace(null);
        resetAllDates();
        setPopupInfo(null);
        setCurrentSex("");
        setTypeGroup("");
        resetTypes();
    };

    return { resetAll };
};
