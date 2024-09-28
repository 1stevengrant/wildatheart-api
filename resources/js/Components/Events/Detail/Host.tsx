import React from "react";

import { usePage } from "@inertiajs/react";

import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { UserAvatar } from "@/Components/UserAvatar";
import { Event } from "@/types/Event";

type HostProps = {
    className?: string;
    host: string;
    hostPhoto?: string;
};

export const Host = ({ className = "", host, hostPhoto }: HostProps) => {
    const page = usePage();
    const event = page.props.event as Event;
    const { full_name, md5email } = event.hostObject;
    return (
        <div className={`${className}`}>
            <div className="flex items-start space-x-4">
                {hostPhoto ? (
                    <UserAvatar image={hostPhoto} className={`w-20 h-20`} />
                ) : (
                    <FallbackAvatar
                        name={full_name}
                        md5email={md5email}
                        size={60}
                    />
                )}

                <div>
                    <p className="text-sm">Your Host ·</p>
                    <p className="text-2xl font-medium">{host}</p>
                </div>
            </div>
        </div>
    );
};
