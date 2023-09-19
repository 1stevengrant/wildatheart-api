import React from "react";
import {userPhoto} from "@/helpers/user";

interface UserAvatarProps {
    className?: string;
    width?: number;
    image?: string;
    alt?: string;
}

const UserAvatar: React.FC<UserAvatarProps> = ({
                                                   className = 'w-6 h-6',
                                                   width = 200,
                                                   image,
                                                   alt = ''
                                               }) => {
    return (
        <img className={className + ' rounded-full inline-block flex-shrink-0'}
             src={userPhoto(image, {
                 width: width
             })}
             alt={alt}/>
    )
}

export default UserAvatar
