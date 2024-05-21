import { Bars3Icon } from "@heroicons/react/24/solid";
import { useSetRecoilState } from "recoil";

import { eventCreateProgressState } from "@/store/uiAtom";

export const EventCreateProgressMobile = () => {
    const setEventCreateProgressShow = useSetRecoilState(
        eventCreateProgressState
    );
    return (
        <div className={`lg:hidden`}>
            <Bars3Icon
                onClick={() => setEventCreateProgressShow(true)}
                className={`w-8 h-8`}
            />
        </div>
    );
};
