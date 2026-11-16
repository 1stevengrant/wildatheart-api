import React from "react";

import { Link, useForm } from "@inertiajs/react";
import { isValidPhoneNumber } from "libphonenumber-js";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import {
    MailingAddress,
    MailingAddressFields,
} from "@/Components/Forms/MailingAddressFields";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { scrollToError } from "@/helpers/scrollToError";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const SendGiftPage = () => {
    const { auth, event } = useTypedPage<{
        event: App.Data.EventData;
    }>().props;

    const phoneNumber = auth.user?.phone?.replace(/\s/g, "") ?? "";

    const { data, setData, post, processing, errors } = useForm<MailingAddress>(
        {
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
        }
    );

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
                            <MailingAddressFields
                                data={data}
                                setData={setData}
                                errors={errors}
                            />

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
