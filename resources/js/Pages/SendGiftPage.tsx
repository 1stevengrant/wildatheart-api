import React from "react";

import { Link, useForm } from "@inertiajs/react";
import { singular } from "pluralize";
import { CountryDropdown, RegionDropdown } from "react-country-region-selector";
import { PhoneInput } from "react-international-phone";
import { isValidPhoneNumber } from "react-phone-number-input";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { FormGrid } from "@/Components/Forms/FormGrid";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { sortedCountries } from "@/helpers/countryList";
import { priorityCountries } from "@/helpers/priorityCountries";
import { scrollToError } from "@/helpers/scrollToError";
import { selectClasses } from "@/helpers/selectClasses";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const SendGiftPage = () => {
    const { auth, event } = useTypedPage<{
        event: App.Data.EventData;
    }>().props;

    const phoneNumber = auth.user?.phone?.replace(/\s/g, "") ?? "";

    const { data, setData, post, processing, errors } = useForm({
        given_name: auth.user?.given_name ?? "",
        family_name: auth.user?.family_name ?? "",
        company: "",
        street_address: "",
        address2: "",
        city: "",
        state: "",
        country: "",
        phone: phoneNumber,
        zip: "",
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route("gift.store", { event: event }), {
            preserveScroll: true,
            onError: () => scrollToError(),
        });
    };

    return (
        <EmptyLayout title={`Claim Your Gift`}>
            <TwoColumnWrapper>
                <LeftColumn>
                    <div className={`max-w-[500px] mx-auto p-4`}>
                        <HeadingOne>
                            Huzzah!
                            <br />
                            Your {singular(event.event_type.name)} has been
                            registered.
                        </HeadingOne>

                        <p className="mt-8">
                            We'd like to send you a Firestarter kit. Where would
                            you like us to send it?
                        </p>

                        <form
                            onSubmit={(event) => handleSubmit(event)}
                            className="mt-4"
                        >
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

                            <div className="mt-4">
                                <PrimaryButton
                                    className={"w-full"}
                                    disabled={
                                        !isValidPhoneNumber(data.phone) ||
                                        processing
                                    }
                                >
                                    Send Me My Gift
                                </PrimaryButton>
                                <Link
                                    href={route("my.events.active")}
                                    className={`text-center py-3 text-sm text-n40 block`}
                                >
                                    No thanks
                                </Link>
                            </div>
                        </form>
                    </div>
                </LeftColumn>
                <RightColumn>
                    <div className={`mx-auto`}>
                        {event.event_type.free_gift_image ? (
                            <img
                                src={imageGenerator(
                                    event.event_type.free_gift_image
                                )}
                                className={`max-h-[700px] hidden md:block`}
                                alt=""
                            />
                        ) : (
                            <img
                                src="/img/fires-gift-image-v2.webp"
                                className={`max-h-[700px] hidden md:block`}
                                alt=""
                            />
                        )}
                    </div>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default SendGiftPage;
