import { useRecoilState } from "recoil";

import { EVENT_FOR_MEN, EVENT_FOR_WOMEN } from "@/helpers/eventSex";
import { sexState } from "@/store/filtersAtom";

export const useSex = () => {
    const [currentSex, setCurrentSex] = useRecoilState(sexState);
    const sexes: string[] = [EVENT_FOR_MEN, EVENT_FOR_WOMEN];

    return { setCurrentSex, currentSex, sexes };
};
