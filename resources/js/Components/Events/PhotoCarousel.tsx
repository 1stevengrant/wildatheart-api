import React, {FC} from "react";
import { imageGenerator } from "@/helpers/user";

type PhotoCarouselProps = {
    className?: string;
    images: string[];
};
const PhotoCarousel: FC<PhotoCarouselProps> = ({
                                                   className,
                                                   images
                                               }) => {
    return (
        <div className={`${className} mt-4 prose prose-p:text-white prose-a:text-orange max-w-none prose-hr:bg-black prose-h2:text-white prose-h3:text-white`}>
            <h2>Photos of Events Like This</h2>
            <ul className="grid snap-x snap-mandatory gap-4 overflow-x-auto card-grid md:gap-6 m-0 p-0">
                {images?.map((image, index) => (
                    <li key={index} className="snap-center list-none p-0 m-0">
                        <img src={imageGenerator(image, {height: 200})} className="rounded-lg" alt=""/>
                    </li>
                ))}


            </ul>
        </div>
    )
}

export default PhotoCarousel;
