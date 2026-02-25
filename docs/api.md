# API

All API endpoints are publicly accessible under the `/api` prefix. None require authentication. The only rate-limiting is on the event connect endpoint via `throttle:connect`.

**Total: 38 public endpoints** (37 GET, 1 POST)

## V1 Core (`/api/v1`)

### Stats

-   URL: `/api/v1/stats`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\StatsController`

### Webhooks

#### Refresh Daily Readings

-   URL: `/api/v1/webhooks/refresh-daily-readings`
-   Method: `POST`
-   Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@refreshDailyReadings`

### Navigation

#### All Navs

-   URL: `/api/v1/navs/all`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Navs\AllNavsController`

#### Desktop Nav

-   URL: `/api/v1/navs/desktop`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Navs\DesktopNavController`

#### Mobile Nav

-   URL: `/api/v1/navs/mobile`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Navs\MobileNavController`

### Events

#### Event Index

-   URL: `/api/v1/events`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\EventController@index`

#### Event Search

-   URL: `/api/v1/events/search`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\SearchForEventsController`

This controller will gather events based on the map bounds from the front end that are updated on the fly as the user moves the map. The controller will return a JSON response with the events that fall within the map bounds.

This controller also includes an `offsetting service` at `App\Services\EventOffsettingService` that moves the events slightly from their exact location to prevent overlapping to a degree and also for an element of privacy before a potential attendee has signed up for an event.

Caching is also implemented in this controller to prevent the same search from being run multiple times in a short period of time.

#### Event Types

-   URL: `/api/v1/events/types`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\EventTypeController`

#### Event Show

-   URL: `/api/v1/events/{event}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\EventController@show`

#### Event Connect (Attend)

-   URL: `/api/v1/events/{event}/connect`
-   Method: `POST`
-   Middleware: `throttle:connect`
-   Controller: `App\Http\Controllers\API\V1\AttendeeController@store`

## Content API (`/api/content`)

### Media & Series

#### Series Show

-   URL: `/api/content/series/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\MediaSeriesDataController@show`

#### Media by Series ID

-   URL: `/api/content/media-by-series-id/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\MediaItemDataController@mediaBySeriesId`

#### Media Redirects

-   URL: `/api/content/media/redirects`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\MediaItemDataController@getRedirects`

#### Media Show

-   URL: `/api/content/media/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\MediaItemDataController@media`

#### Subsplash Media

-   URL: `/api/content/subsplash-media/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\MediaItemDataController@subsplashMedia`

### Subsplash Proxy

-   URL: `/api/content/subsplash/{any}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\SubsplashController@getProxy`

Wildcard proxy route for Subsplash API requests.

### Experiences

#### All Experiences

-   URL: `/api/content/experiences/all`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\ExperienceDataController@all`

#### Experience Show

-   URL: `/api/content/experiences/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\ExperienceDataController@show`

## Content V1 API (`/api/content/v1`)

### SEO

#### Sitemap

-   URL: `/api/content/v1/seo/sitemap`
-   Method: `GET`
-   Controller: `App\Http\Controllers\SeoController@getSitemap`

### Search

-   URL: `/api/content/v1/search`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\SearchController@index`

### Blogs

#### Blog Index

-   URL: `/api/content/v1/blogs`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\BlogsController@index`

Returns a list of all blog posts.

#### Blog Show / Contributor Blogs

-   URL: `/api/content/v1/blogs/{uri}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\BlogsController@show`

This endpoint can return the posts for a specific contributor or a single blog post.

### Daily Reading

#### Daily Reading Index

-   URL: `/api/content/v1/daily-reading`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@index`

Returns the daily reading content from the Transistor.fm API.

#### Daily Reading Recent

-   URL: `/api/content/v1/daily-reading/recent`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@getRecent`

Returns the most recent daily reading content.

#### Daily Reading Redirect

-   URL: `/api/content/v1/daily-reading/redirect/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@redirect`

Redirects the user to the correct daily reading content based on the slug.

### Pages

-   URL: `/api/content/v1/page/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\PageEntryController@getPageBySlug`

Returns a page's content by its slug.

### Media

#### Media Show

-   URL: `/api/content/v1/media/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\MediaItemEntryController@media`

#### Media or Series

-   URL: `/api/content/v1/media-or-series/{slugOrUri}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\MediaItemOrSeriesController@show`

### Prayers

#### Prayers Index

-   URL: `/api/content/v1/prayers`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\PrayersEntryController@index`

Returns a list of all prayers.

#### Prayer Show

-   URL: `/api/content/v1/prayers/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\PrayersEntryController@show`

Returns a single prayer by its slug.

### Events (Statamic)

#### Events Index

-   URL: `/api/content/v1/events`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\EventsEntryController@index`

#### Event Show

-   URL: `/api/content/v1/events/{slug}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\EventsEntryController@show`

### Alerts

-   URL: `/api/content/v1/alerts`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\AlertEntryController@index`

### Subsplash App

#### Daily Reading Today

-   URL: `/api/content/v1/subsplash/daily-readings`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@dailyReadingToday`

#### Daily Reading Listen

-   URL: `/api/content/v1/subsplash/daily-readings/listen/{id?}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@dailyReadingsListen`

#### Daily Readings List

-   URL: `/api/content/v1/subsplash/daily-readings/list`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@dailyReadingsList`

#### Daily Reading by ID

-   URL: `/api/content/v1/subsplash/daily-readings/{id}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@dailyReading`

#### Prayers

-   URL: `/api/content/v1/subsplash/prayers`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@prayers`

#### Prayer by ID

-   URL: `/api/content/v1/subsplash/prayers/{id}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@prayer`

#### Listen Prayer

-   URL: `/api/content/v1/subsplash/prayers/listen/{id}`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\V1\Content\SubsplashAppController@listenPrayer`

## Root-Level Endpoints (`/api`)

### Event Search (Legacy)

-   URL: `/api/events/search`
-   Method: `GET`
-   Controller: `App\Http\Controllers\API\SearchForEventsController`

### All Events

-   URL: `/api/events/all`
-   Method: `GET`
-   Controller: `App\Http\Controllers\AllEventsController`
