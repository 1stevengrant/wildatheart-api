import React from "react";

import { Accordion } from "@/Components/Accordion";
import { ImageSet } from "@/Sets/ImageSet";
import { TextSet } from "@/Sets/TextSet";
import { VideoSet } from "@/Sets/VideoSet";
import { ContentBlock } from "@/types/EventType";

export const PageContent: React.FC<{ content: Array<ContentBlock> }> = ({
    content,
}) => (
    <>
        {content
            .filter((block) => block.enabled)
            .map((block, index) => {
                switch (block.type) {
                    case "body_set":
                        return (
                            <TextSet
                                key={index}
                                text={block.body}
                                className="prose-p:text-white prose-strong:text-white prose-ol:text-white prose-ul:text-white prose-li:text-white prose-li:marker:text-white prose-headings:text-white"
                            />
                        );
                    case "video_set":
                        return <VideoSet key={index} video={block.video} />;
                    case "image_set":
                        return (
                            <ImageSet
                                key={index}
                                image={block.image}
                                params={{ height: 400 }}
                            />
                        );
                    case "accordion_set":
                        return (
                            <Accordion
                                key={index}
                                accordion={
                                    Array.isArray(block.accordion)
                                        ? block.accordion
                                        : undefined
                                }
                            />
                        );
                    default:
                        return null;
                }
            })}
    </>
);
