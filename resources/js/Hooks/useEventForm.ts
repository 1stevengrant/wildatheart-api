import type { FormDataType } from "@inertiajs/core";
import { useForm } from "@inertiajs/react";

import type { LocationData } from "@/Hooks/useEventLocation";

export type EventLocationFields = Pick<
    App.Data.FireEventData,
    | "latitude"
    | "longitude"
    | "country"
    | "region"
    | "district"
    | "locality"
    | "neighborhood"
    | "street_address"
    | "poi"
    | "city"
    | "zip"
    | "state"
    | "place"
>;

export const emptyEventLocation: EventLocationFields = {
    latitude: null,
    longitude: null,
    country: null,
    region: null,
    district: null,
    locality: null,
    neighborhood: null,
    street_address: null,
    poi: null,
    city: null,
    zip: null,
    state: null,
    place: null,
};

export const eventLocationFrom = (
    location: Partial<EventLocationFields>
): EventLocationFields => ({
    latitude: location.latitude ?? null,
    longitude: location.longitude ?? null,
    country: location.country ?? null,
    region: location.region ?? null,
    district: location.district ?? null,
    locality: location.locality ?? null,
    neighborhood: location.neighborhood ?? null,
    street_address: location.street_address ?? null,
    poi: location.poi ?? null,
    city: location.city ?? null,
    zip: location.zip ?? null,
    state: location.state ?? null,
    place: location.place ?? null,
});

type EventFormFields = EventLocationFields & {
    is_private?: boolean | null;
};

export const useEventForm = <T extends EventFormFields & FormDataType<T>>(
    initialData: T
) => {
    const form = useForm<T>(initialData);

    const updateLocationData = (locationData: LocationData) => {
        form.setData((data) => ({
            ...data,
            ...eventLocationFrom(locationData),
        }));
    };

    const isPrivate = Boolean(form.data.is_private);

    const handlePrivacyToggle = () => {
        form.setData((data) => ({ ...data, is_private: !data.is_private }));
    };

    return {
        ...form,
        isPrivate,
        handlePrivacyToggle,
        updateLocationData,
    };
};
