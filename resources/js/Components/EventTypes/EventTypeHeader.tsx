import { Image } from "@/Components/Image";

export const EventHeader = ({
    title,
    image,
}: {
    title: string;
    image?: string;
}) => (
    <div className="flex items-center space-x-4 mb-4">
        {image && (
            <Image
                src={image}
                imageDimensions={{ width: 150, height: 150 }}
                className={`rounded-xl`}
            />
        )}
        <h1 className="text-[24px] font-bold leading-tight">{title}</h1>
    </div>
);
