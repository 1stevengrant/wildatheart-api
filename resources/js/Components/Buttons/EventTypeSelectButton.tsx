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
            className={`border-4 rounded-2xl relative ${isSelected ? "border-white" : "border-grey-border"}`}
        >
            {type.image ? (
                <img
                    className={`rounded-xl`}
                    srcSet={`${imageGenerator(type.image, {
                        width: 100,
                        height: 100,
                    })} 100w, ${imageGenerator(type.image, { width: 200, height: 200 })} 200w`}
                    sizes={`(max-width: 640px) 100px, 200px`}
                    src={imageGenerator(type.image, {
                        width: 100,
                        height: 100,
                    })}
                    alt={type.name}
                />
            ) : (
                <div>{type.name}</div>
            )}
        </button>
    );
};
