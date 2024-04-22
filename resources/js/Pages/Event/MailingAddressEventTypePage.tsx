import React, { useState } from "react";

import { Link, useForm, usePage } from "@inertiajs/react";
import { CountryDropdown, RegionDropdown } from "react-country-region-selector";
import { PhoneInput } from "react-international-phone";
import { isValidPhoneNumber } from "react-phone-number-input/min";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import FormGrid from "@/Components/Forms/FormGrid";
import InputError from "@/Components/Forms/InputError";
import InputLabel from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import sortedCountries from "@/helpers/countryList";
import priorityCountries from "@/helpers/priorityCountries";
import selectClasses from "@/helpers/selectClasses";
import { PageProps } from "@/types";
import { Event } from "@/types/Event";

const MailingAddressEventTypePage = () => {
    const page = usePage();
    const { auth } = page.props as unknown as PageProps;
    const event = page.props.event as Event;
    const phoneNumber = auth.user.phone
        ? auth.user.phone.replace(/\s/g, "")
        : "";

    const [phone, setPhone] = useState(phoneNumber);
    const { data, setData, post, processing, errors } = useForm({
        given_name: auth.user.given_name ?? "",
        family_name: auth.user.family_name ?? "",
        company: "",
        street_address: "",
        address2: "",
        city: auth.user.city ?? "",
        state: auth.user.state ?? "",
        country: "",
        phone: phoneNumber,
        zip: auth.user.postal_code ?? "",
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route("gift.store", event), {
            preserveScroll: true,
        });
    };

    return (
        <PageContainer>
            <h1>Mailing Address</h1>
            <form onSubmit={(event) => handleSubmit(event)} className="mt-4">
                <FormGrid>
                    <div>
                        <InputLabel
                            htmlFor="given_name"
                            value="First Name"
                            required
                        />
                        <TextInput
                            id="given_name"
                            name="given_name"
                            value={data.given_name}
                            autoComplete="given_name"
                            isFocused={true}
                            onChange={(e) =>
                                setData("given_name", e.target.value)
                            }
                        />
                        <InputError message={errors.given_name} />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="family_name"
                            value="Last Name"
                            required
                        />
                        <TextInput
                            id="family_name"
                            name="family_name"
                            value={data.family_name}
                            autoComplete="family_name"
                            onChange={(e) =>
                                setData("family_name", e.target.value)
                            }
                        />
                        <InputError message={errors.family_name} />
                    </div>

                    <div className={`col-span-2`}>
                        <InputLabel htmlFor="company" value="Company" />
                        <TextInput
                            id="company"
                            name="company"
                            placeholder="(Optional)"
                            value={data.company}
                            autoComplete="company"
                            onChange={(e) => setData("company", e.target.value)}
                        />
                        <InputError message={errors.company} />
                    </div>

                    <div className={`col-span-2`}>
                        <InputLabel
                            htmlFor="address"
                            value="Address"
                            required
                        />
                        <TextInput
                            id="address"
                            name="address"
                            value={data.street_address}
                            autoComplete="address"
                            onChange={(e) =>
                                setData("street_address", e.target.value)
                            }
                        />
                        <InputError message={errors.street_address} />
                    </div>

                    <div className={`col-span-2`}>
                        <TextInput
                            id="address2"
                            name="address2"
                            value={data.address2}
                            placeholder="Apt, Suite, etc."
                            onChange={(e) =>
                                setData("address2", e.target.value)
                            }
                        />
                        <InputError message={errors.address2} />
                    </div>

                    <div className="col-span-2">
                        <InputLabel
                            htmlFor="country"
                            value="Country"
                            required
                        />
                        <CountryDropdown
                            priorityOptions={priorityCountries}
                            value={data.country}
                            classes={selectClasses}
                            onChange={(val) => {
                                setData("country", val);
                            }}
                        />
                        <InputError message={errors.country} />
                    </div>

                    <div className="col-span-2 md:grid md:grid-cols-3 md:gap-4">
                        <div>
                            <InputLabel htmlFor="city" value="City" required />
                            <TextInput
                                id="city"
                                name="city"
                                value={data.city}
                                onChange={(e) =>
                                    setData("city", e.target.value)
                                }
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="state"
                                value="State"
                                required
                            />
                            <RegionDropdown
                                classes={selectClasses}
                                country={data.country}
                                value={data.state}
                                onChange={(val) => setData("state", val)}
                            />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="zip"
                                value="Zip/Postal Code"
                                required
                            />
                            <TextInput
                                id="zip"
                                name="zip"
                                value={data.zip}
                                onChange={(e) => setData("zip", e.target.value)}
                            />
                        </div>
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
                            value={phone}
                            onChange={(phone) => {
                                setPhone(phone);
                                setData("phone", phone);
                            }}
                        />
                        <InputError message={errors.phone} />
                    </div>
                </FormGrid>

                <div className="mt-4">
                    <PrimaryButton
                        className={"w-full"}
                        disabled={!isValidPhoneNumber(phone) || processing}
                    >
                        Create Event
                    </PrimaryButton>
                </div>
            </form>
        </PageContainer>
    );
};

export default MailingAddressEventTypePage;
