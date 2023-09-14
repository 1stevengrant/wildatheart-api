import algoliasearch from "algoliasearch";

const indexName = process.env.MIX_SCOUT_PREFIX + 'wah_local'
const searchClient = algoliasearch(
    process.env.MIX_REACT_APP_ALGOLIA_APP_ID as string,
    process.env.MIX_REACT_APP_ALGOLIA_API_KEY as string
);

export {indexName, searchClient}
