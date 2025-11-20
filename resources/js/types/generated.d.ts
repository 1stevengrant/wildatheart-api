declare namespace App.Data {
export type AttendeeData = {
id: number;
event_id: string;
user_id: number | null;
family_name: string;
given_name: string;
email: string;
country: string;
country_code: string;
phone: string;
city: string;
state: string;
marketing_updates: boolean;
contacted_on: string | null;
created_at: string;
updated_at: string;
event_type_name: string | null;
event_name: string | null;
fullname: string;
};
export type EventData = {
id: string;
name: string | null;
longitude: number;
latitude: number;
badge_display: string;
display_longitude: number;
display_latitude: number;
display_name: string | null;
start_date: string | null;
startDate: string | null;
end_date: string | null;
endDate: string | null;
event_type_id: number | null;
event_type_name: string | null;
event_type: App.Data.EventTypeData;
event_type_image: string;
type_group: string | null;
location: string;
city: string | null;
state: string | null;
country: string | null;
host: string;
host_name: string;
host_friendly_name: string | null;
host_id: number | null;
host_photo: string;
sex: App.Enums.EventSexEnum;
url: string;
group_size: number;
frequency: App.Enums.Event.FrequencyEnum | null;
hero_image: string | null;
hero_video: string | null;
is_private: boolean;
gift_requested: boolean;
approved_at: string | null;
host_object: App.Data.HostData;
event_type_data: App.Data.EventTypeData;
card_title: string;
event_type_slug: string;
region: string | null;
district: string | null;
locality: string | null;
neighborhood: string | null;
street_address: string | null;
zip: string | null;
is_featured: boolean | null;
custom_landing_url: string | null;
};
export type EventFormData = {
name: string;
state: string | null;
city: string | null;
zip: string | null;
country: string | null;
latitude: number | null;
longitude: number | null;
region: string | null;
district: string | null;
locality: string | null;
neighborhood: string | null;
street_address: string | null;
poi: string | null;
is_private: boolean | null;
start_date: string | null;
end_date: string | null;
number_of_items_in_material_order: string | null;
frequency: App.Enums.Event.FrequencyEnum | null;
};
export type EventTypeData = {
id: number;
allow_materials_order: boolean;
number_of_items_in_material_order_label: string | null;
group_type: string;
name: string;
filter_name: string | null;
order: number;
free_gift_image: string | null;
zapier_webhook_url: string | null;
card_teaser_text: string | null;
tags: string | null;
is_active: boolean;
sex: App.Enums.EventSexEnum;
slug: string;
description: string | null;
event_guide_pages: Array<any> | null;
video_link: string | null;
registration_welcome_video: string | null;
registration_welcome_text: string | null;
registration_before_your_event_text: string | null;
event_schedule: Array<any> | null;
event_faqs: Array<any> | null;
image: string;
hero_image: string | null;
hero_video: string | null;
photos: Array<any> | null;
summary: string | null;
key_details: Array<any> | null;
action_buttons: Array<any> | null;
new_content: Array<any> | null;
};
export type FeaturedContentData = {
show_in_regions: Array<string>;
featured_content_url: string | null;
featured_content_text: string | null;
featured_content_image: string | null;
};
export type HostData = {
family_name: string | null;
given_name: string | null;
md5email: string | null;
fullname: string | null;
photo: string | null;
};
export type UserData = {
id: number;
given_name: string;
family_name: string;
fullname: string;
email: string;
opted_into_marketing: boolean;
image: string;
profile_step: number;
phone: string;
city: string;
state: string;
postal_code: string;
};
}
declare namespace App.Data.Navigation {
export type DesktopNavItemData = {
id: string;
title: string;
enabled: boolean;
type: string;
url: string | null;
icon: string | null;
dropdownLinks: Array<App.Data.Navigation.DropdownLinkData> | null;
bottomLinkTitle: string | null;
bottomLinkUrl: string | null;
featuredMediaItemByline: string | null;
featuredMediaItemTitle: string | null;
featuredMediaItemUrl: string | null;
featuredMediaItemImage: string | null;
};
export type DesktopNavResponseData = {
items: Array<App.Data.Navigation.DesktopNavItemData>;
};
export type DropdownLinkData = {
id: string;
title: string;
url: string;
icon: string | null;
enabled: boolean;
type: string;
};
export type MobileNavResponseData = {
mainNavBar: Array<App.Data.Navigation.NavItemData>;
drawerItems: Array<App.Data.Navigation.NavItemData>;
};
export type NavItemData = {
id: string;
title: string;
url: string;
icon: string | null;
enabled: boolean;
type: string;
};
}
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
blocks: Array<App.DataTypeObjects.SlideBlock | App.DataTypeObjects.GenericBlock> | null;
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
blocks: Array<App.DataTypeObjects.SlideBlock | App.DataTypeObjects.GenericBlock | App.DataTypeObjects.HeroTitleBlock> | null;
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
export type CardVariant = 'landscape' | 'portrait' | 'square' | 'book' | 'blog-article';
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
tags: Array<any> | null;
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
author_name: string | null;
summary: string;
content: Array<any> | string | null;
featured_image_src: Array<any> | string | null;
api_url: string;
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
uri: string | null;
};
export type ExperienceResponse = {
blocks: Array<any> | null;
height: string | null;
images: Array<any> | null;
position: string | null;
sessions: Array<any> | null;
subtitle: string | null;
hero_logo: string | null;
hero_title: Array<any> | null;
updated_by: number;
button_link: string | null;
button_text: string | null;
description: Array<any> | null;
leader_text: string | null;
hero_variant: string | null;
testimonials: Array<any> | null;
hero_subtitle: Array<any> | null;
experience_logo: Array<any> | null;
find_a_group_link: string | null;
host_a_group_link: string | null;
featured_video_url: string | null;
related_event_type: string | null;
subsplash_series_id: string | null;
background_video_url: string | null;
featured_video_fallback_image: string | null;
background_video_fallback_image: Array<any> | null;
sessionData: Array<any> | null;
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
declare namespace App.DataTypeObjects.API.V1.Content.Events {
export type EventsShowResponse = {
blocks: Array<any> | null;
description: string | null;
buttons: Array<any> | null;
event_logo: Array<any> | null;
event_type: Array<any> | null;
featured_image: Array<any> | null;
featured_video_fallback_image: Array<any> | null;
featured_video_url: string | null;
topics: Array<any> | null;
background_video_url: string | null;
background_video_fallback_image: Array<any> | null;
height: Array<any> | null;
hero_logo: Array<any> | null;
hero_subtitle: Array<any> | null;
hero_title: Array<any> | null;
hero_variant: Array<any> | null;
hide_from_search_engines: boolean;
images: Array<any>;
is_local_event: boolean | null;
leader_text: string | null;
position: Array<any> | null;
related_event_type: Array<any> | null;
seo_description: string | null;
seo_image: Array<any> | null;
seo_title: string | null;
show_in_collections: boolean | null;
site_url: string | null;
sitemap_priority: string | null;
subtitle: string | null;
testimonials: Array<any> | null;
wide: string | null;
width: Array<any> | null;
final_details_blocks: Array<any> | null;
vertical_videos_block: Array<any> | null;
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
declare namespace App.DataTypeObjects.API.V1.Content.Prayers {
export type PrayersShowResponse = {
content: string;
before_content_blocks: Array<any>;
after_content_blocks: Array<any>;
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
declare namespace App.DataTypeObjects.Previews {
export type BlogPreview = {
author_name: string | null;
author_image_src: string | null;
tags: Array<any> | null;
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
id: string | null;
title: string | null;
slug: string | null;
uri: string | null;
url: string | null;
blueprint: App.DataTypeObjects.Blueprint;
subtitle: string | null;
};
}
declare namespace App.Enums {
export type EventSexEnum = 'men' | 'women';
export type ReportFormat = 'csv';
export type ReportName = 'getBAKRHosts' | 'getWAHRHosts' | 'getBAKRExperienceHosts' | 'getWAHExperienceHosts' | 'getFiresHosts' | 'getCAPRHosts' | 'getCAPExperienceHosts' | 'getSurvivalSchoolHosts';
}
declare namespace App.Enums.Event {
export type FrequencyEnum = 'biweekly' | 'weekly' | 'monthly' | 'quarterly' | 'varies';
}
