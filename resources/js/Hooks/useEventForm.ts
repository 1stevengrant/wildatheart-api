import { useState } from "react";

import type { FormDataKeys } from "@inertiajs/core";
import { useForm } from "@inertiajs/react";

interface UseEventFormProps<T> {
    initialData?: Partial<T> & Partial<App.Data.EventFormData>;
    user: any;
    eventType?: any;
}

export const useEventForm = <
    T extends Record<string, any> = App.Data.EventFormData,
>({
    initialData,
    user,
}: UseEventFormProps<T>) => {
    const seed = (initialData ?? {}) as Partial<App.Data.EventFormData>;

    const [isPrivate, setIsPrivate] = useState(seed.is_private || false);

    const { data, setData, post, put, processing, errors } = useForm<T>({
        name: seed.name || `${user.given_name}'s`,
        state: seed.state || "",
        city: seed.city || "",
        zip: seed.zip || "",
        country: seed.country || "",
        latitude: seed.latitude || null,
        longitude: seed.longitude || null,
        region: seed.region || "",
        district: seed.district || "",
        locality: seed.locality || "",
        neighborhood: seed.neighborhood || "",
        street_address: seed.street_address || "",
        poi: seed.poi || "",
        is_private: seed.is_private || false,
        start_date: seed.start_date || null,
        end_date: seed.end_date || null,
        frequency: seed.frequency || "monthly",
        number_of_items_in_material_order:
            seed.number_of_items_in_material_order || null,
        ...initialData,
    } as unknown as T);

    const updateLocationData = (locationData: any) => {
        // Update all location fields, clearing old values when new location doesn't have them
        Object.keys(locationData).forEach((key) => {
            const value = locationData[key];
            // Keep null for coordinates, empty string for text fields
            const defaultValue =
                key === "latitude" || key === "longitude" ? null : "";
            setData(key as FormDataKeys<T>, (value ?? defaultValue) as never);
        });
    };

    const handlePrivacyToggle = () => {
        const next = !isPrivate;
        setIsPrivate(next);
        setData("is_private" as FormDataKeys<T>, next as never);
    };

    return {
        data,
        setData,
        post,
        put,
        processing,
        errors,
        isPrivate,
        setIsPrivate,
        handlePrivacyToggle,
        updateLocationData,
    };
};
