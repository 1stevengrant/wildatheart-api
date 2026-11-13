const LOCATION_KEY = "user_location";
const MODAL_DISMISSED_KEY = "location_modal_dismissed";
const EXPIRATION_DAYS = 30;

export const setUserLocation = (latitude: number, longitude: number): void => {
    const data = {
        latitude,
        longitude,
        timestamp: Date.now(),
    };
    localStorage.setItem(LOCATION_KEY, JSON.stringify(data));
};

export const getUserLocation = (): {
    latitude: number;
    longitude: number;
} | null => {
    const data = localStorage.getItem(LOCATION_KEY);
    if (!data) return null;

    const { latitude, longitude, timestamp } = JSON.parse(data);
    if (Date.now() - timestamp > EXPIRATION_DAYS * 24 * 60 * 60 * 1000) {
        localStorage.removeItem(LOCATION_KEY);
        return null;
    }

    return { latitude, longitude };
};

export const setModalDismissed = (): void => {
    localStorage.setItem(MODAL_DISMISSED_KEY, Date.now().toString());
};

export const shouldShowModal = (): boolean => {
    const dismissedTimestamp = localStorage.getItem(MODAL_DISMISSED_KEY);
    if (!dismissedTimestamp) return true;

    return (
        Date.now() - parseInt(dismissedTimestamp) >
        EXPIRATION_DAYS * 24 * 60 * 60 * 1000
    );
};

export const getUserZoom = (): number | null => {
    const zoom = localStorage.getItem("userZoom");
    return zoom ? parseFloat(zoom) : null;
};

export const setUserZoom = (zoom: number): void => {
    localStorage.setItem("userZoom", zoom.toString());
};
