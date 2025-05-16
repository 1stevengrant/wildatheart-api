export type EventTypeStats = {
    public: number;
    private: number;
    total: number;
};

export type EventsByType = {
    [key: string]: EventTypeStats;
};

export type EventsInTimeFrameByType = {
    [key: string]: number;
};

export type UnpublishedEventsByType = {
    [key: string]: number;
};

export type HostContactsByType = {
    [key: string]: number;
};

export type EmailStats = {
    keep_burning_clicks: number;
    extinguish_clicks: number;
    no_action_emails: number;
};

export type EventsDashboardStats = {
    total_active_events: number;
    events_by_type: EventsByType;
    events_in_time_frame: number;
    events_in_time_frame_by_type: EventsInTimeFrameByType;
    unpublished_events_by_type: UnpublishedEventsByType;
    host_contacts_by_type: HostContactsByType;
    new_events_in_time_frame: number;
    email_stats: EmailStats;
};
