import { imageGenerator } from "@/helpers/user";

interface IUserAvatarProps {
    className?: string;
    image?: string;
    alt?: string;
    height?: number;
    width?: number;
}

export const UserAvatar = ({
    className = "h-6 w-6",
    image,
    alt = "",
    height = 100,
    width = 100,
}: IUserAvatarProps) => {
    const containsBase64PNG = (
        imageString: string | undefined | null
    ): boolean => {
        return (
            imageString !== undefined &&
            imageString !== null &&
            imageString.includes("data:image/png;base64,")
        );
    };

    return (
        <img
            className={
                className +
                " rounded-full inline-block flex-shrink-0 object-cover"
            }
            src={
                containsBase64PNG(image)
                    ? image
                    : imageGenerator(image, {
                          height: height,
                          width: width,
                      })
            }
            alt={alt}
        />
    );
};
