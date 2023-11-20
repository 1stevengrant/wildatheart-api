import React from "react";

const PhotoCarouselMobile = () => {
    return (
        <div className="md:hidden">
            <h2 className="mb-6 text-lg">Photos of Fires Like This</h2>
            <ul className="card-grid grid snap-x snap-mandatory gap-4 overflow-x-auto md:gap-6">
                <div className="snap-center">
                    <img src="/img/fires/Image2.webp" className="rounded-lg" alt=""/>
                </div>
                <div className="snap-center">
                    <img src="/img/fires/Image3.webp" className="rounded-lg" alt=""/>
                </div>
                <div className="snap-center">
                    <img src="/img/fires/Image.webp" className="rounded-lg" alt=""/>
                </div>
                <div className="snap-center">
                    <img src="/img/fires/Image4.webp" className="rounded-lg" alt=""/>
                </div>
            </ul>
        </div>
    )
}

export default PhotoCarouselMobile;
