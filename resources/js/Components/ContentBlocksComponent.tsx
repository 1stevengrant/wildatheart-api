import VideoPlayer from "@/Components/Events/Detail/VideoPlayer";

type ContentBlocksComponentProps = {
    contentBlocks: any[];
    className?: string;
};
export const ContentBlocksComponent = ({ contentBlocks, className = "" }: ContentBlocksComponentProps) => {
    return (
        <>
            {contentBlocks && (
                <div
                    className={`prose prose-lg prose-p:text-white prose-h1:text-white prose-h2:text-white prose-h3:text-white prose-h4:text-white ${className}`}>
                    {
                        contentBlocks.map((block: any, index: number) => {
                            switch (block.type) {
                                case "text-block":
                                    return <p key={index}>{block.fields.text}</p>;
                                case "video-block":
                                    return <VideoPlayer src={block.fields.video_url} />;
                                default:
                                    return null;
                            }
                        })
                    }
                </div>
            )}
        </>
    );
};
