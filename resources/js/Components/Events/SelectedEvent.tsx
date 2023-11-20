import React from "react";
import EventPopup from "./EventPopup";
import FireMarkerBlack from "./FireMarkerBlack";

const SelectedEvent = () => {
    return (
        <div>
            <FireMarkerBlack className={'md:hidden w-8'}/>
            <EventPopup/>
        </div>
    );
};

export default SelectedEvent;
