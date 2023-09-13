import React from "react";
import FirePopup from "./FirePopup";
import FireMarkerBlack from "./FireMarkerBlack";

const SelectedFire = () => {
    return (
        <div>
            <FireMarkerBlack className={'md:hidden w-8'}/>
            <FirePopup/>
        </div>
    );
};

export default SelectedFire;
