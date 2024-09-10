export const eventTypeHasFaqs = (eventType: any) => {
    return eventType.event_faqs && eventType.event_faqs.length > 0;
};
