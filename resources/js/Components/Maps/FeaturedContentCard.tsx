import { useState } from "react";

import { imageGenerator } from "@/helpers/user";

interface FeaturedContentCardProps {
    item: App.Data.FeaturedContentItemData;
}

export const FeaturedContentCard = ({ item }: FeaturedContentCardProps) => {
    const [imageError, setImageError] = useState(false);

    // Don't render if missing required fields
    if (!item.item_text || !item.item_url) {
        return null;
    }

    return (
        <div className="bg-gradient-to-r from-p400 to-black rounded-lg shadow-lg p-4 mb-4">
            <a
                href={item.item_url}
                target="_blank"
                rel="noopener noreferrer"
                className="flex gap-4 items-center group"
            >
                {item.item_image && !imageError && (
                    <div className="flex-shrink-0">
                        <img
                            src={imageGenerator(
                                item.item_image,
                                {},
                                "https://b-cdn.wildatheart.org"
                            )}
                            alt="Featured content"
                            className="w-16 h-16 object-cover rounded"
                            onError={() => setImageError(true)}
                        />
                    </div>
                )}
                <div className="flex-1 min-w-0">
                    <p className="text-sm text-white/90 group-hover:text-white transition-colors">
                        {item.item_text}
                    </p>
                    <span className="text-xs text-p100 group-hover:text-p75 mt-1 inline-block">
                        Learn more →
                    </span>
                </div>
            </a>
        </div>
    );
};
