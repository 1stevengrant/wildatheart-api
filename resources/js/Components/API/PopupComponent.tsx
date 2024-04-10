import { Popup } from "react-map-gl/maplibre";
import { useRecoilState } from "recoil";

import { EventCard } from "@/Components/Events/EventCard";
import { popupInfoState } from "@/store/popupInfoAtom";
import { Event } from "@/types/Event";

export const PopupComponent = () => {
    const [popupInfo, setPopupInfo] = useRecoilState<Event | null>(
        popupInfoState
    );

    if (!popupInfo) {
        return null;
    }

    return (
        <Popup
            anchor="top"
            longitude={Number(popupInfo.longitude)}
            latitude={Number(popupInfo.latitude)}
            onClose={() => setPopupInfo(null)}
        >
            <EventCard event={popupInfo} />
        </Popup>
    );
};
