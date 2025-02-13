# API
While the vast majority of the Local platform uses Laravel and Inertia.js, there is an event search endpoint that is responsible for returning events based on a user's search criteria.

## Endpoints
### Event Search 
- URL: `/api/events/search`
- Method: `GET`
- Controller: `App\Http\Controllers\API\SearchForEventsController`

This controller will gather events based on the map bounds from the front end that are updated on the fly as the user moves the map. The controller will return a JSON response with the events that fall within the map bounds.

This controller also includes an `offsetting service` at `App\Services\EventOffsettingService` that moves the events slightly from their exact location to prevent overlapping to a degree and also for an element of privacy before a potential attendee has signed up for an event.

Caching is also implemented in this controller to prevent the same search from being run multiple times in a short period of time.

### Statamic Content API
Statamic as a CMS ships with a headless API that is used in some parts of the Wild at Heart website. You can read about the specifics of the Statamic API in the [Statamic documentation](https://statamic.dev/rest-api).

### Custom Statamic Content API
As we pull content from a number of different sources like Subsplash and Transistor.fm we have created a custom Statamic API that pulls in content from these sources and returns it in a format that is easy to consume by the front end.

#### Alerts
- URL: `/api/content/v1/alerts`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\AlertEntryController@index`

#### Blogs
- URL: `/api/content/v1/blogs`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\BlogsController@index`

Returns a list of all blog posts

#### Contributor Blogs & Single Blog Post
- URL: `/api/content/v1/blogs/{uri}`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\BlogsController@show`

This endpoint can return the posts for a specific contributor or a single blog post.

#### Daily Reading
- URL: `/api/content/v1/daily-reading`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@index`

This controller returns the daily reading content for the Wild at Heart website from the Transistor.fm API.

#### Daily Reading Recent
- URL: `/api/content/v1/daily-reading/recent`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@getRecent`

Returns the most recent daily reading content.

#### Daily Reading Redirect
- URL: `/api/content/v1/daily-reading/redirect/{slug}`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\DailyReadingController@redirect`

This controller is used to redirect the user to the correct daily reading content based on the slug.

#### Media or Series
- URL: `/api/content/v1/media-or-series/{slugOrUri}`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\MediaItemOrSeriesController@show`

@TODO: Add more information about this endpoint

#### Media
- URL: `/api/content/v1/media/{slug}`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\MediaItemEntryController@media`

@TODO: Add more information about this endpoint

#### Page
- URL: `/api/content/v1/page/{slug}`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\PageEntryController@getPageBySlug`

This controller returns a page's content by its slug.

#### Prayers
- URL: `/api/content/v1/prayers`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\PrayersEntryController@index`

Returns a list of all prayers

#### Prayers Single
- URL: `/api/content/v1/prayers/{slug}`
- Method: `GET`
- Controller: `App\Http\Controllers\API\V1\Content\PrayersEntryController@show`

Returns a single prayer by its slug.
