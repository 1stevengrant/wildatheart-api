import algoliasearch from "algoliasearch";

const indexName = import.meta.env.VITE_SCOUT_PREFIX + 'wah_local'
const searchClient = algoliasearch(
    import.meta.env.VITE_REACT_APP_ALGOLIA_APP_ID as string,
    import.meta.env.VITE_REACT_APP_ALGOLIA_API_KEY as string
);

export {indexName, searchClient}
