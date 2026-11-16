import { CountryDropdown, RegionDropdown } from "react-country-region-selector";
import { PhoneInput } from "react-international-phone";

import { FormGrid } from "@/Components/Forms/FormGrid";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { TextField } from "@/Components/Forms/TextField";
import { sortedCountries } from "@/helpers/countryList";
import { priorityCountries } from "@/helpers/priorityCountries";
import { selectClasses } from "@/helpers/selectClasses";

export type MailingAddress = {
    given_name: string;
    family_name: string;
    company: string;
    street_address: string;
    address2: string;
    city: string;
    state: string;
    country: string;
    phone: string;
    zip: string;
};

export const MailingAddressFields = ({
    data,
    setData,
    errors,
}: {
    data: MailingAddress;
    setData: (key: keyof MailingAddress, value: string) => void;
    errors: Partial<Record<keyof MailingAddress, string>>;
}) => {
    return (
        <FormGrid>
            <TextField
                id="given_name"
                label="First Name"
                required
                value={data.given_name}
                autoComplete="given-name"
                isFocused={true}
                onChange={(value) => setData("given_name", value)}
                error={errors.given_name}
            />

            <TextField
                id="family_name"
                label="Last Name"
                required
                value={data.family_name}
                autoComplete="family-name"
                onChange={(value) => setData("family_name", value)}
                error={errors.family_name}
            />

            <TextField
                id="company"
                label="Company"
                className="col-span-2"
                placeholder="(Optional)"
                value={data.company}
                autoComplete="company"
                onChange={(value) => setData("company", value)}
                error={errors.company}
            />

            <TextField
                id="address"
                label="Address"
                required
                className="col-span-2"
                value={data.street_address}
                autoComplete="address"
                onChange={(value) => setData("street_address", value)}
                error={errors.street_address}
            />

            <TextField
                id="address2"
                className="col-span-2"
                placeholder="Apt, Suite, etc."
                value={data.address2}
                onChange={(value) => setData("address2", value)}
                error={errors.address2}
            />

            <div className="col-span-2">
                <InputLabel htmlFor="country" value="Country" required />
                <CountryDropdown
                    id="country"
                    name="country"
                    priorityOptions={priorityCountries}
                    value={data.country}
                    className={selectClasses}
                    onChange={(value) => setData("country", value)}
                />
                <InputError message={errors.country} />
            </div>

            <div className="col-span-2 md:grid md:grid-cols-3 md:gap-4">
                <TextField
                    id="city"
                    label="City"
                    required
                    value={data.city}
                    onChange={(value) => setData("city", value)}
                />

                <div>
                    <InputLabel htmlFor="state" value="State" required />
                    <RegionDropdown
                        id="state"
                        name="state"
                        className={selectClasses}
                        country={data.country}
                        value={data.state}
                        onChange={(value) => setData("state", value)}
                    />
                </div>

                <TextField
                    id="zip"
                    label="Zip/Postal Code"
                    required
                    value={data.zip}
                    onChange={(value) => setData("zip", value)}
                />
            </div>

            <div className="col-span-2">
                <InputError message={errors.city} />
                <InputError message={errors.state} />
                <InputError message={errors.zip} />
            </div>

            <div className="col-span-2">
                <InputLabel htmlFor="phone" value="Phone" required />
                <PhoneInput
                    defaultCountry="us"
                    countries={sortedCountries}
                    value={data.phone}
                    onChange={(phone) => setData("phone", phone)}
                />
                <InputError message={errors.phone} />
            </div>
        </FormGrid>
    );
};
