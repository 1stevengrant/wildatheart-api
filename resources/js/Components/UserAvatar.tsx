import React from "react";

interface IUserAvatarProps {
    className?: string;
    image?: string;
    alt?: string;
}

export const UserAvatar = ({
    className = "h-6 w-6",
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
