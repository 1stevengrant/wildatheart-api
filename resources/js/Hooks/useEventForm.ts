import { useState } from "react";

import { useForm } from "@inertiajs/react";

interface UseEventFormProps {
    initialData?: Partial<App.Data.EventFormData>;
    user: any;
    eventType?: any;
}

export const useEventForm = ({ initialData, user }: UseEventFormProps) => {
    const [isPrivate, setIsPrivate] = useState(
        initialData?.is_private || false
    );

    const { data, setData, post, put, processing, errors } =
        useForm<App.Data.EventFormData>({
            name: initialData?.name || `${user.given_name}'s`,
            state: initialData?.state || "",
            city: initialData?.city || "",
            zip: initialData?.zip || "",
            country: initialData?.country || "",
            latitude: initialData?.latitude || null,
            longitude: initialData?.longitude || null,
            region: initialData?.region || "",
            district: initialData?.district || "",
            locality: initialData?.locality || "",
            neighborhood: initialData?.neighborhood || "",
            street_address: initialData?.street_address || "",
            poi: initialData?.poi || "",
            is_private: initialData?.is_private || false,
            start_date: initialData?.start_date || null,
            end_date: initialData?.end_date || null,
            frequency: initialData?.frequency || "monthly",
            number_of_items_in_material_order:
                initialData?.number_of_items_in_material_order || null,
            ...initialData,
        });

    const updateLocationData = (locationData: any) => {
        // Update all location fields, clearing old values when new location doesn't have them
        Object.keys(locationData).forEach((key) => {
            const value = locationData[key];
            // Keep null for coordinates, empty string for text fields
            const defaultValue =
                key === "latitude" || key === "longitude" ? null : "";
            setData(key as keyof App.Data.EventFormData, value ?? defaultValue);
        });
    };

    const handlePrivacyToggle = () => {
        setIsPrivate(!isPrivate);
        setData("is_private", !isPrivate);
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
