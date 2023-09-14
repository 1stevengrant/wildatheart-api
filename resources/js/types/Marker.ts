export type Marker = {
    city: string;
    country: string;
    host: string;
    hostPhoto: string;
    id: string;
    location: string
    name: string;
    objectID: string;
    state: string;
    url: string;
    _geoloc: {
        lat: number;
        lng: number;
    }
    _highlightResult: {
        city: {
            matchLevel: string;
            matchedWords: string[];
            value: string;
        },
        country: {
            matchLevel: string;
            matchedWords: string[];
            value: string;
        },
        name: {
            matchLevel: string;
            matchedWords: string[];
            value: string;
        },
        state: {
            matchLevel: string;
            matchedWords: string[];
            value: string;
        }
    },
    _tags: string[];
};


