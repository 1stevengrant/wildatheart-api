import React from "react";

import { useRecoilState } from "recoil";

import { selectedPlaceState } from "@/store/mapAtom";

const SelectedPlace = () => {
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    return (
        <div>
            {selectedPlace ? (
                <>
                    <p>Coordinates: {selectedPlace.center.join(", ")}</p>
                </>
            ) : null}
        </div>
    );
};

export default SelectedPlace;
