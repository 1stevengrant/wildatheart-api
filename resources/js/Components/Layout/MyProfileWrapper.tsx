import React from "react";

import { ProfileNav } from "@/Components/Layout/ProfileNav";
import { HeadingOne } from "@/Components/Type/HeadingOne";

export const MyProfileWrapper = ({
    children,
    title,
}: {
    children: React.ReactNode;
    title: string | React.ReactNode;
}) => {
    return (
        <div className="md:flex md:flex-row">
            <ProfileNav />
            <div className={`mb-24 p-4 md:w-5/6 md:px-7 md:py-6`}>
                <HeadingOne className={`mb-4`}>{title}</HeadingOne>
                {children}
            </div>
        </div>
    );
};
