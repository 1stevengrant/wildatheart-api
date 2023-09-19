import React from 'react';
import UserAvatar from "@/Components/UserAvatar";

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
                    <UserAvatar
                        image={props.fire.hostPhoto}
                        width={32}/>

                    <div className="text-[#BABABA] text-xs leading-tighter">
                        Hosted by {props.fire.host}
                    </div>
                </div>
            </div>
        </>
    );
}
export default FireContent
