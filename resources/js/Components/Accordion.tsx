import React from "react";

import { Disclosure } from "@headlessui/react";
import { ChevronRightIcon } from "@heroicons/react/20/solid";

import { ImageSet } from "@/Sets/ImageSet";
import { TextSet } from "@/Sets/TextSet";
import { VideoSet } from "@/Sets/VideoSet";

export const Accordion = ({
    accordion,
}: {
    accordion: any[];
    className?: string;
}) => {
    return (
        <>
            {accordion &&
                accordion.map((item: any, index: number) => {
                    return (
                        <Disclosure key={index}>
                            {({ open }) => (
                                /* Use the `open` state to conditionally change the direction of an icon. */
                                <>
                                    <Disclosure.Button className="flex w-full items-center justify-between border-t px-2 py-2 text-white border-n700 focus:outline-none">
                                        <div
                                            className={`flex items-center font-semibold`}
                                        >
                                            <span
                                                className={`text-left leading-tight`}
                                            >
                                                {item.accordion_box_title}
                                            </span>
                                        </div>
                                        <ChevronRightIcon
                                            className={`w-6 h-6 text-white flex-shrink-0 ${open ? `rotate-90 transform` : ``} `}
                                        />
                                    </Disclosure.Button>
                                    <Disclosure.Panel className="border-t py-4 border-t-n700">
                                        {item.accordion_box_content &&
                                            item.accordion_box_content
                                                .filter(
                                                    (block: any) =>
                                                        block.enabled === true
                                                )
                                                .map(
                                                    (
                                                        block: any,
                                                        index: number
                                                    ) => {
                                                        switch (block.type) {
                                                            case "text_set":
                                                                return (
                                                                    <TextSet
                                                                        key={
                                                                            index
                                                                        }
                                                                        text={
                                                                            block.body
                                                                        }
                                                                    />
                                                                );
                                                            case "video_set":
                                                                return (
                                                                    <VideoSet
                                                                        key={
                                                                            index
                                                                        }
                                                                        video={
                                                                            block.video
                                                                        }
                                                                    />
                                                                );

                                                            case "image_set":
                                                                return (
                                                                    <ImageSet
                                                                        key={
                                                                            index
                                                                        }
                                                                        image={
                                                                            block.image
                                                                        }
                                                                        className={`rounded-xl`}
                                                                        params={{
                                                                            height: 400,
                                                                        }}
                                                                    />
                                                                );
                                                            default:
                                                                return null;
                                                        }
                                                    }
                                                )}
                                    </Disclosure.Panel>
                                </>
                            )}
                        </Disclosure>
                    );
                })}
        </>
    );
};
