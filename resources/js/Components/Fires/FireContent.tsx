import React from 'react';
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";

const FireContent = (props: any) => {
    return (
        <>
            <img
                src='/img/wah-fires-suburb-1.webp'
                className="rounded-md flex-grow-0"
                alt="" />

            <div className="flex flex-col justify-between relative">
                <div>
                    <p className="text-white font-semibold">{props.fire.name} Fire</p>

                    <p className="text-white text-sm">{props.fire.location}</p>
                </div>
                <div className="flex items-center space-x-2">
                    <>
                        {props.fire.hostPhoto ? (
                            <UserAvatar
                                image={props.fire.hostPhoto}
                                width={32}/>
                        ) : (
                            <UserCircleIcon className={'w-6 h-6 text-white'} />
                        )}
                    </>
                    <div className="text-grey-200 text-xs leading-tighter">
                        Hosted by {props.fire.host}
                    </div>
                </div>
            </div>
        </>
    );
}
export default FireContent
