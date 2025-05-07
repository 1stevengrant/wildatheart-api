import { Accordion } from "@/Components/Accordion";
import { ImageSet } from "@/Sets/ImageSet";
import { TextSet } from "@/Sets/TextSet";
import { VideoSet } from "@/Sets/VideoSet";
import { ContentBlock } from "@/types/EventType";

export const PageContent = ({ content }: { content: Array<ContentBlock> }) => (
    <>
        {content
            .filter((block) => block.enabled)
            .map((block, index) => {
                switch (block.type) {
                    case "body_set":
                        return <TextSet key={index} text={block.body} />;
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
