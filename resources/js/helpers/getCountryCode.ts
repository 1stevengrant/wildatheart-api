interface IApiResponse {
    country_code: string;
}

async function getCountryCode(): Promise<string | null> {
    try {
        const response = await fetch("https://ipapi.co/json/");
        const data: IApiResponse = await response.json();
        return data.country_code;
    } catch (error) {
        console.error(error);
        return "US";
    }
}

export default getCountryCode;
