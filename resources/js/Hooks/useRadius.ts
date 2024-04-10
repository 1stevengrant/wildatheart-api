import { useRecoilState } from "recoil";

import { radiusState } from "@/store/filtersAtom";

export const useRadius = () => {
    const [radius, setRadius] = useRecoilState(radiusState);

    return { radius, setRadius };
};
