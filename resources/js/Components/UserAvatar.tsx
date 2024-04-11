import React from "react";

interface IUserAvatarProps {
    className?: string;
    width?: number;
    image?: string;
    alt?: string;
}

export const UserAvatar = ({
    className = "h-6 w-6",
    width = 200,
    image,
    alt = "",
}: IUserAvatarProps) => {
    return (
        <img
            className={
                className +
                " rounded-full inline-block flex-shrink-0 object-cover"
            }
            src={image}
            alt={alt}
        />
    );
};
