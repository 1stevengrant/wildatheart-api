import { useState } from "react";

import { PaperAirplaneIcon } from "@heroicons/react/24/outline";
import { XMarkIcon } from "@heroicons/react/24/solid";
import { usePage } from "@inertiajs/react";
import { useSetRecoilState } from "recoil";

import { Modal } from "@/Components/Ui/Modal";
import { reactNotificationState } from "@/store";
import { Event } from "@/types/Event";

export const ShareEvent = ({ className = "" }: { className?: string }) => {
    const [isOpen, setOpen] = useState(false);
    const setReactNotifications = useSetRecoilState(reactNotificationState);
    const page = usePage();
    const event = page.props.event as Event;
    const shareLinks = [
        {
            title: "Facebook",
            url: `https://www.facebook.com/sharer/sharer.php?u=${event.url}`,
        },
        {
            title: "Twitter",
            url: `https://twitter.com/share?url=${event.url}&text=${event.name} hosted by ${event.host}`,
        },
        {
            title: "LinkedIn",
            url: `https://www.linkedin.com/shareArticle?url=${event.url}&title=${event.name}&source=${event.url}`,
        },
        {
            title: "Email",
            url: `mailto:?subject=Check out this Fire from ${event.host}&body=${event.name} - ${event.url}`,
        },
    ];

    const handleLinkClick = (event: React.FormEvent) => {
        event.preventDefault();
        const url = (event.target as HTMLAnchorElement).href;
        window.open(url, "_blank");
        setOpen(false);
    };

    const copyToClipboard = async (): Promise<void> => {
        try {
            await navigator.clipboard.writeText(event.url);
            setReactNotifications((prevState) => [
                ...prevState,
                {
                    message: "URL copied to clipboard!",
                    type: "success",
                },
            ]);
            setOpen(false);
        } catch (error) {
            console.error(error);
            setReactNotifications((prevState) => [
                ...prevState,
                {
                    message: "Oops, unable to copy",
                    type: "error",
                },
            ]);
        }
    };

    return (
        <div
            className={`${className} shadow-xl hover:shadow-none transition absolute top-[20px] right-4 bg-white z-10 flex items-center space-x-2 rounded-full px-4 py-2 text-black font-medium hover:text-orange duration-200 ease-in-out`}
        >
            <button
                className={`flex items-center space-x-2 rounded-full`}
                onClick={() => setOpen(true)}
            >
                <PaperAirplaneIcon className={"w-6 h-6 -rotate-45"} />
                <span>Share</span>
            </button>
            <Modal open={isOpen} setOpen={setOpen}>
                <div className={"flex justify-between mb-6"}>
                    <p className={`font-bold`}>Share Event</p>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={"w-6 h-6 text-white"} />
                    </button>
                </div>
                <div className="grid grid-cols-2 gap-4">
                    {shareLinks.map((link, index) => (
                        <a
                            key={index}
                            onClick={handleLinkClick}
                            href={link.url}
                            target={"_blank"}
                            className={`rounded-lg px-2 py-1 border border-p200 hover:border-black bg-p200 text-white transition-colors duration-100 ease-in-out hover:bg-black text-center`}
                        >
                            {link.title}
                        </a>
                    ))}
                    <button
                        className="col-span-2 rounded-lg border px-2 py-1 text-center text-white transition-colors duration-100 ease-in-out border-p200 bg-p200 hover:border-black hover:bg-black"
                        onClick={copyToClipboard}
                    >
                        Copy URL to Clipboard
                    </button>
                </div>
            </Modal>
        </div>
    );
};
