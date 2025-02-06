declare namespace App.DataTypeObjects {
    export type Attributes = {
        title: string;
        number: number;
        season: number;
        status: string;
        published_at: string;
        duration: number | null;
        explicit: boolean;
        keywords: string;
        alternate_url: string;
        media_url: string;
        image_url: string | null;
        video_url: string;
        author: string;
        summary: string | null;
        description: string;
        slug: string;
        created_at: string;
        updated_at: string;
        formatted_published_at: string;
        duration_in_mmss: string;
        share_url: string;
        transcript_url: string | null;
        transcripts: Array<any>;
        formatted_summary: string;
        formatted_description: string;
        embed_html: string;
        embed_html_dark: string;
        audio_processing: boolean;
        type: string;
        email_notifications: string | null;
    };
    export type Blueprint = {
        title: string;
        handle: string;
    };
    export type ButtonProps = {
        text: string | null;
        href: string | null;
        variant: string | null;
        leftIcon: any | null;
        rightIcon: any | null;
        openInModal: boolean | null;
        openInNewTab: boolean | null;
    };
    export type CardVariant = {
        name: string;
        value: string;
    };
    export type ContentPreview = {
        id: any;
        title: string | null;
        description: string | null;
        uri: string | null;
        featured_image_src: string | null;
        published_at: string | null;
        collection: any | null;
        blueprint: any | null;
        related_content: Array<any> | null;
    };
    export type Entry = {
        test_prop: string;
    };
    export type File = {
        url: string;
        filename: string;
        basename: string;
        extension: string;
        size: string;
        mime_type: string;
        size_bytes: number;
    };
    export type GenericBlock = {
        id: string;
        title: string;
        type: string;
    };
    export type HeroTitleBlock = {
        type: string;
        hero_title: Array<App.DataTypeObjects.TextLineBlock>;
    };
    export type ImageData = {
        square: string | null;
        wide: string | null;
        banner: string | null;
        vertical: string | null;
    };
    export type MediaItemData = {
        id: string;
        blocks: Array<
            App.DataTypeObjects.SlideBlock | App.DataTypeObjects.GenericBlock
        > | null;
        images: App.DataTypeObjects.ImageData | null;
        locale: string;
        parent: Array<App.DataTypeObjects.ParentData> | null;
        private: boolean;
        published: boolean;
        seo_description: string | null;
        seo_image: Array<any> | null;
        seo_title: string | null;
        slug: string;
        status: string;
        title: string;
        uri: string;
        url: string;
        blueprint: App.DataTypeObjects.Blueprint;
    };
    export type MediaItemsSlide = {
        type: any;
        slides: { [key: number]: App.DataTypeObjects.SlideBlock };
    };
    export type PageData = {
        id: string;
        blocks: Array<
            | App.DataTypeObjects.SlideBlock
            | App.DataTypeObjects.GenericBlock
            | App.DataTypeObjects.HeroTitleBlock
        > | null;
        images: App.DataTypeObjects.ImageData;
        locale: string;
        parent: Array<App.DataTypeObjects.ParentData> | null;
        private: boolean;
        published: boolean;
        seo_description: string | null;
        seo_image: Array<any> | null;
        seo_title: string | null;
        slug: string;
        status: string;
        title: string;
        uri: string | null;
        url: string | null;
        blueprint: App.DataTypeObjects.Blueprint;
    };
    export type ParentData = {
        id: string;
        title: string;
        url: string;
        permalink: string;
        api_url: string;
    };
    export type PlayableFile = {
        playtime: string;
        duration_seconds: number;
        url: string;
        filename: string;
        basename: string;
        extension: string;
        size: string;
        mime_type: string;
        size_bytes: number;
    };
    export type Relationships = {
        show: App.DataTypeObjects.ShowData;
    };
    export type Show = {
        id: string;
        type: string;
    };
    export type ShowData = {
        data: App.DataTypeObjects.Show;
    };
    export type SlideBlock = {
        id: any;
        type: any;
        title: string | null;
        subtitle: string | null;
        featured_image_src: string | null;
        tag: string | null;
        leader: string | null;
        description: string | null;
        trim_description_bool: boolean | null;
        details: string | null;
        variant: App.DataTypeObjects.CardVariant | null;
        left_cta: App.DataTypeObjects.ButtonProps | null;
        right_cta: App.DataTypeObjects.ButtonProps | null;
        href: string | null;
        is_coming_soon: boolean | null;
        date: string | null;
    };
    export type StatamicValue = {
        value: string;
        label: string;
        key: string;
    };
    export type TextLineBlock = {
        type: string;
        text: string;
        text_style: App.DataTypeObjects.StatamicValue;
        id: string;
    };
    export type TransistorPodcastEpisode = {
        id: string;
        type: string;
        attributes: App.DataTypeObjects.Attributes;
        relationships: App.DataTypeObjects.Relationships;
    };
}
declare namespace App.DataTypeObjects.API.V1 {
    export type EntryResponse = {
        id: string;
        title: string;
        private: boolean;
        published: boolean;
        status: string;
        slug: string;
        uri: string | null;
        url: string | null;
        blueprint: App.DataTypeObjects.Blueprint;
        locale: string;
    };
    export type ErrorResponse = {
        error: string;
    };
    export type RedirectResponse = {
        redirect: string;
        code: string;
    };
}
declare namespace App.DataTypeObjects.API.V1.Content {
    export type AlertResponse = {
        id: string;
        title: string;
        private: boolean;
        published: boolean;
        status: string;
        blueprint: App.DataTypeObjects.Blueprint;
        locale: string;
        content: string;
        start_date: string;
        end_date: string;
        link_text: string;
        link_url: string;
    };
    export type BlogResponse = {
        published_date: string;
        content: string;
        summary: string;
        author_name: string | null;
        id: string;
        title: string;
        private: boolean;
        published: boolean;
        status: string;
        slug: string;
        uri: string | null;
        url: string | null;
        blueprint: App.DataTypeObjects.Blueprint;
        locale: string;
    };
    export type DailyReadingResponse = {
        transistor_episode: App.DataTypeObjects.TransistorPodcastEpisode;
        current_episode_slug: string | null;
    };
}
declare namespace App.DataTypeObjects.API.V1.Content.Prayers {
    export type PrayersShowResponse = {
        content: string;
        summary: string;
        drupal_url: string;
        mp3: App.DataTypeObjects.PlayableFile | null;
        pdf: App.DataTypeObjects.File | null;
        id: string;
        title: string;
        private: boolean;
        published: boolean;
        status: string;
        slug: string;
        uri: string | null;
        url: string | null;
        blueprint: App.DataTypeObjects.Blueprint;
        locale: string;
    };
}
declare namespace App.DataTypeObjects.Statamic.Blueprints {
    export type CustomSeries = {
        id: string;
        blocks: Array<any | any> | null;
        images: App.DataTypeObjects.ImageData | null;
        locale: string;
        parent: Array<App.DataTypeObjects.ParentData> | null;
        private: boolean;
        published: boolean;
        seo_description: string | null;
        seo_image: Array<any> | null;
        seo_title: string | null;
        slug: string;
        status: string;
        title: string;
        uri: string;
        url: string;
        blueprint: App.DataTypeObjects.Blueprint;
    };
    export type SubsplashMediaSeries = {
        id: string;
        title: string;
        slug: string;
        uri: string;
        url: string;
        blueprint: App.DataTypeObjects.Blueprint;
        subtitle: string;
        subsplash_data: Array<any>;
    };
}
