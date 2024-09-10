export const eventTypeHasSchedule = (eventType: any) => {
    return eventType.event_schedule && eventType.event_schedule.length > 0;
};
