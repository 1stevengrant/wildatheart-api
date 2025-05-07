export const isSelectedEvent = (
    event: App.Data.EventData,
    currentEvent: App.Data.EventData | undefined | null,
    borderColor?: string
) => {
    return event.id === currentEvent?.id
        ? "border-p200"
        : `${borderColor ? `${borderColor}` : `border-transparent`}`;
};
