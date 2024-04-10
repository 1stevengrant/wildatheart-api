import { useRecoilState } from "recoil";

import { popupInfoState } from "@/store/popupInfoAtom";

export const usePopupInfo = () => {
    const [popupInfo, setPopupInfo] = useRecoilState(popupInfoState);

    return { setPopupInfo, popupInfo };
};
