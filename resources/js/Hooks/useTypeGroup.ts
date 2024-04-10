import { useRecoilState } from "recoil";

import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import { typeGroupState } from "@/store/filtersAtom";

export const useTypeGroup = () => {
    const [typeGroup, setTypeGroup] = useRecoilState(typeGroupState);

    const typeGroups = [retreatTypeGroup, fireTypeGroup, experienceTypeGroup];

    return { setTypeGroup, typeGroup, typeGroups };
};
