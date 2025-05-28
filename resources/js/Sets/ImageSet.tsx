import React from "react";

import { Image } from "@/Components/Image";

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
        <Image
            src={image}
            imageDimensions={params}
            alt={alt}
            className={className}
        />
    );
};
