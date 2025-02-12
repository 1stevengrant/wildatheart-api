# Front End

The Local platform is build using React JavaScript library with TypeScript. Untypically though, we also make use of Inertia.js to render the front end. Inertia.js is a library that allows you to render single-page applications using server-side routing. This means that we can use React to build the front end, but still have the benefits of server-side routing via Laravel.

Tailwind CSS is used for styling the front end. Tailwind is a utility-first CSS framework that allows you to build custom designs without having to write custom CSS.

All React components live inside the `resources/js` directory and are loosly organized by feature. The `resources/js/Pages` directory contains all the pages that are rendered by Inertia.js. The `resources/js/Components` directory contains all the React components that are used to build the pages.

## State Management
While Laravel and Inertia are responsible for the majority of the application's state, we also make use of Recoil, specifically around the mapping and filtering of events.

## Hooks
We make use of custom hooks to manage the state of the application. These hooks are located in the `resources/js/Hooks` directory.

## Third-party Libraries
There are a handful of key third-party libraries that we use in the front end:
- React Map GL: Used for rendering the map on the home page.
- Mantine: used for their date picker component

## Build Process
The front end is built using Vite and is compiled into the `public` directory. The `vite.config.js` file contains the configuration for the build process. The commands to build the front end are:
- `npm run dev`: Start the development server
- `npm run build`: Build the front end for production
- `npm run lint`: Lint the front end code
- `npm run lint:fix`: Lint the front end code and fix any issues
- `npm run prettier:fix`: Format the front end code
- `npm run format`: Format the front end code
