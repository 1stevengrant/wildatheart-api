import { imageGenerator } from "@/helpers/user";

type ImageProps = {
    className?: string;
    width?: number;
    image?: string;
    alt?: string;
};

export const Image = ({
    className = "",
    width = 600,
    image,
    alt = "",
}: ImageProps) => {
    return (
        <img
            className={`rounded-xl object-cover ${className}`}
            src={imageGenerator(image, {
                width: width,
            })}
            alt={alt}
        />
    );
};
