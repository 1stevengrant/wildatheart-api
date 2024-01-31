import React, {useRef} from 'react';

type VideoPlayerProps = {
    src: string;
    className?: string;
};

const VideoPlayer: React.FC<VideoPlayerProps> = ({
                                                     src,
                                                     className
                                                 }) => {
    const videoRef = useRef<HTMLVideoElement>(null);

    return (
        <div className={`${className} rounded-2xl`}>
            <video ref={videoRef}
                   autoPlay
                   muted
                   className={`w-full rounded-2xl`}
                   controls>
                <source src={src}/>
                Your browser does not support the video tag.
            </video>
        </div>
    );
};

export default VideoPlayer;
