import React from "react";

import { imageGenerator } from "@/helpers/user";

export const EventHeader: React.FC<{ title: string; image?: string }> = ({
    title,
    image,
}) => (
    <div className="flex items-center space-x-4 mb-4">
        {image && (
            <img
                alt={title}
                className="rounded-xl"
                src={imageGenerator(image, { width: 150, height: 150 })}
            />
        )}
        <h1 className="text-[24px] font-bold leading-tight">{title}</h1>
    </div>
);
