import React, { useRef } from "react";

import Tippy from "@tippyjs/react/headless";
import { useMediaQuery } from "@uidotdev/usehooks";

import { SelectedEventType } from "@/Components/Events/SelectedEventType";
import { imageGenerator } from "@/helpers/user";
import { EventType } from "@/types/EventType";

import "tippy.js/dist/tippy.css";

export const EventTypeSelectButton = ({
    type,
    setSelectedType,
    isSelected,
}: {
    type: EventType;
    setSelectedType: (type: EventType) => void;
    isSelected: boolean;
}) => {
    const tippyRef = useRef<any>(null);
    const isSmallDevice = useMediaQuery("only screen and (max-width : 768px)");

    const handleHide = () => {
        if (tippyRef.current && tippyRef.current.hide) {
            tippyRef.current.hide();
        }
    };

    return (
        <Tippy
            trigger="click mouseenter"
            key={type.id}
            placement="bottom-start"
            onTrigger={() => {
                if (isSmallDevice) {
                    setSelectedType(type);
                }
            }}
            interactive={true}
            touch={true}
            delay={250}
            render={(attrs) => (
                <SelectedEventType
                    eventType={type}
                    attrs={attrs}
                    hideTippy={handleHide}
                />
            )}
            onMount={(instance) => {
                tippyRef.current = instance;
            }}
        >
            <button
                onClick={() => setSelectedType(type)}
                className={`border-4 rounded-2xl relative ${isSelected ? "border-white" : "border-n700 hover:border-p200"}`}
            >
                {type.image ? (
                    <img
                        className="rounded-xl"
                        srcSet={`${imageGenerator(type.image, {
                            width: 100,
                            height: 100,
                        })} 100w, ${imageGenerator(type.image, { width: 200, height: 200 })} 200w`}
                        sizes="(max-width: 640px) 100px, 200px"
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
        </Tippy>
    );
};
