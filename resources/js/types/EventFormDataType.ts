export type EventFormDataType = {
    name: string;
    state?: string | null;
    city?: string | null;
    zip?: string | null;
    country?: string | null;
    latitude: number | null;
    longitude: number | null;
    region?: string | null;
    district?: string | null;
    locality?: string | null;
    neighborhood?: string | null;
    street_address?: string | null;
    poi?: string | null;
    is_private?: boolean | null;
    start_date?: Date | null;
    end_date?: Date | null;
    number_of_items_in_material_order?: number | string | null;
};
