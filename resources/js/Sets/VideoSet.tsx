import Vimeo from "@u-wave/react-vimeo";

import { getVimeoId } from "@/helpers/getVimeoId";

export const VideoSet = ({
    video,
    className = "",
}: {
    video: string;
    className?: string;
}) => {
    if (!video) {
        return null;
    }
    return (
        <Vimeo
            className={className}
            responsive={true}
            video={getVimeoId(video)}
        />
    );
};
