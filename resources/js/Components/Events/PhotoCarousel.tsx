import React from "react";

const PhotoCarousel = () => {
    return (
        <div className="mt-12 hidden md:block">
            <h2 className="text-2xl">Photos of Fires Like This</h2>

            <div className="mt-12 flex items-center space-x-4">
                <img src="/img/fires/Image1.webp"
                     className="rounded-lg object-fill"
                     alt=""/>
                <div className="grid grid-cols-2 gap-4">
                    <img src="/img/fires/Image2.webp"
                         className="rounded-lg"
                         alt=""/>
                    <img src="/img/fires/Image3.webp"
                         className="rounded-lg"
                         alt=""/>
                    <img src="/img/fires/Image.webp"
                         className="rounded-lg"
                         alt=""/>
                    <img src="/img/fires/Image4.webp"
                         className="rounded-lg"
                         alt=""/>
                </div>
            </div>
        </div>
    )
}

export default PhotoCarousel;
