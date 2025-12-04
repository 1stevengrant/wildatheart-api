export const isSelectedEvent = (
    event: App.Data.EventMapData,
    currentEvent: App.Data.EventMapData | undefined | null,
    borderColor?: string
) => {
    return event.id === currentEvent?.id
        ? "border-p200"
        : `${borderColor ? `${borderColor}` : `border-transparent`}`;
};
