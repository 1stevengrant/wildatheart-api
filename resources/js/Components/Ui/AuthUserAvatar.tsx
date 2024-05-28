import React from "react";

import { UserCircleIcon } from "@heroicons/react/24/outline";

import { UserAvatar } from "@/Components/UserAvatar";

export const AuthUserAvatar = ({ auth }: { auth: any }) => {
    return (
        <>
            {auth.user.image ? (
                <UserAvatar className={"h-10 w-10"} image={auth.user.image} />
            ) : (
                <UserCircleIcon className={"w-8 h-8"} />
            )}
        </>
    );
};
