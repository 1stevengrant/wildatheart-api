import { lazy, Suspense } from "react";

import Markdown from "react-markdown";

const ReactPlayer = lazy(() => import("react-player"));

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
                <div
                    className={`prose prose-lg prose-p:text-white prose-h1:text-white prose-h2:text-white prose-h3:text-white prose-h4:text-white prose-a:text-p200 [&_a:hover]:no-underline  ${className}`}
                >
                    {contentBlocks.map((block: any, index: number) => {
                        switch (block.type) {
                            case "text_block":
                                return (
                                    <Markdown key={index}>
                                        {block.text}
                                    </Markdown>
                                );
                            case "video_block":
                                return (
                                    <Suspense key={index} fallback={null}>
                                        <ReactPlayer src={block.video} />
                                    </Suspense>
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
