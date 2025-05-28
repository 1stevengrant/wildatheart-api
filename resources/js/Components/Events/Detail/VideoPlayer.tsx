import { useRef } from "react";

export const VideoPlayer = ({
    src,
    className = "",
}: {
    src: string;
    className?: string;
}) => {
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
