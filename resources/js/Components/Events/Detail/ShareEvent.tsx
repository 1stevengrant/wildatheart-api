import React, { useState } from "react";

import { PaperAirplaneIcon } from "@heroicons/react/24/outline";
import { XMarkIcon } from "@heroicons/react/24/solid";
import { usePage } from "@inertiajs/react";
import { useSetRecoilState } from "recoil";

import { Modal } from "@/Components/Ui/Modal";
import { reactNotificationState } from "@/store";

export const ShareEvent = ({ className = "" }: { className?: string }) => {
    const [isOpen, setOpen] = useState(false);
    const setReactNotifications = useSetRecoilState(reactNotificationState);
    const page = usePage();
    const event = page.props.event as App.Data.EventData;
    const meta = page.props.meta as App.Data.SocialMetaData | null;

    // event.url is relative under the NewEventLandingFeature flag, so resolve
    // it against the current origin to guarantee an absolute, shareable link.
    const shareUrl =
        typeof window !== "undefined"
            ? new URL(event.url, window.location.origin).href
            : event.url;
    // Prefer the server-composed social title so the share text matches the
    // unfurled preview card; event.name alone can be an awkward possessive
    // (e.g. "Duane's hosted by Duane Crabtree").
    const shareTitle = meta?.title ?? `${event.name} hosted by ${event.host}`;

    const shareLinks = [
        {
            title: "Facebook",
            url: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`,
        },
        {
            title: "Twitter",
            url: `https://twitter.com/share?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareTitle)}`,
        },
        {
            title: "LinkedIn",
            url: `https://www.linkedin.com/shareArticle?url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(shareTitle)}&source=${encodeURIComponent(shareUrl)}`,
        },
        {
            title: "Email",
            url: `mailto:?subject=${encodeURIComponent(`Check out this event from ${event.host}`)}&body=${encodeURIComponent(`${shareTitle} - ${shareUrl}`)}`,
        },
    ];

    const canUseNativeShare = (): boolean =>
        typeof navigator !== "undefined" &&
        typeof navigator.share === "function" &&
        window.matchMedia("(max-width: 767px)").matches;

    const handleShareClick = async (): Promise<void> => {
        if (!canUseNativeShare()) {
            setOpen(true);

            return;
        }

        try {
            await navigator.share({
                title: shareTitle,
                text: shareTitle,
                url: shareUrl,
            });
        } catch (error) {
            // User dismissing the native sheet rejects with AbortError; ignore
            // it and only fall back to the modal on a genuine failure.
            if (error instanceof DOMException && error.name === "AbortError") {
                return;
            }

            setOpen(true);
        }
    };

    const handleLinkClick = (event: React.FormEvent) => {
        event.preventDefault();
        const url = (event.target as HTMLAnchorElement).href;
        window.open(url, "_blank");
        setOpen(false);
    };

    const copyToClipboard = async (): Promise<void> => {
        try {
            await navigator.clipboard.writeText(shareUrl);
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
            className={`${className} shadow-xl hover:shadow-none transition absolute top-[20px] right-4 bg-white z-10 flex items-center space-x-2 rounded-full px-4 py-2 text-black font-medium hover:text-p300 duration-200 ease-in-out`}
        >
            <button
                className={`flex items-center space-x-2 rounded-full`}
                onClick={handleShareClick}
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
