export interface EventGuidePageContentInterface {
    type: "body_set" | "video_set" | "image_set" | "accordion_set";
    enabled: boolean;
    body?: string;
    video?: any;
    image?: any;
    accordion?: any;
}
