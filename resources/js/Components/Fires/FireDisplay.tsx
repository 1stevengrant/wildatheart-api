import React, {useEffect} from "react";
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";
import HostedBy from "@/Components/Fires/HostedBy";
import FireImage from "@/Components/Fires/FireImage";
import FireName from "@/Components/Fires/FireName";
import FireLocation from "@/Components/Fires/FireLocation";
import FireHostInfo from "@/Components/Fires/FireHostInfo";
import FireInfo from "@/Components/Fires/FireInfo";

interface FireDisplayProps {
    fire: any;
    className?: string;
}

const FireDisplay = ({fire, className}: FireDisplayProps) => {
    useEffect(() => {

    }, [fire]);
    return (
        <div className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-grey-border`}>
            <FireImage/>
            <FireInfo>
                <div>
                    <FireName name={fire.name}/>
                    <FireLocation location={fire.location}/>
                </div>
                <FireHostInfo>
                    <>
                        {fire.hostPhoto ? (
                            <UserAvatar
                                image={fire.hostPhoto}
                                width={32}/>
                        ) : (
                            <UserCircleIcon className={'w-6 h-6 text-white'}/>
                        )}
                    </>
                    <HostedBy host={fire.host}/>
                </FireHostInfo>
            </FireInfo>
        </div>
    );
}

export default FireDisplay;
