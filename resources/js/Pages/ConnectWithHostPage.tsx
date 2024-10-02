import React, { useState } from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link, useForm, usePage } from "@inertiajs/react";
import { CountryDropdown, RegionDropdown } from "react-country-region-selector";
import { PhoneInput } from "react-international-phone";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventDisplay } from "@/Components/Events/EventDisplay";
import { FormGrid } from "@/Components/Forms/FormGrid";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import TextInput from "@/Components/Forms/TextInput";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { scrollToError } from "@/helpers/scrollToError";
import { selectClasses } from "@/helpers/selectClasses";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { PageProps } from "@/types";
import { Event } from "@/types/Event";

const ConnectWithHostPage = () => {
    const page = usePage();
    const event = page.props.event as Event;
    const { auth } = page.props as unknown as PageProps;
    const [processing, setProcessing] = useState(false);
    const form = useForm({
        _method: "POST",
        given_name: auth.user?.given_name ?? "",
        family_name: auth.user?.family_name ?? "",
        phone: auth.user?.phone ?? "",
        email: auth.user?.email ?? "",
        confirm_email: auth.user?.email ?? "",
        city: auth.user?.city ?? "",
        state: "",
        country: "",
        opted_into_marketing: true,
    });

    const handleConfirmEmailPaste = (
        e: React.ClipboardEvent<HTMLInputElement>
    ) => {
        e.preventDefault();
    };

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        setProcessing(true);
        e.preventDefault();
        form.post(route("connect.store", event.id), {
            preserveScroll: "errors",
            onFinish: () => {
                setProcessing(false);
            },
            onError: () => {
                scrollToError();
                setProcessing(false);
            },
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
                            className={
                                "bg-white rounded-full h-14 w-14 flex items-center" +
                                " justify-center"
                            }
                        >
                            <ArrowLeftIcon className={"h-8 w-8 text-black"} />
                        </Link>
                        <HeadingOne className={"mt-6"}>
                            Connect With {event.host}
                        </HeadingOne>

                        <form onSubmit={handleSubmit} className={"mt-6"}>
                            <FormGrid className="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        htmlFor="given_name"
                                        value="First Name"
                                        required
                                    />
                                    <TextInput
                                        id="given_name"
                                        name="given_name"
                                        value={form.data.given_name}
                                        autoComplete="given_name"
                                        isFocused={true}
                                        onChange={(e) =>
                                            form.setData(
                                                "given_name",
                                                e.target.value
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.given_name}
                                    />
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
                                        value={form.data.family_name}
                                        autoComplete="family_name"
                                        onChange={(e) =>
                                            form.setData(
                                                "family_name",
                                                e.target.value
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.family_name}
                                    />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="email"
                                        value="Email"
                                        required
                                    />
                                    <TextInput
                                        id="email"
                                        name="email"
                                        value={form.data.email}
                                        autoComplete="email"
                                        type={"email"}
                                        onChange={(e) =>
                                            form.setData(
                                                "email",
                                                e.target.value
                                            )
                                        }
                                    />
                                    <InputError message={form.errors.email} />
                                </div>

                                <div className={`col-span-2`}>
                                    <InputLabel
                                        htmlFor="confirm_email"
                                        value="Confirm Email"
                                        required
                                    />
                                    <TextInput
                                        id="confirm_email"
                                        name="confirm_email"
                                        value={form.data.confirm_email}
                                        autoComplete="confirm_email"
                                        type={"email"}
                                        onPaste={handleConfirmEmailPaste}
                                        onChange={(e) =>
                                            form.setData(
                                                "confirm_email",
                                                e.target.value
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.confirm_email}
                                    />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="city"
                                        value="City"
                                        required
                                    />
                                    <TextInput
                                        id="city"
                                        name="city"
                                        value={form.data.city}
                                        onChange={(e) =>
                                            form.setData("city", e.target.value)
                                        }
                                    />
                                    <InputError message={form.errors.city} />
                                </div>

                                <div className="col-span-2">
                                    <InputLabel
                                        htmlFor="country"
                                        value="Country"
                                        required
                                    />
                                    <CountryDropdown
                                        value={form.data.country}
                                        classes={selectClasses}
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
                                        classes={selectClasses}
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
                            </FormGrid>
                            <div className="mt-12">
                                <PrimaryButton
                                    disabled={processing}
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
                        className={"max-w-[350px] mx-auto hidden lg:flex"}
                        event={event}
                    />
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default ConnectWithHostPage;
