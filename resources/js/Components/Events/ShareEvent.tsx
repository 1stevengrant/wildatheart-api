import React from "react";
import {ShareIcon} from "@heroicons/react/24/outline";
import Modal from "@/Components/Ui/Modal";
import {XMarkIcon} from "@heroicons/react/24/solid";
import {Event} from "@/types/Event";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";
import {usePage} from "@inertiajs/react";

const ShareEvent= () => {
    const [isOpen, setOpen] = React.useState(false);
    const [reactNotifications, setReactNotifications] = useRecoilState(reactNotificationAtom);
    const page = usePage();
    const event = page.props.event as Event;
    const shareLinks = [
        {
            title: 'Facebook',
            url: `https://www.facebook.com/sharer/sharer.php?u=${event.url}`,
        },
        {
            title: 'Twitter',
            url: `https://twitter.com/share?url=${event.url}&text=${event.name} hosted by ${event.host}`,
        },
        {
            title: 'LinkedIn',
            url: `https://www.linkedin.com/shareArticle?url=${event.url}&title=${event.name}&source=${event.url}`,
        },
        {
            title: 'Email',
            url: `mailto:?subject=Check out this Fire from ${event.host}&body=${event.name} - ${event.url}`,
        }
    ];

    const handleLinkClick = (event: React.FormEvent) => {
        event.preventDefault();
        const url = (event.target as HTMLAnchorElement).href;
        window.open(url, '_blank')
        setOpen(false)
    }

    const copyToClipboard = async (): Promise<void> => {
        try {
            await navigator.clipboard.writeText(event.url);
            setReactNotifications(prevState => [...prevState, {
                message: 'URL copied to clipboard!',
                type: 'success',
            }]);
            setOpen(false);
        } catch (error) {
            console.error(error);
            setReactNotifications(prevState => [...prevState, {
                message: 'Oops, unable to copy',
                type: 'error',
            }]);
        }
    };


    return (
        <div>
            <button
                onClick={() => setOpen(true)}
                className={'flex items-center justify-end share-btn space-x-2 text-white'}
            >
                <ShareIcon className={'w-6 h-6'}/>
                <span>Share</span>
            </button>
            <Modal open={isOpen} setOpen={setOpen}>
                <div className={'flex justify-end mb-6'}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={'w-6 h-6 text-white'}/>
                    </button>
                </div>
                <div className="grid grid-cols-2 gap-4">
                    {shareLinks.map((link, index) => (
                        <a key={index}
                           onClick={handleLinkClick}
                           href={link.url}
                           target={'_blank'}
                           className={`rounded-lg px-2 py-1 border border-orange hover:border-black bg-orange text-white transition-colors duration-100 ease-in-out hover:bg-black text-center`}>
                            {link.title}
                        </a>
                    ))}
                    <button
                        className="col-span-2 rounded-lg border px-2 py-1 text-center text-white transition-colors duration-100 ease-in-out border-orange bg-orange hover:border-black hover:bg-black"
                        onClick={copyToClipboard}
                    >
                        Copy URL to Clipboard
                    </button>
                </div>
            </Modal>
        </div>
    )
}

export default ShareEvent;
