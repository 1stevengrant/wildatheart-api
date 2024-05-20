import React from "react";

import { imageGenerator } from "@/helpers/user";
import { EventType } from "@/types/EventType";

export const EventTypeSelectButton = ({
    type,
    setSelectedType,
    isSelected,
}: {
    type: EventType;
    setSelectedType: (type: EventType) => void;
    isSelected: boolean;
}) => {
    return (
        <button
            onClick={() => setSelectedType(type)}
            className={`border-4 rounded-2xl ${isSelected ? "border-white" : "border-grey-border"}`}
        >
            <img
                className={`rounded-xl`}
                src={imageGenerator(type.image, { width: 100, height: 100 })}
                alt={type.name}
            />
        </button>
    );
};
