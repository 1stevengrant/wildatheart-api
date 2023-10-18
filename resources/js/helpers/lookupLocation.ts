import config from "@/helpers/config";

const lookupLocation = async (longitude: number, latitude: number) => {
    try {
        const response = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?access_token=${config('services.mapbox.token')}`)

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data.features;
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
        return null;
    }
};

export default lookupLocation;
