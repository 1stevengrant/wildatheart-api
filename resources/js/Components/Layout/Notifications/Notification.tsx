import { Fragment, useState, useEffect } from "react";

import { Transition } from "@headlessui/react";
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    XMarkIcon,
    ExclamationTriangleIcon,
} from "@heroicons/react/24/outline";
import { useSetRecoilState } from "recoil";

import { reactNotificationState } from "@/store";

type NotificationType = "success" | "error" | "warning" | "info";

interface NotificationProps {
    message: string;
    type?: NotificationType;
    duration?: number;
}

const NOTIFICATION_TYPES = {
    success: {
        icon: CheckCircleIcon,
        iconColor: "text-green-400",
    },
    error: {
        icon: ExclamationCircleIcon,
        iconColor: "text-red-dark",
    },
    warning: {
        icon: ExclamationTriangleIcon,
        iconColor: "text-yellow-600",
    },
    info: {
        icon: ExclamationTriangleIcon,
        iconColor: "text-yellow-600",
    },
} as const;

export const Notification = ({
    message,
    type = "success",
    duration = 2500,
}: NotificationProps) => {
    const [show, setShow] = useState(true);
    const setReactNotifications = useSetRecoilState(reactNotificationState);
    const { icon: Icon, iconColor } = NOTIFICATION_TYPES[type];

    useEffect(() => {
        const timer = setTimeout(() => {
            setShow(false);
            setReactNotifications([]);
        }, duration);
        return () => clearTimeout(timer);
    }, [duration, setReactNotifications]);

    return (
        <div className="flex w-full flex-col items-center space-y-4 sm:items-end">
            <Transition
                show={show}
                as={Fragment}
                enter="transform ease-out duration-300 transition"
                enterFrom="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enterTo="translate-y-0 opacity-100 sm:translate-x-0"
                leave="transition ease-in duration-100"
                leaveFrom="opacity-100"
                leaveTo="opacity-0"
            >
                <div className="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                    <div className="p-4">
                        <div className="flex items-start">
                            <div className="flex-shrink-0">
                                <Icon
                                    className={`h-6 w-6 ${iconColor}`}
                                    aria-hidden="true"
                                />
                            </div>
                            <div className="ml-3 w-0 flex-1 pt-0.5">
                                <p className="text-sm font-medium text-black">
                                    {message}
                                </p>
                            </div>
                            <button
                                type="button"
                                className="ml-4 inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                onClick={() => setShow(false)}
                            >
                                <XMarkIcon
                                    className="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    );
};
