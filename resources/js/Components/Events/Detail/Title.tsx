import React, {FC} from "react";
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";

interface TitleProps {
    title: String;
    children?: React.ReactNode;
}

const Title: FC<TitleProps> = ({
                                   title,
                                   children
                               }) => {
    return (

        <div className="flex items-center space-x-4">
            <h1 className="mb-1 flex items-center text-3xl leading-tighter">
                {title}
                {children}
            </h1>
        </div>
    )
}

export default Title;
