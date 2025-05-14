import Markdown from "react-markdown";

import { VideoPlayer } from "@/Components/Events/Detail/VideoPlayer";
import { proseClasses } from "@/helpers/proseClasses";

export const ContentBlocksComponent = ({
    contentBlocks,
    className = "",
}: {
    contentBlocks: any[];
    className?: string;
}) => {
    return (
        <>
            {contentBlocks && (
                <div className={`${className} ${proseClasses}`}>
                    {contentBlocks.map((block: any, index: number) => {
                        switch (block.type) {
                            case "text_block":
                                return (
                                    <Markdown
                                        className={proseClasses}
                                        key={index}
                                    >
                                        {block.text}
                                    </Markdown>
                                );
                            case "video_block":
                                return (
                                    <VideoPlayer src={block.fields.video_url} />
                                );
                            default:
                                return null;
                        }
                    })}
                </div>
            )}
        </>
    );
};
