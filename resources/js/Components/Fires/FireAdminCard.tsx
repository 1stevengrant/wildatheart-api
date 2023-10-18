import React, {useState} from "react";
import {Fire} from "@/types/Fire";
import {ClipboardDocumentCheckIcon, PencilIcon} from "@heroicons/react/24/outline";
import {defaultIconSize} from "@/helpers/defaultIconSize";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";
import DeleteFireModal from "@/Components/DeleteFireModal";

interface FireAdminCardProps {
    fire: Fire
}

const FireAdminCard = ({fire}: FireAdminCardProps) => {
    const [reactNotifications, setReactNotifications] = useRecoilState(reactNotificationAtom)

    const copyToClipboard = async (): Promise<void> => {
        try {
            await navigator.clipboard.writeText(fire.url);
            const newNotification = {
                message: 'URL copied to clipboard!',
                type: 'success',
            };
            setReactNotifications([...reactNotifications, newNotification]);
        } catch (error) {
            console.error(error);
            const newErrorNotification = {
                message: 'Oops, unable to copy',
                type: 'error',
            };
            setReactNotifications([...reactNotifications, newErrorNotification]);
        }
    };

    const [open, setOpen] = useState(false)

    return (
        <div>
            <a href={fire.url} className="relative z-40">
                <div className="rounded-md border border-grey-darker bg-black p-2 shadow-lg md:p-4">
                    <div className="flex items-stretch space-x-4">
                        <img src="/img/wah-fires-suburb-1.webp"
                             className="rounded-md"
                             alt=""/>
                        <div className="relative flex flex-col justify-between">
                            <div>
                                <p className="text-white">{fire.name} Fire</p>

                                <p className="text-sm text-white">{fire.location}</p>
                            </div>
                            <div className="flex items-center space-x-2">
                                <div
                                    className="h-6 w-6 flex-shrink-0 rounded-full bg-cover bg-center object-fill"
                                    style={{backgroundImage: `url('https://wahfires.test/svgs/user.svg')`}}></div>
                                <div className="text-sm leading-tighter text-grey-200">
                                    Hosted by {fire.host}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <div
                className="relative -mt-2 flex justify-end space-x-4 rounded-lg bg-white/10 p-6 text-sm">
                <DeleteFireModal
                    open={open}
                    setOpen={setOpen}
                    fire={fire}/>
                <a href={route('my.campfires.edit', fire)}
                   className="flex items-center space-x-2 hover:text-orange">
                    <PencilIcon className={defaultIconSize}/>

                    <span className="hidden md:inline">
                        Edit Fire
                    </span>
                </a>
                <button className="flex items-center space-x-2 hover:text-orange"
                        onClick={copyToClipboard}
                >
                    <ClipboardDocumentCheckIcon className={defaultIconSize}/>
                    <span className={`hidden md:inline`}>
                        Copy Link
                    </span>
                </button>
            </div>
        </div>
    )
}

export default FireAdminCard;
