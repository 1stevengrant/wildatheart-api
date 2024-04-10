import React from "react";

import { imageGenerator } from "@/helpers/user";

interface IUserAvatarProps {
    className?: string;
    width?: number;
    image?: string;
    alt?: string;
}

const UserAvatar = ({
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
            src={imageGenerator(image, {
                width: width,
            })}
            alt={alt}
        />
    );
};

export default UserAvatar;
