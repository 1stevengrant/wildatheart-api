import React from 'react';
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";
import HostedBy from "@/Components/Fires/HostedBy";
import FireName from "@/Components/Fires/FireName";
import FireLocation from "@/Components/Fires/FireLocation";
import FireImage from "@/Components/Fires/FireImage";
import FireHostInfo from "@/Components/Fires/FireHostInfo";
import FireInfo from "@/Components/Fires/FireInfo";

interface FireContentProps {
    fire: {
        name: string;
        location: string;
        host: string;
        hostPhoto: string;
    }
}

const FireContent = (props: FireContentProps) => {
    return (
        <>
            <FireImage/>
            <FireInfo>
                <div>
                    <FireName name={props.fire.name}/>
                    <FireLocation location={props.fire.location}/>
                </div>
                <FireHostInfo>
                    <>
                        {props.fire.hostPhoto ? (
                            <UserAvatar
                                image={props.fire.hostPhoto}
                                width={32}/>
                        ) : (
                            <UserCircleIcon className={'w-6 h-6 text-white'}/>
                        )}
                    </>
                    <HostedBy host={props.fire.host}/>
                </FireHostInfo>
            </FireInfo>
        </>
    );
}
export default FireContent
