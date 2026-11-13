import { useState } from "react";

import { XMarkIcon } from "@heroicons/react/24/solid";

import { imageGenerator } from "@/helpers/user";

interface FeaturedContentCardProps {
    featuredContent: App.Data.FeaturedContentData | null;
    position?: "map-top" | "map-bottom" | "sidebar";
}

export const FeaturedContentCard = ({
    featuredContent,
    position = "map-bottom",
}: FeaturedContentCardProps) => {
    const [dismissedUrl, setDismissedUrl] = useState<string | null>(null);
    const [imageError, setImageError] = useState(false);

    if (
        !featuredContent ||
        !featuredContent.featured_content_text ||
        !featuredContent.featured_content_url
    ) {
        return null;
    }

    const url = featuredContent.featured_content_url;
    const dismissedKey = `featured_content_dismissed_${url}`;

    if (dismissedUrl === url || sessionStorage.getItem(dismissedKey)) {
        return null;
    }

    const handleDismiss = () => {
        sessionStorage.setItem(dismissedKey, "true");
        setDismissedUrl(url);
    };

    // Position classes based on where it's rendered
    const positionClasses = {
        "map-top": "absolute top-4 left-4 right-4 md:hidden z-10", // Mobile only, at top
        "map-bottom": "absolute bottom-4 left-4 right-4 md:hidden z-10", // Mobile only, at bottom
        sidebar: "relative mb-4", // In sidebar for desktop
    };

    return (
        <div
            className={`${positionClasses[position]} bg-gradient-to-r from-p400 to-black rounded-lg shadow-lg p-4 max-w-sm md:max-w-none`}
        >
            <button
                onClick={handleDismiss}
                className="absolute top-2 right-2 text-white/60 hover:text-white transition-colors"
                aria-label="Dismiss"
            >
                <XMarkIcon className="h-5 w-5" />
            </button>

            <a
                href={featuredContent.featured_content_url}
                target="_blank"
                rel="noopener noreferrer"
                className="flex gap-4 items-center group"
            >
                {featuredContent.featured_content_image && !imageError && (
                    <div className="flex-shrink-0">
                        <img
                            src={imageGenerator(
                                featuredContent.featured_content_image,
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
                    <p className="text-sm text-white/90 group-hover:text-white transition-colors pr-6">
                        {featuredContent.featured_content_text}
                    </p>
                    <span className="text-xs text-p100 group-hover:text-p75 mt-1 inline-block">
                        Learn more →
                    </span>
                </div>
            </a>
        </div>
    );
};
