import React, { useRef } from "react";

type VideoPlayerProps = {
    src: string;
    className?: string;
};

export const VideoPlayer = ({ src, className = "" }: VideoPlayerProps) => {
    const videoRef = useRef<HTMLVideoElement>(null);

    return (
        <div className={`${className} rounded-2xl`}>
            <video
                ref={videoRef}
                muted
                className={`w-full rounded-2xl`}
                controls
            >
                <source src={src} />
                Your browser does not support the video tag.
            </video>
        </div>
    );
};
