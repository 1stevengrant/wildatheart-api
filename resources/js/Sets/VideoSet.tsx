import Vimeo from "@u-wave/react-vimeo";

export const VideoSet = ({
    video,
    className = "",
}: {
    video?: string;
    className?: string;
}) => {
    if (!video) {
        return null;
    }
    return (
        <Vimeo
            className={className}
            responsive={true}
            video={video}
        />
    );
};
