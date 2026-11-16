import React from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, useForm } from "@inertiajs/react";
import { CountryDropdown, RegionDropdown } from "react-country-region-selector";
import { PhoneInput } from "react-international-phone";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventDisplay } from "@/Components/Events/EventDisplay";
import { FormGrid } from "@/Components/Forms/FormGrid";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { TextField } from "@/Components/Forms/TextField";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { scrollToError } from "@/helpers/scrollToError";
import { selectClasses } from "@/helpers/selectClasses";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const ConnectWithHostPage = () => {
    const { auth, event } = useTypedPage<{
        event: App.Data.EventData;
    }>().props;
    const form = useForm({
        _method: "POST",
        given_name: auth.user?.given_name ?? "",
        family_name: auth.user?.family_name ?? "",
        phone: auth.user?.phone ?? "",
        email: auth.user?.email ?? "",
        confirm_email: auth.user?.email ?? "",
        city: "",
        state: "",
        country: "",
        opted_into_marketing: true as boolean,
    });

    const handleConfirmEmailPaste = (
        e: React.ClipboardEvent<HTMLInputElement>
    ) => {
        e.preventDefault();
    };

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        form.post(route("connect.store", event.id), {
            preserveScroll: "errors",
            onError: () => scrollToError(),
        });
    };
    return (
        <EmptyLayout
            title={`Connect with ${event.host} for ${event.name} in ${event.location}`}
        >
            <TwoColumnWrapper>
                <LeftColumn className={""}>
                    <div className={"mx-auto max-w-[500px]"}>
                        <Link
                            href={event.url}
                            className={`bg-white hover:text-p300 rounded-full h-14 w-14 flex items-center text-black justify-center transition ease-in-out duration-75`}
                        >
                            <ArrowLeftIcon className={"h-8 w-8 fill-current"} />
                        </Link>
                        <HeadingOne className={"mt-6"}>
                            Connect With {event.host}
                        </HeadingOne>

                        <form onSubmit={handleSubmit} className={"mt-6"}>
                            <FormGrid className="grid grid-cols-2 gap-4">
                                <TextField
                                    id="given_name"
                                    label="First Name"
                                    required
                                    value={form.data.given_name}
                                    autoComplete="given-name"
                                    isFocused={true}
                                    onChange={(value) =>
                                        form.setData("given_name", value)
                                    }
                                    error={form.errors.given_name}
                                />

                                <TextField
                                    id="family_name"
                                    label="Last Name"
                                    required
                                    value={form.data.family_name}
                                    autoComplete="family-name"
                                    onChange={(value) =>
                                        form.setData("family_name", value)
                                    }
                                    error={form.errors.family_name}
                                />

                                <TextField
                                    id="email"
                                    label="Email"
                                    required
                                    className="col-span-2"
                                    type="email"
                                    value={form.data.email}
                                    autoComplete="email"
                                    onChange={(value) =>
                                        form.setData("email", value)
                                    }
                                    error={form.errors.email}
                                />

                                <TextField
                                    id="confirm_email"
                                    label="Confirm Email"
                                    required
                                    className="col-span-2"
                                    type="email"
                                    value={form.data.confirm_email}
                                    autoComplete="email"
                                    onPaste={handleConfirmEmailPaste}
                                    onChange={(value) =>
                                        form.setData("confirm_email", value)
                                    }
                                    error={form.errors.confirm_email}
                                />

                                <TextField
                                    id="city"
                                    label="City"
                                    required
                                    className="col-span-2"
                                    value={form.data.city}
                                    onChange={(value) =>
                                        form.setData("city", value)
                                    }
                                    error={form.errors.city}
                                />

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="country"
                                        value="Country"
                                        required
                                    />
                                    <CountryDropdown
                                        id="country"
                                        name="country"
                                        value={form.data.country}
                                        className={selectClasses}
                                        onChange={(val) =>
                                            form.setData("country", val)
                                        }
                                    />
                                    <InputError message={form.errors.country} />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="state"
                                        value="State"
                                        required
                                    />
                                    <RegionDropdown
                                        id="state"
                                        name="state"
                                        className={selectClasses}
                                        country={form.data.country}
                                        value={form.data.state}
                                        onChange={(val) =>
                                            form.setData("state", val)
                                        }
                                    />
                                    <InputError message={form.errors.state} />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="phone"
                                        value="Phone"
                                        required
                                    />
                                    <PhoneInput
                                        value={form.data.phone ?? ""}
                                        defaultCountry={"us"}
                                        onChange={(phone: string) =>
                                            form.setData(
                                                "phone",
                                                phone ? phone.toString() : ""
                                            )
                                        }
                                    />
                                    <InputError message={form.errors.phone} />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="emailUpdates"
                                        value="Updates & Resources"
                                        className={`mb-2`}
                                    />
                                    <SwitchComponent
                                        checked={form.data.opted_into_marketing}
                                        onChange={() =>
                                            form.setData(
                                                "opted_into_marketing",
                                                !form.data.opted_into_marketing
                                            )
                                        }
                                    />
                                    <Instruction className={`mt-1`}>
                                        Send me updates and resources from Wild
                                        at Heart.
                                    </Instruction>
                                </div>
                                <div className="col-span-2">
                                    <Instruction className={`mt-1`}>
                                        By connecting with {event.host} you are
                                        expressing interest in their event and
                                        will not solicit or promote other events
                                        or offerings.
                                    </Instruction>
                                </div>
                            </FormGrid>
                            <div className="mt-12">
                                <PrimaryButton
                                    disabled={form.processing}
                                    className={"w-full"}
                                >
                                    Connect with {event.host}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </LeftColumn>
                <RightColumn>
                    <EventDisplay
                        className={
                            "max-w-[350px] md:max-w-[400px] mx-auto hidden lg:flex"
                        }
                        image={event.event_type_image}
                        name={event.display_name ?? event.event_type_name ?? ""}
                        badge={event.badge_display}
                        location={event.location}
                        hostName={event.host}
                        hostPhoto={event.host_photo}
                    />
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default ConnectWithHostPage;
