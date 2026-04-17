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
managers: { [key: number]: App.Data.EventManagerData };
managers_count: number;
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
export type EventManagerData = {
id: string;
fullname: string;
email: string;
image: string;
is_pending: boolean;
invitation_id: string | null;
is_owner: boolean;
};
export type EventManagerInvitationWithEventData = {
id: string;
event: App.Data.EventData;
invited_by_name: string;
};
export type EventMapData = {
id: string;
name: string | null;
display_longitude: number;
display_latitude: number;
type_group: string | null;
url: string;
event_type_image: string;
location: string;
host_photo: string;
display_name: string | null;
host_name: string;
managers_count: number;
card_title: string;
badge_display: string;
};
export type EventOwnershipTransferData = {
id: string;
email: string;
transferred_by_name: string;
is_pending: boolean;
};
export type EventOwnershipTransferWithEventData = {
id: string;
event: App.Data.EventData;
transferred_by_name: string;
};
export type EventTypeData = {
id: number;
allow_materials_order: boolean;
number_of_items_in_material_order_label: string | null;
group_type: string;
type: App.Enums.EventTypeEnum | null;
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
summary_link: string | null;
key_details: Array<any> | null;
action_buttons: Array<any> | null;
new_content: Array<any> | null;
welcome_nav_text_override: string | null;
before_event_nav_text_override: string | null;
schedule_nav_text_override: string | null;
faq_nav_text_override: string | null;
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
export type InternalEventSearchData = {
id: string;
name: string | null;
host_name: string | null;
host_email: string | null;
host_phone: string | null;
city: string | null;
state: string | null;
event_type: string | null;
frequency: string | null;
attendee_count: number | null;
start_date: string | null;
distance: number | null;
status: string;
host_street: string | null;
host_address_city: string | null;
host_address_state: string | null;
host_zip: string | null;
host_country: string | null;
host_wegive_id: string | null;
created_at: string | null;
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
declare namespace App.Data.V1 {
export type AttendeeData = {
id: number;
event_id: string;
given_name: string;
family_name: string;
email: string;
phone: string;
city: string;
state: string;
country: string;
country_code: string;
marketing_updates: boolean;
created_at: string;
};
export type EventCollectionData = {
id: string;
location: string;
dates: string | null;
sex: string;
eventType: string;
eventTypeImage: string;
eventTypeCardDescription: string;
eventGroupType: string;
hostName: string;
hostAvatar: string | null;
longitude: number | null;
latitude: number | null;
newCardHeading: string | null;
distance: number | null;
};
export type EventData = {
id: string;
location: string;
latitude: string;
longitude: string;
dates: string | null;
sex: string;
eventType: string;
eventGroupType: string;
eventTypeImage: string;
eventTypeDescription: string;
eventTypePhotos: Array<any>;
scheduledFor: string | null;
hostName: string;
hostAvatar: string | null;
landingPage: Array<any> | null;
connect_url: string;
};
export type EventTypeData = {
id: number;
name: string;
camel_case_name: string;
sex: string;
image: string;
group_type: string;
type: string | null;
listings_count: number;
countries_count: number;
summary: string | null;
summary_link: string | null;
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
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
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
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
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
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
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
featured_video_fallback_image: Array<any> | null;
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
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
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
icons: Array<any> | null;
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
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
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
export type EventTypeEnum = 'gathering' | 'retreat' | 'course';
export type ReportFormat = 'csv';
export type ReportName = 'getBAKRHosts' | 'getWAHRHosts' | 'getBAKRExperienceHosts' | 'getWAHExperienceHosts' | 'getFiresHosts' | 'getCAPRHosts' | 'getCAPExperienceHosts' | 'getSurvivalSchoolHosts';
export type UserRoleEnum = 'admin' | 'host' | 'attendee';
}
declare namespace App.Enums.Event {
export type FrequencyEnum = 'biweekly' | 'weekly' | 'monthly' | 'quarterly' | 'varies';
}
declare namespace App.Enums.Internal {
export type EventSearchField = 'name' | 'host_name' | 'host_email' | 'host_phone' | 'city' | 'state' | 'event_type' | 'frequency' | 'attendee_count' | 'start_date' | 'distance' | 'status' | 'host_street' | 'host_address_city' | 'host_address_state' | 'host_zip' | 'host_country' | 'host_wegive_id' | 'created_at';
}
declare namespace App.StatamicDTO.Blueprints.Collections.Alerts {
export type Alert = {
id: string;
title: string;
content: string | null;
start_date: string | null;
end_date: string | null;
link_text: string | null;
link_url: Array<any> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Authors {
export type Author = {
id: string;
title: string;
image: Array<any> | null;
roles_description: string | null;
bio_short: string | null;
bio_long: string | null;
updates_explanation: string | null;
updates_links: Array<App.StatamicDTO.Shared.UpdatesLinksRow> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
trigger_astro_build_field: any;
slug: string | null;
date: string;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Blogs {
export type Blog = {
id: string;
title: string;
content: string | null;
author: Array<any> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
trigger_astro_build_field: any;
slug: string | null;
date: string;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Books {
export type Book = {
id: string;
title: string;
subtitle: string | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
authors: Array<Array<any>> | null;
description: string | null;
detail_description: string | null;
buy_book_url: Array<any> | null;
topics: Array<Array<any>> | null;
publication_date: string | null;
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
trigger_astro_build_field: any;
slug: string | null;
date: string;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Events {
export type MultidayRetreat = {
id: string;
title: string;
subtitle: string | null;
description: string | null;
topics: Array<Array<any>> | null;
event_type: Array<any> | null;
is_local_event: boolean | null;
event_logo: Array<any> | null;
related_event_type: any;
icons: Array<App.StatamicDTO.Shared.IconSet> | null;
testimonials: Array<App.StatamicDTO.Shared.TestimonialSet> | null;
vertical_videos_block: App.StatamicDTO.Shared.VerticalVideosBlockGroup | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
slug: string | null;
date: string;
show_in_collections: boolean | null;
blocks: Array<App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.ImageTitleSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.ActionsSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsSet> | null;
hero_variant: string | null;
leader_text: string | null;
hero_logo: Array<any> | null;
hero_title: Array<App.StatamicDTO.Shared.TextLineSet> | null;
hero_subtitle: Array<App.StatamicDTO.Shared.CountdownSet> | null;
background_video_url: Array<any> | null;
background_video_fallback_image: Array<any> | null;
featured_video_url: Array<any> | null;
featured_video_fallback_image: Array<any> | null;
buttons: Array<App.StatamicDTO.Shared.ButtonSet | App.StatamicDTO.Shared.AddToCalendarSet> | null;
position: string | null;
width: string | null;
height: string | null;
final_details_blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
export type VideoStudy = {
id: string;
title: string;
subtitle: string | null;
description: string | null;
topics: Array<Array<any>> | null;
related_content: Array<any> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
trigger_astro_build_field: any;
slug: string | null;
date: string;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Experiences {
export type Experience = {
id: string;
title: string;
subtitle: string | null;
description: string | null;
topics: Array<Array<any>> | null;
testimonials: Array<App.StatamicDTO.Shared.TestimonialSet> | null;
subsplash_series_id: string | null;
experience_logo: Array<any> | null;
host_a_group_link: string | null;
find_a_group_link: string | null;
related_event_type: any;
sessions: Array<Array<any>> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
blocks: Array<App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.ImageTitleSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.ActionsSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsSet> | null;
hero_variant: string | null;
leader_text: string | null;
hero_logo: Array<any> | null;
hero_title: Array<App.StatamicDTO.Shared.TextLineSet> | null;
hero_subtitle: Array<App.StatamicDTO.Shared.CountdownSet> | null;
background_video_url: Array<any> | null;
background_video_fallback_image: Array<any> | null;
featured_video_url: Array<any> | null;
featured_video_fallback_image: Array<any> | null;
buttons: Array<App.StatamicDTO.Shared.ButtonSet | App.StatamicDTO.Shared.AddToCalendarSet> | null;
position: string | null;
width: string | null;
height: string | null;
trigger_astro_build_field: any;
slug: string | null;
date: string;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.MediaItem {
export type CustomMediaItem = {
id: string;
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
title: string;
description: string | null;
subsplash_media_item_id: string | null;
series: Array<any> | null;
position: number | null;
media_type: string | null;
slug: string | null;
date: string;
make_scheduled_page_public: boolean | null;
content_type: Array<Array<any>> | null;
topics: Array<Array<any>> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
export type ExperienceSession = {
id: string;
title: string;
subtitle: string | null;
description: string | null;
topics: Array<Array<any>> | null;
experience: Array<any> | null;
session_trailer: Array<any> | null;
experience_video: Array<any> | null;
background_video_url: Array<any> | null;
background_video_fallback_image: Array<any> | null;
subsplash_media_item_id: string | null;
questions: Array<App.StatamicDTO.Shared.QuestionSet> | null;
description_legacy: string | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
date: string;
slug: string | null;
content_type: Array<Array<any>> | null;
media_type: Array<Array<any>> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
export type MediaItem = {
id: string;
title: string;
subtitle: string | null;
description: string | null;
topics: Array<Array<any>> | null;
media_type: string | null;
audio_source_url: string | null;
video_source_url: string | null;
series: Array<Array<any>> | null;
slug: string | null;
date: string;
images: App.StatamicDTO.Shared.ImagesGroup | null;
content_type: Array<Array<any>> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
export type SubsplashMediaItem = {
id: string;
data_is_imported_from_subsplash: any;
override_subsplash_content: boolean | null;
title: string;
subtitle: string | null;
description: string | null;
speaker: string | null;
topics: Array<Array<any>> | null;
video_iframe_url: Array<any> | null;
video_direct_url: Array<any> | null;
vimeo_id: string | null;
is_vertical_video: boolean | null;
audio_iframe_url: Array<any> | null;
audio_direct_url: Array<any> | null;
document_title: string | null;
document_direct_url: Array<any> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
subsplash_media_item_id: string | null;
last_imported: string | null;
last_updated: string | null;
title_subsplash: string | null;
subtitle_subsplash: string | null;
date_subsplash: string | null;
description_subsplash: string | null;
images_subsplash: Array<App.StatamicDTO.Shared.NewImageSet> | null;
speaker_subsplash: string | null;
topics_subsplash: Array<Array<any>> | null;
video_iframe_url_subsplash: Array<any> | null;
video_direct_url_subsplash: Array<any> | null;
audio_iframe_url_subsplash: Array<any> | null;
audio_direct_url_subsplash: Array<any> | null;
document_title_subsplash: string | null;
document_direct_url_subsplash: Array<any> | null;
duration_subsplash: string | null;
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
slug: string | null;
date: string;
series: Array<any> | null;
content_type: Array<Array<any>> | null;
media_type: Array<any> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.MediaSeries {
export type CustomSeries = {
id: string;
title: string;
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
media_items: Array<Array<any>> | null;
subsplash_media_series_id: string | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
slug: string | null;
date: string;
hide_navigation: boolean | null;
content_type: Array<Array<any>> | null;
topics: Array<Array<any>> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
export type SubsplashMediaSeries = {
id: string;
title: string;
subsplash_media_series_id: string | null;
drupal_url: string | null;
override_subsplash_content: boolean | null;
group_field: App.StatamicDTO.Shared.GroupFieldGroup | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
slug: string | null;
date: string;
content_type: Array<Array<any>> | null;
topics: Array<Array<any>> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Pages {
export type Page = {
id: string;
title: string;
blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
content: string | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
slug: string | null;
date: string;
parent: Array<any> | null;
hide_navigation: boolean | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Podcasts {
export type Podcast = {
id: string;
title: string;
description: string | null;
speakers: Array<Array<any>> | null;
subsplash_series_id: string | null;
drupal_url: string | null;
publish_schedule: string | null;
video_or_audio: string | null;
spotify_url: Array<any> | null;
itunes_url: Array<any> | null;
youtube_url: Array<any> | null;
rss_feed_url: Array<any> | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
trigger_astro_build_field: any;
slug: string | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Collections.Prayers {
export type Prayers = {
id: string;
title: string;
before_content_blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
content: string | null;
after_content_blocks: Array<App.StatamicDTO.Shared.HeroSet | App.StatamicDTO.Shared.HeroSliderSet | App.StatamicDTO.Shared.MediaSliderSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.HeroLogoSet | App.StatamicDTO.Shared.SubscribeFormSet | App.StatamicDTO.Shared.AddToCalendarSet | App.StatamicDTO.Shared.HeaderSet | App.StatamicDTO.Shared.ContainerSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsBlockSet | App.StatamicDTO.Shared.BentoBlockSet | App.StatamicDTO.Shared.DividerBlockSet | App.StatamicDTO.Shared.VideoBlockSet | App.StatamicDTO.Shared.CodeBlockSet | App.StatamicDTO.Shared.TestimonialsBlockSet | App.StatamicDTO.Shared.ImageSliderBlockSet> | null;
summary: string | null;
pdf: Array<any> | null;
mp3: Array<any> | null;
drupal_url: string | null;
images: App.StatamicDTO.Shared.ImagesGroup | null;
seo_title: string | null;
seo_description: string | null;
seo_image: Array<any> | null;
hide_from_search_engines: boolean | null;
sitemap_priority: number;
slug: string | null;
date: string;
parent: Array<any> | null;
locale: string;
origin: string | null;
published: boolean;
private: boolean;
status: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
collection: Array<any> | null;
blueprint: Array<any> | null;
last_modified: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Globals {
export type DesktopNav = {
id: string;
desktop_nav: Array<App.StatamicDTO.Shared.MainNavItemSet | App.StatamicDTO.Shared.SingleLinkSet> | null;
handle: string;
api_url: string | null;
};
export type LocalPlatform = {
id: string;
host_agreement_content: string | null;
featured_content_enabled: boolean | null;
featured_content_image: Array<any>;
featured_content_text: string;
featured_content_url: string | null;
show_in_regions: Array<string> | null;
handle: string;
api_url: string | null;
};
export type MobileNav = {
id: string;
main_nav_bar: Array<App.StatamicDTO.Shared.StandardNavLinkSet> | null;
drawer_items: Array<App.StatamicDTO.Shared.StandardNavLinkSet2> | null;
handle: string;
api_url: string | null;
};
}
declare namespace App.StatamicDTO.Blueprints.Taxonomies.Topics {
export type Topic = {
id: string;
title: string;
slug: string;
locale: string;
uri: string | null;
url: string | null;
permalink: string | null;
api_url: string | null;
taxonomy: Array<any> | null;
};
}
declare namespace App.StatamicDTO.Shared {
export type ActionsSet = {
type: string;
id: string;
enabled: boolean;
buttons: Array<App.StatamicDTO.Shared.OpenInAppSet | App.StatamicDTO.Shared.ShareSet> | null;
};
export type AddToCalendarSet = {
type: string;
id: string;
enabled: boolean;
button_text: string | null;
button_variant: string | null;
name: string | null;
description: string | null;
options: Array<string> | null;
location: string | null;
start_date: string | null;
end_date: string | null;
start_time: string | null;
end_time: string | null;
timezone: string | null;
recurring: boolean | null;
recurrence: App.StatamicDTO.Shared.RecurrenceGroup | null;
};
export type AppPreviewBlockSet = {
type: string;
id: string;
enabled: boolean;
screenshot: Array<any>;
screen_capture_url: string | null;
};
export type BentoBlockSet = {
type: string;
id: string;
enabled: boolean;
compartments: Array<App.StatamicDTO.Shared.CompartmentSet> | null;
row_count: string | null;
column_count: string | null;
};
export type BooksSet = {
type: string;
id: string;
enabled: boolean;
author: Array<any> | null;
excluded_books: Array<Array<any>> | null;
};
export type ButtonCloudSet = {
type: string;
id: string;
enabled: boolean;
title: App.StatamicDTO.Shared.TitleGroup | null;
buttons: Array<App.StatamicDTO.Shared.ButtonSet2> | null;
position: string | null;
variant: string | null;
size: string | null;
};
export type ButtonGroup = {
text: string | null;
image: Array<any> | null;
href: Array<any> | null;
variant: string | null;
size: string | null;
left_icon: App.StatamicDTO.Shared.LeftIconGroup | null;
right_icon: App.StatamicDTO.Shared.RightIconGroup | null;
open_in_modal: boolean | null;
open_in_new_tab: boolean | null;
};
export type ButtonSet = {
type: string;
id: string;
enabled: boolean;
text: string | null;
image: Array<any> | null;
href: Array<any> | null;
variant: string | null;
size: string | null;
left_icon: App.StatamicDTO.Shared.LeftIconGroup | null;
right_icon: App.StatamicDTO.Shared.RightIconGroup | null;
open_in_modal: boolean | null;
open_in_new_tab: boolean | null;
};
export type ButtonSet2 = {
type: string;
id: string;
enabled: boolean;
text: string | null;
image: Array<any> | null;
href: Array<any> | null;
left_icon: App.StatamicDTO.Shared.LeftIconGroup | null;
right_icon: App.StatamicDTO.Shared.RightIconGroup | null;
open_in_modal: boolean | null;
open_in_new_tab: boolean | null;
};
export type ButtonsSet = {
type: string;
id: string;
enabled: boolean;
buttons: Array<App.StatamicDTO.Shared.ButtonSet | App.StatamicDTO.Shared.AddToCalendarSet> | null;
position: string | null;
};
export type CodeBlockSet = {
type: string;
id: string;
enabled: boolean;
html: string | null;
};
export type ColumnSet = {
type: string;
id: string;
enabled: boolean;
blocks: Array<App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ImageSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.ButtonCloudSet | App.StatamicDTO.Shared.AppPreviewBlockSet> | null;
column_width: any;
horizontal_alignment: string | null;
};
export type ColumnsBlockSet = {
type: string;
id: string;
enabled: boolean;
columns: Array<App.StatamicDTO.Shared.ColumnSet> | null;
};
export type ColumnsSet = {
type: string;
id: string;
enabled: boolean;
columns: Array<App.StatamicDTO.Shared.ColumnSet> | null;
};
export type CompartmentSet = {
type: string;
id: string;
enabled: boolean;
blocks: Array<App.StatamicDTO.Shared.ContentCardSet | App.StatamicDTO.Shared.ThemeCardBlockSet> | null;
row_span: string | null;
column_span: string | null;
};
export type ContainerSet = {
type: string;
id: string;
enabled: boolean;
blocks: Array<App.StatamicDTO.Shared.MiniBioSet | App.StatamicDTO.Shared.ImageSet | App.StatamicDTO.Shared.CodeBlockSet> | null;
};
export type ContentCardSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
subtitle: string | null;
href: Array<any> | null;
background_image: Array<any> | null;
background_video_url: Array<any> | null;
disable_aspect_ratio: boolean | null;
};
export type ContentInTaxonomySet = {
type: string;
id: string;
enabled: boolean;
tags: Array<Array<any>> | null;
collections_to_load_from: any;
limit: number | null;
};
export type CountdownSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
end_date_time: string | null;
show_on_mobile: boolean | null;
};
export type CustomSlideSet = {
type: string;
id: string;
enabled: boolean;
tag: string | null;
featured_image: Array<any> | null;
leader: string | null;
title: string | null;
description: string | null;
trim_description: boolean | null;
details: string | null;
right_cta: App.StatamicDTO.Shared.RightCtaGroup | null;
left_cta: App.StatamicDTO.Shared.LeftCtaGroup | null;
link: Array<any> | null;
};
export type DividerBlockSet = {
type: string;
id: string;
enabled: boolean;
show_line: boolean | null;
width: string | null;
spacing_top: string | null;
spacing_bottom: string | null;
};
export type DropdownLinkSet = {
type: string;
id: string;
enabled: boolean;
title: string;
url: string;
icon: string | null;
width: string | null;
height: string | null;
};
export type GroupFieldGroup = {
subtitle: string | null;
description: string | null;
topics: Array<Array<any>> | null;
};
export type HeaderSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
subtitle: string | null;
image: Array<any> | null;
};
export type HeroLogoSet = {
type: string;
id: string;
enabled: boolean;
hero_logo: Array<any> | null;
};
export type HeroSet = {
type: string;
id: string;
enabled: boolean;
blocks: Array<App.StatamicDTO.Shared.HeroTitleSet | App.StatamicDTO.Shared.ImageTitleSet | App.StatamicDTO.Shared.CountdownSet | App.StatamicDTO.Shared.ButtonsSet | App.StatamicDTO.Shared.ActionsSet | App.StatamicDTO.Shared.StyledTextSet | App.StatamicDTO.Shared.ColumnsSet> | null;
hero_variant: string | null;
leader_text: string | null;
hero_logo: Array<any> | null;
hero_title: Array<App.StatamicDTO.Shared.TextLineSet> | null;
hero_subtitle: Array<App.StatamicDTO.Shared.CountdownSet> | null;
background_video_url: Array<any> | null;
background_video_fallback_image: Array<any> | null;
featured_video_url: Array<any> | null;
featured_video_fallback_image: Array<any> | null;
buttons: Array<App.StatamicDTO.Shared.ButtonSet | App.StatamicDTO.Shared.AddToCalendarSet> | null;
position: string | null;
width: string | null;
height: string | null;
};
export type HeroSliderSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
delay: number | null;
slides: Array<App.StatamicDTO.Shared.SlideSet> | null;
};
export type HeroTitleSet = {
type: string;
id: string;
enabled: boolean;
hero_title: Array<App.StatamicDTO.Shared.TextLineSet> | null;
};
export type IconSet = {
type: string;
id: string;
enabled: boolean;
icon: string | null;
width: string | null;
height: string | null;
title: string | null;
subtitle: string | null;
};
export type ImageSet = {
type: string;
id: string;
enabled: boolean;
image: Array<any> | null;
small_width: any;
medium_width: any;
link: Array<any> | null;
};
export type ImageSliderBlockSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
images: Array<Array<any>> | null;
};
export type ImageTitleSet = {
type: string;
id: string;
enabled: boolean;
assets_field: Array<any> | null;
};
export type ImagesGroup = {
wide: Array<any> | null;
square: Array<any> | null;
vertical: Array<any> | null;
};
export type LeftCtaGroup = {
text: string | null;
image: Array<any> | null;
href: Array<any> | null;
variant: string | null;
size: string | null;
left_icon: App.StatamicDTO.Shared.LeftIconGroup | null;
right_icon: App.StatamicDTO.Shared.RightIconGroup | null;
open_in_modal: boolean | null;
open_in_new_tab: boolean | null;
};
export type LeftIconGroup = {
icon: string | null;
width: string | null;
height: string | null;
};
export type MainNavItemSet = {
type: string;
id: string;
enabled: boolean;
title: string;
dropdown_links: Array<App.StatamicDTO.Shared.DropdownLinkSet> | null;
bottom_link_title: string;
bottom_link_url: string;
featured_media_item_byline: string;
featured_media_item_title: string;
featured_media_item_url: string | null;
featured_media_item_image: Array<any> | null;
};
export type MediaItemSet = {
type: string;
id: string;
enabled: boolean;
media_item: Array<Array<any>> | null;
};
export type MediaItemsFromSeriesSet = {
type: string;
id: string;
enabled: boolean;
media_items_from_series: Array<any> | null;
};
export type MediaSliderSet = {
type: string;
id: string;
enabled: boolean;
section_title: string | null;
link_to_more: Array<any> | null;
media_series: Array<any> | null;
media_items: Array<Array<any>> | null;
focused_item: number | null;
slides: Array<App.StatamicDTO.Shared.SeriesSet | App.StatamicDTO.Shared.MediaItemsFromSeriesSet | App.StatamicDTO.Shared.MediaItemSet | App.StatamicDTO.Shared.CustomSlideSet | App.StatamicDTO.Shared.ContentInTaxonomySet | App.StatamicDTO.Shared.SubsplashListIdSet | App.StatamicDTO.Shared.BooksSet> | null;
};
export type MiniBioSet = {
type: string;
id: string;
enabled: boolean;
name: string | null;
image: Array<any> | null;
prose: string | null;
};
export type NewImageSet = {
type: string;
id: string;
enabled: boolean;
image_id_subsplash: string | null;
image_type_subsplash: string | null;
image_source_url_subsplash: Array<any> | null;
image_dynamic_url_subsplash: string | null;
};
export type OpenInAppSet = {
type: string;
id: string;
enabled: boolean;
icon: string | null;
width: string | null;
height: string | null;
text: string | null;
subsplash_media_id: string | null;
subsplash_series_id: string | null;
custom_deep_link: string | null;
};
export type QuestionSet = {
type: string;
id: string;
enabled: boolean;
question_text: string | null;
};
export type RecurrenceGroup = {
INTERVAL: string | null;
FREQ: string | null;
COUNT: string | null;
};
export type RightCtaGroup = {
text: string | null;
image: Array<any> | null;
href: Array<any> | null;
variant: string | null;
size: string | null;
left_icon: App.StatamicDTO.Shared.LeftIconGroup | null;
right_icon: App.StatamicDTO.Shared.RightIconGroup | null;
open_in_modal: boolean | null;
open_in_new_tab: boolean | null;
};
export type RightIconGroup = {
icon: string | null;
width: string | null;
height: string | null;
};
export type SeriesSet = {
type: string;
id: string;
enabled: boolean;
series: Array<Array<any>> | null;
};
export type ShareSet = {
type: string;
id: string;
enabled: boolean;
icon: string | null;
width: string | null;
height: string | null;
text: string | null;
title: string | null;
custom_share_text: string | null;
custom_link: string | null;
};
export type SingleLinkSet = {
type: string;
id: string;
enabled: boolean;
title: string;
url: string;
icon: string | null;
width: string | null;
height: string | null;
};
export type SlideSet = {
type: string;
id: string;
enabled: boolean;
leader: string | null;
title: string | null;
description: string | null;
background_image: Array<any> | null;
background_video_url: string | null;
button: App.StatamicDTO.Shared.ButtonGroup | null;
};
export type StandardNavLinkSet = {
type: string;
id: string;
enabled: boolean;
title: string;
url: string;
icon: string | null;
width: string | null;
height: string | null;
};
export type StandardNavLinkSet2 = {
type: string;
id: string;
enabled: boolean;
title: string;
url: string;
icon: string | null;
width: string | null;
height: string | null;
};
export type StyledTextSet = {
type: string;
id: string;
enabled: boolean;
text: string | null;
style: string | null;
color: string | null;
};
export type SubscribeFormSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
subtitle: string | null;
form_action: string | null;
disclaimer: string | null;
success_title: string | null;
success_subtitle: string | null;
};
export type SubsplashListIdSet = {
type: string;
id: string;
enabled: boolean;
subsplash_list_id: string | null;
};
export type TagSet = {
type: string;
id: string;
enabled: boolean;
text: string | null;
};
export type TestimonialSet = {
type: string;
id: string;
enabled: boolean;
body: string | null;
name: string | null;
};
export type TestimonialsBlockSet = {
type: string;
id: string;
enabled: boolean;
title: string | null;
testimonials: Array<App.StatamicDTO.Shared.TestimonialSet> | null;
display_count: string | null;
};
export type TextLineSet = {
type: string;
id: string;
enabled: boolean;
text: string | null;
text_style: string | null;
};
export type ThemeCardBlockSet = {
type: string;
id: string;
enabled: boolean;
featured_image: Array<any> | null;
leader: string | null;
title: string | null;
description: string | null;
button: App.StatamicDTO.Shared.ButtonGroup | null;
href: Array<any> | null;
tags: Array<App.StatamicDTO.Shared.TagSet> | null;
};
export type TitleGroup = {
text: string | null;
style: string | null;
color: string | null;
};
export type UpdatesLinksRow = {
id: string;
title: string | null;
link: Array<any> | null;
};
export type VerticalVideosBlockGroup = {
title: string | null;
videos: Array<App.StatamicDTO.Shared.VideoSet> | null;
};
export type VideoBlockSet = {
type: string;
id: string;
enabled: boolean;
preview_video_url: Array<any> | null;
preview_video_image: Array<any> | null;
video_button: App.StatamicDTO.Shared.VideoButtonGroup | null;
width: string | null;
};
export type VideoButtonGroup = {
text: string | null;
image: Array<any> | null;
href: Array<any> | null;
variant: string | null;
size: string | null;
left_icon: App.StatamicDTO.Shared.LeftIconGroup | null;
right_icon: App.StatamicDTO.Shared.RightIconGroup | null;
open_in_modal: boolean | null;
open_in_new_tab: boolean | null;
};
export type VideoSet = {
type: string;
id: string;
enabled: boolean;
video_url: string | null;
poster_url: string | null;
title: string | null;
subtitle: string | null;
};
}
