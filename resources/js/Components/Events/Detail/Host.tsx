import React from "react";

import { UserCircleIcon } from "@heroicons/react/24/outline";

import { UserAvatar } from "@/Components/UserAvatar";

type HostProps = {
    className?: string;
    host: string;
    hostPhoto?: string;
};

const Host = ({ className = "", host, hostPhoto }: HostProps) => {
    return (
        <div className={`${className}`}>
            <div className="flex items-start space-x-4">
                {hostPhoto ? (
                    <UserAvatar image={hostPhoto} className={`w-20 h-20`} />
                ) : (
                    <UserCircleIcon className={"w-20 h-20"} />
                )}

                <div>
                    <p className="text-sm">Your Host ·</p>
                    <p className="text-2xl font-medium">{host}</p>
                </div>
            </div>
        </div>
    );
};

export default Host;
