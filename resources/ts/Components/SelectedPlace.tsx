import React from "react";
import {selectedPlaceAtom} from "../store";
import {useRecoilState} from "recoil";

const SelectedPlace = () => {
    const [selectedPlace, setSelectedPlace] = useRecoilState(selectedPlaceAtom);
    return (
        <div>
            {selectedPlace ? (
                <>
                    <p>Coordinates: {selectedPlace.center.join(', ')}</p>
                </>
            ) : null}
        </div>
    );
};

export default SelectedPlace;
