export type EventType = {
    id: number;
    name: string;
    slug: string;
    description: string;
    image: string;
    tags: string;
    sex: string;
    card_teaser_text?: string;
    action_buttons?: any;
    created_at: string;
    event_faqs: any;
    event_schedule: any;
    hero_image?: string;
    hero_video?: string;
    is_active: boolean;
    key_details?: any;
    new_content?: any;
    photos?: any;
    registration_before_your_event_text?: string;
    registration_welcome_text?: string;
    registration_welcome_video?: string;
    summary?: string;
    video_link?: string;
    updated_at: string;
    event_guide_pages?: Array<GuidePage>;
};

export interface ContentBlock {
    type: "body_set" | "video_set" | "image_set" | "accordion_set";
    enabled: boolean;
    body?: string;
    video?: string;
    image?: string;
    accordion?: string;
}

export interface GuidePage {
    page_title: string;
    page_content: Array<ContentBlock>;
}
