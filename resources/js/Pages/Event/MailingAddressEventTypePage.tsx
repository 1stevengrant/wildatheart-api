import React from "react";

import { ArrowLeftIcon, ArrowRightIcon } from "@heroicons/react/20/solid";
import { Link, useForm } from "@inertiajs/react";
import { singular } from "pluralize";
import { CountryDropdown, RegionDropdown } from "react-country-region-selector";
import { PhoneInput } from "react-international-phone";
import { isValidPhoneNumber } from "react-phone-number-input/min";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { FormGrid } from "@/Components/Forms/FormGrid";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { sortedCountries } from "@/helpers/countryList";
import { priorityCountries } from "@/helpers/priorityCountries";
import { scrollToError } from "@/helpers/scrollToError";
import { selectClasses } from "@/helpers/selectClasses";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const MailingAddressEventTypePage = () => {
    const page = useTypedPage();
    const { auth } = page.props;
    const { address } = page.props as any;

    const eventType = page.props.eventType as any;
    const phoneNumber = auth.user?.phone?.replace(/\s/g, "") ?? "";

    const { data, setData, post, processing, errors } = useForm<{
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
    }>({
        given_name: auth.user?.given_name ?? "",
        family_name: auth.user?.family_name ?? "",
        company: "",
        street_address: address?.street_address ?? "",
        address2: address?.address2 ?? "",
        city: address?.city ?? "",
        state: address?.state ?? "",
        country:
            !address?.country || address.country === "US"
                ? "United States"
                : address.country,
        phone: phoneNumber,
        zip: address?.zip ?? "",
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route("host.eventType.mailing.store", eventType), {
            preserveScroll: true,
            onError: () => scrollToError(),
        });
    };

    return (
        <EmptyLayout title={`Event Mailing Address`}>
            <SidebarLayout
                footer={
                    <EmptyLayoutFooter>
                        <Link
                            className={circularButtonClasses}
                            href={route("host.eventType.faqs", eventType)}
                        >
                            <ArrowLeftIcon className="h-4 w-4" />
                        </Link>
                        <PrimaryButton
                            onClick={handleSubmit}
                            disabled={
                                !isValidPhoneNumber(data.phone) || processing
                            }
                        >
                            Save Mailing Address
                            <ArrowRightIcon className="ml-2 h-4 w-4" />
                        </PrimaryButton>
                    </EmptyLayoutFooter>
                }
            >
                <EmptyLayoutBody>
                    <PageContainer>
                        <HeadingOne>Mailing Address</HeadingOne>
                        {eventType.allow_materials_order && (
                            <p>
                                Tell us where we should send your free{" "}
                                {singular(eventType.name)} resources.
                            </p>
                        )}
                        <form className="my-4">
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
                                        autoComplete="given-name"
                                        isFocused={true}
                                        onChange={(e) =>
                                            setData(
                                                "given_name",
                                                e.target.value
                                            )
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
                                        autoComplete="family-name"
                                        onChange={(e) =>
                                            setData(
                                                "family_name",
                                                e.target.value
                                            )
                                        }
                                    />
                                    <InputError message={errors.family_name} />
                                </div>

                                <div className={`col-span-2`}>
                                    <InputLabel
                                        htmlFor="company"
                                        value="Company"
                                    />
                                    <TextInput
                                        id="company"
                                        name="company"
                                        placeholder="(Optional)"
                                        value={data.company}
                                        autoComplete="company"
                                        onChange={(e) =>
                                            setData("company", e.target.value)
                                        }
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
                                            setData(
                                                "street_address",
                                                e.target.value
                                            )
                                        }
                                    />
                                    <InputError
                                        message={errors.street_address}
                                    />
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
                                        id="country"
                                        name="country"
                                        priorityOptions={priorityCountries}
                                        value={data.country}
                                        className={selectClasses}
                                        onChange={(val) => {
                                            setData("country", val);
                                        }}
                                    />
                                    <InputError message={errors.country} />
                                </div>

                                <div className="col-span-2 md:grid md:grid-cols-3 md:gap-4">
                                    <div>
                                        <InputLabel
                                            htmlFor="city"
                                            value="City"
                                            required
                                        />
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
                                            id="state"
                                            name="state"
                                            className={selectClasses}
                                            country={data.country}
                                            value={data.state}
                                            onChange={(val) =>
                                                setData("state", val)
                                            }
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
                                            onChange={(e) =>
                                                setData("zip", e.target.value)
                                            }
                                        />
                                    </div>
                                </div>

                                <div className="col-span-2">
                                    <InputError message={errors.city} />
                                    <InputError message={errors.state} />
                                    <InputError message={errors.zip} />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="phone"
                                        value="Phone"
                                        required
                                    />
                                    <PhoneInput
                                        defaultCountry="us"
                                        countries={sortedCountries}
                                        value={data.phone}
                                        onChange={(phone) => {
                                            setData("phone", phone);
                                        }}
                                    />
                                    <InputError message={errors.phone} />
                                </div>
                            </FormGrid>
                        </form>
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default MailingAddressEventTypePage;
