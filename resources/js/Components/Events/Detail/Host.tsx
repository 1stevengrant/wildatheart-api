import React from "react";
import {UserCircleIcon} from "@heroicons/react/24/outline";
import UserAvatar from "@/Components/UserAvatar";

type HostProps = {
    className?: string;
    host: string;
    hostPhoto?: string;
}

const Host: React.FC<HostProps> = ({
                                       className,
                                       host,
                                       hostPhoto
                                   }) => {
    return (
        <div className={`${className}`}>
            <div className="flex items-start space-x-4">
                {hostPhoto ? (
                    <UserAvatar
                        image={hostPhoto}
                        width={400}
                        className={`w-20 h-20`}/>) : (
                    <UserCircleIcon className={'w-20 h-20'}/>
                )}

                <div>
                    <p className="text-sm">Your Host ·</p>
                    <p className="text-2xl font-medium">{host}</p>
                </div>
            </div>
        </div>
    )
}

export default Host;
