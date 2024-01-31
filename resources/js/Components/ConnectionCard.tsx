import React from "react";
import {Connection} from "@/types/Connection";
import {CheckCircleIcon, ExclamationCircleIcon, PhoneIcon} from "@heroicons/react/24/outline";
import {defaultIconSize} from "@/helpers/defaultIconSize";
import {useForm} from "@inertiajs/react";

interface ConnectionCardProps {
    connection: Connection
}

const ConnectionCard = ({connection}: ConnectionCardProps) => {
    const form = useForm();

    return (
        <li
            className="col-span-1 rounded-lg border shadow-lg divide-y divide-grey-darker border-grey-darker">
            <div className="flex w-full items-center justify-between p-6 space-x-6">
                <div className="flex-1 truncate">
                    <div className="flex items-center space-x-3">
                        <h3 className="truncate text-sm font-medium text-grey-200">
                            {connection.family_name}, {connection.given_name} ({connection.city})
                        </h3>
                    </div>
                    <p className="mt-1 truncate text-sm text-grey-200">
                        {connection.created_at}
                    </p>
                </div>
            </div>
            <div>
                <div
                    className="-mt-px flex flex-col divide-x divide-y divide-grey-darker md:divide-y-0 md:flex-row"
                >
                    <div className="flex flex-1 md:w-0">
                        <a
                            href={`mailto:${connection.email}`}
                            className="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent px-3 py-4 text-sm font-semibold transition-all duration-150 ease-in-out text-grey-200 hover:text-orange"
                        >

                            <span className="overflow-hidden text-ellipsis">
                        {connection.email}
                    </span>
                        </a>
                    </div>

                    {connection.phone && (
                        <div className="-ml-px flex flex-1 md:w-0">
                            <a
                                href={`tel:${connection.phone}`}
                                className="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold transition-all duration-150 ease-in-out text-grey-200 hover:text-orange"
                            >
                                <PhoneIcon className={defaultIconSize}/>
                                {connection.phone}
                            </a>
                        </div>
                    )}


                    <div className="-ml-px flex flex-1 md:w-0">
                        <button
                            onClick={async () => {
                                form.put(route('toggle.contacted', connection.id), {
                                    preserveScroll: true,
                                });
                            }}
                            className="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold transition-all duration-150 ease-in-out text-grey-200 hover:text-orange"
                        >
                            {
                                connection.contacted_on ? (
                                    <>
                                        <CheckCircleIcon className={defaultIconSize}/>
                                        <span>Contacted</span>
                                    </>
                                ) : (
                                    <>
                                        <ExclamationCircleIcon className={defaultIconSize}/>
                                        <span>Not Contacted</span>
                                    </>
                                )
                            }
                        </button>
                    </div>
                </div>
            </div>
        </li>
    )
}

export default ConnectionCard;
