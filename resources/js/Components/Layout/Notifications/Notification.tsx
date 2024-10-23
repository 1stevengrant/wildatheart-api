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

interface INotificationProps {
    message: string;
    type?: "success" | "error" | "warning" | "info";
    duration?: number;
}

export const Notification = ({
    message,
    type = "success",
    duration = 5000,
}: INotificationProps) => {
    const [show, setShow] = useState(true);
    const setReactNotifications = useSetRecoilState(reactNotificationState);

    useEffect(() => {
        const timer = setTimeout(() => {
            setShow(false);
            setReactNotifications([]);
        }, duration);
        return () => clearTimeout(timer);
    });

    const getIcon = () => {
        switch (type) {
            case "success":
                return (
                    <CheckCircleIcon
                        className="h-6 w-6 text-green-400"
                        aria-hidden="true"
                    />
                );
            case "error":
                return (
                    <ExclamationCircleIcon
                        className="h-6 w-6 text-red-dark"
                        aria-hidden="true"
                    />
                );
            case "warning":
            case "info":
                return (
                    <ExclamationTriangleIcon
                        className="h-6 w-6 text-yellow-600"
                        aria-hidden="true"
                    />
                );
        }
    };

    return (
        <>
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
                                <div className="flex-shrink-0">{getIcon()}</div>
                                <div className="ml-3 w-0 flex-1 pt-0.5">
                                    <p className="text-sm font-medium text-black">
                                        {message}
                                    </p>
                                </div>
                                <div className="ml-4 flex flex-shrink-0">
                                    <button
                                        type="button"
                                        className="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        onClick={() => {
                                            setShow(false);
                                        }}
                                    >
                                        <span className="sr-only">Close</span>
                                        <XMarkIcon
                                            className="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </>
    );
};
