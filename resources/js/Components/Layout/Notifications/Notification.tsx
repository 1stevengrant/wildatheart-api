import { Fragment, useState, useEffect } from "react";

import { Transition } from "@headlessui/react";
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from "@heroicons/react/24/outline";
import { useSetRecoilState } from "recoil";

import { reactNotificationState } from "@/store";

export const Notification = ({
    message,
    type = "success",
    duration = 2500,
}: {
    message: string;
    type?: "success" | "error" | "info";
    duration?: number;
}) => {
    const [show, setShow] = useState(true);
    const setReactNotifications = useSetRecoilState(reactNotificationState);

    useEffect(() => {
        const timer = setTimeout(() => {
            setShow(false);
            setReactNotifications([]);
        }, duration);
        return () => clearTimeout(timer);
    }, [duration, setReactNotifications]);

    const icon = {
        success: (
            <CheckCircleIcon className="h-6 w-6 text-g250" aria-hidden="true" />
        ),
        error: (
            <ExclamationCircleIcon
                className="h-6 w-6 text-r200"
                aria-hidden="true"
            />
        ),
        info: (
            <InformationCircleIcon
                className="h-6 w-6 text-n400"
                aria-hidden="true"
            />
        ),
    }[type];
    const backgroundColor = type === "error" ? "bg-r50" : "bg-white";

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
                    <div
                        className={`pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg ${backgroundColor} shadow-lg ring-1 ring-black ring-opacity-5`}
                    >
                        <div className="p-4">
                            <div className="flex items-start">
                                <div className="flex-shrink-0">{icon}</div>
                                <div className="ml-3 w-0 flex-1 pt-0.5">
                                    <p
                                        className={`text-sm font-medium ${type === "success" ? "text-black" : "text-r200"}`}
                                    >
                                        {message}
                                    </p>
                                </div>
                                <div className="ml-4 flex flex-shrink-0">
                                    <button
                                        type="button"
                                        className="inline-flex rounded-md bg-white text-n80 hover:text-n200 focus:outline-none focus:ring-2 focus:ring-p200 focus:ring-offset-2"
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
