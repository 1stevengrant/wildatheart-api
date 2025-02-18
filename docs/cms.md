# CMS
The Local platform has some content managed sections and for that we make use of Statamic CMS.

Statamic by default is a flat-file CMS, but we have opted to use the first-party Eloquent driver to allow us to use a MySQL database to store our content.

We use the flat-file configuration for content types.

## Addons

The [Runway](https://runway.duncanmcclean.com/) addon allows us to edit native Laravel models in the Statamic control panel rather than using something like Laravel Nova or Filament as a separate admin panel.

We have a custom `Wild at Heart` addon that is accessed via the `Tools` section of the Statamic control panel. This is intended as a general utility for managing external services.

It can be used to manage:
- Triggering Astro builds - not all content is dynamic and so a manual build needs to be triggered. @TODO: implement a notification when an Astro build completes.
