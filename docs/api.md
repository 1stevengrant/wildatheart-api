# API Feature Development

Run these commands to make API features work if you've pulled the production database

-   run `php artisan app:fake-events-with-airport-data`
    this will create a bunch of events with airport data for testing purposes that are more realistic than the default seeders

## API Feature Deployment

-   update the sex column on the events table to be for men `php artisan app:update-sex-on-events`
-   import events from Drupal `php artisan app:import-from-drupal`
