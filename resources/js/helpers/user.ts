import { config } from "./config";

export const imageGenerator = (
    image: string | undefined,
    params: {
        [key: string]: string | number;
    } = {}
): string => {
    if (!image) {
        return "";
    }

    const apiUrl = config("services.bunny.api_url");

    const stringParams: Record<string, string> = Object.fromEntries(
        Object.entries(params).map(([key, value]) => [key, String(value)])
    );

    const queryString = new URLSearchParams(stringParams).toString();
    return `${apiUrl}/${image}?${queryString}`;
};
