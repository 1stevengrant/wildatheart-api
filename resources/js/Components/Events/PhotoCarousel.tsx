import React, { FC } from "react";

type PhotoCarouselProps = {
    className?: string;
    images: string[];
};
const PhotoCarousel: FC<PhotoCarouselProps> = ({ className, images }) => {
    return (
        <div className={`${className}`}>
            <h2 className="mb-6 text-lg">Photos of Events Like This</h2>
            <ul className="grid snap-x snap-mandatory gap-4 overflow-x-auto card-grid md:gap-6">
                {images?.map((image, index) => (
                    <li key={index} className="snap-center">
                        <img src={image} className="rounded-lg" alt="" />
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default PhotoCarousel;
