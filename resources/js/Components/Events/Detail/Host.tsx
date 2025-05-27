import React from "react";

import { UserAvatar } from "@/Components/UserAvatar";

type HostProps = {
    className?: string;
    host: string | null;
    host_photo?: string | null;
};

export const Host = ({ className = "", host, host_photo }: HostProps) => {
    return (
        <div className={`${className}`}>
            <div className="flex items-start space-x-4">
                {host_photo && (
                    <UserAvatar image={host_photo} className={`w-20 h-20`} />
                )}
                <div>
                    <p className="text-sm">Your Host ·</p>
                    <p className="capitalize text-2xl font-medium">{host}</p>
                </div>
            </div>
        </div>
    );
};
