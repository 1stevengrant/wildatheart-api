import { defaultCountries } from "react-international-phone";
const preferredOrder: { [key: string]: number } = {
    us: 1,
    ca: 2,
    gb: 3,
    nz: 4,
    au: 5,
};

export const sortedCountries = [...defaultCountries].sort((a, b) => {
    const aCode = a[1];
    const bCode = b[1];
    const aOrder = preferredOrder[aCode] || Number.MAX_VALUE;
    const bOrder = preferredOrder[bCode] || Number.MAX_VALUE;
    return aOrder - bOrder;
});
