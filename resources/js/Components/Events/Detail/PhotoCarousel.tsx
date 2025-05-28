import { Image } from "@/Components/Image";

export const PhotoCarousel = ({
    className,
    images,
}: {
    className?: string;
    images: string[];
}) => {
    return (
        <div
            className={`${className} mt-4 prose prose-p:text-white prose-a:text-p200 max-w-none prose-hr:bg-black prose-h2:text-white prose-h3:text-white`}
        >
            <h2>Photos of Events Like This</h2>
            <ul className="m-0 grid snap-x snap-mandatory gap-4 overflow-x-auto p-0 card-grid md:gap-6">
                {images?.map((image, index) => (
                    <li key={index} className="m-0 snap-center list-none p-0">
                        <Image
                            src={image}
                            imageDimensions={{ height: 200 }}
                            className={`rounded-lg`}
                        />
                    </li>
                ))}
            </ul>
        </div>
    );
};
