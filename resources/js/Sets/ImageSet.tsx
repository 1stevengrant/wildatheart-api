import React from "react";

import { imageGenerator } from "@/helpers/user";

export const ImageSet = ({
    image,
    alt = "",
    className = "",
    params,
}: {
    image?: string;
    alt?: string;
    className?: string;
    params?: { [key: string]: string | number } | undefined;
}) => {
    if (!image) return null;

    return (
        <img
            alt={alt}
            className={className}
            src={imageGenerator(image, params)}
        />
    );
};
