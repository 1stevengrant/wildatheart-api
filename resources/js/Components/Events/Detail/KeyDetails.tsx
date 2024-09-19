import React from "react";

import { HeadingOne } from "@/Components/Type/HeadingOne";
import { Event } from "@/types/Event";

export const KeyDetails = ({ event }: { event: Event }) => {
    if (
        event.eventTypeData.key_details &&
        event.eventTypeData.key_details.length > 0
    ) {
        return (
            <div>
                <HeadingOne className={`mt-[48px]`}>Key Details</HeadingOne>
                <ul className={`my-8`}>
                    {event.eventTypeData.key_details.map(
                        (item: any, index: number) => (
                            <li className="mb-4 flex items-start" key={index}>
                                <div
                                    dangerouslySetInnerHTML={{
                                        __html: item.fields.icon,
                                    }}
                                    className={`w-6 h-6`}
                                />
                                <div className="ml-[24px] leading-[24px]">
                                    <p className="font-medium">
                                        {item.fields.headline}
                                    </p>
                                    <p className="text-sm text-[#B8B8B8]">
                                        {item.fields.description}
                                    </p>
                                </div>
                            </li>
                        )
                    )}
                </ul>
            </div>
        );
    }
};
