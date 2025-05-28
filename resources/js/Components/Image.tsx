import React from "react";

import { imageGenerator } from "@/helpers/user";

export const Image = ({
    src,
    className = "",
    imageDimensions = { width: 100, height: 100 },
    alt = "",
}: {
    src: string;
    className?: string;
    imageDimensions?: { width?: number; height?: number };
    alt?: string;
}) => {
    return (
        <img
            src={imageGenerator(src, imageDimensions)}
            className={className}
            alt={alt}
        />
    );
};
