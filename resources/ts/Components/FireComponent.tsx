import React, { useEffect, useState } from "react";
import { GeoHit } from "../types/FireHit";
import FireContent from "./FireContent";

interface FireComponentProps {
    fire: GeoHit;
    onClick: (fire: GeoHit) => void;
    currentFire: GeoHit | null;
}

const FireComponent: React.FC<FireComponentProps> = ({ fire, onClick, currentFire }) => {
    const isSelected = currentFire?.objectID === fire.objectID;

    return (
        <div
            className={`p-4 flex space-x-4 border rounded-md cursor-pointer ${
                isSelected ? "border-orange border-2" : "border-grey-border"
            }`}
            onClick={() => onClick(fire)}
        >
            <FireContent fire={fire} />
        </div>
    );
};

export default FireComponent;
