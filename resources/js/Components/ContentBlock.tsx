import { Accordion } from "@/Components/Accordion";
import { ImageSet } from "@/Sets/ImageSet";
import { TextSet } from "@/Sets/TextSet";
import { VideoSet } from "@/Sets/VideoSet";
import { ContentBlockPropsInterface } from "@/types/ContentBlockPropsInterface";

export const ContentBlock = ({ block }: ContentBlockPropsInterface) => {
    const CONTENT_COMPONENTS = {
        body_set: () => (
            <TextSet
                text={block.body ?? ""}
                className="prose-p:text-white prose-strong:text-white prose-ol:text-white prose-ul:text-white prose-li:text-white prose-li:marker:text-white prose-h1:text-white prose-h2:text-white prose-h3:text-white prose-h4:text-white prose-h5:text-white prose-h6:text-white"
            />
        ),
        video_set: () => <VideoSet video={block.video} />,
        image_set: () => (
            <ImageSet image={block.image} params={{ height: 400 }} />
        ),
        accordion_set: () => <Accordion accordion={block.accordion} />,
    };

    const Component = CONTENT_COMPONENTS[block.type];
    return Component ? <Component /> : null;
};
