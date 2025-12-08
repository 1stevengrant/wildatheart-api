import React from "react";

import { UserAvatar } from "@/Components/UserAvatar";

type HostProps = {
    className?: string;
    host: string | null;
    host_photo?: string | null;
    managers?: { [key: number]: App.Data.EventManagerData };
};

export const Host = ({
    className = "",
    host,
    host_photo,
    managers = {},
}: HostProps) => {
    const managersArray = Object.values(managers);
    const hasHosts = managersArray.length > 0;
    const hostLabel = hasHosts ? "Your Hosts" : "Your Host";

    return (
        <div className={`${className}`}>
            <p className="text-xl md:text-3xl leading-tight md:leading-normal text-white mb-4">
                {hostLabel}
            </p>

            {/* Main Host */}
            <div className="flex items-center space-x-3 mb-4">
                {host_photo && (
                    <UserAvatar image={host_photo} className="w-12 h-12" />
                )}
                <p className="capitalize font-medium">{host}</p>
            </div>

            {/* Additional Hosts */}
            {hasHosts && (
                <div className="space-y-3">
                    {managersArray.map((manager) => (
                        <div
                            key={manager.id}
                            className="flex items-center space-x-3"
                        >
                            <UserAvatar
                                image={manager.image}
                                className="w-12 h-12"
                            />
                            <p className="capitalize font-medium">
                                {manager.fullname}
                            </p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};
