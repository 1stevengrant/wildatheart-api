interface FeaturedBadgeProps {
    event: App.Data.EventData;
    className?: string;
}

export const FeaturedBadge = ({
    event,
    className = "",
}: FeaturedBadgeProps) => {
    if (!event.is_featured) {
        return null;
    }

    return (
        <div
            className={`bg-p200 text-center text-white text-xs px-3 py-0.5 rounded-full ${className}`}
        >
            Featured
        </div>
    );
};
