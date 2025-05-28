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
