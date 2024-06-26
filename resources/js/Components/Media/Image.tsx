import { imageGenerator } from "@/helpers/user";

export const Image = ({
    url,
    className = "",
    alt = "",
}: {
    url: string;
    className?: string;
    alt?: string;
}) => {
    return (
        <figure className={`${className}`}>
            <img
                className={`rounded-xl`}
                srcSet={`${imageGenerator(url)}`}
                src={imageGenerator(url)}
                alt={alt}
            />
        </figure>
    );
};
