import React from "react";

import { imageGenerator } from "@/helpers/user";
import { EventType } from "@/types/EventType";

export const EventTypeSelectButton = ({
    type,
    setSelectedType,
}: {
    type: EventType;
    setSelectedType: (type: EventType) => void;
}) => {
    return (
        <button onClick={() => setSelectedType(type)}>
            <img
                className={`rounded-xl border border-grey-darker`}
                src={imageGenerator(type.image, { width: 100, height: 100 })}
                alt={type.name}
            />
        </button>
    );
};
