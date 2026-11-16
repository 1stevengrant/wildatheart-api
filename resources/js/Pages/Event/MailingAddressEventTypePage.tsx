import React from "react";

import { ArrowLeftIcon, ArrowRightIcon } from "@heroicons/react/20/solid";
import { Link, useForm } from "@inertiajs/react";
import { isValidPhoneNumber } from "libphonenumber-js";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import {
    MailingAddress,
    MailingAddressFields,
} from "@/Components/Forms/MailingAddressFields";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { scrollToError } from "@/helpers/scrollToError";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const MailingAddressEventTypePage = () => {
    const page = useTypedPage();
    const { auth } = page.props;
    const { address } = page.props as any;

    const eventType = page.props.eventType as any;
    const phoneNumber = auth.user?.phone?.replace(/\s/g, "") ?? "";

    const { data, setData, post, processing, errors } = useForm<MailingAddress>(
        {
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
        }
    );

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
                            <MailingAddressFields
                                data={data}
                                setData={setData}
                                errors={errors}
                            />
                        </form>
                    </PageContainer>
                </EmptyLayoutBody>
            </SidebarLayout>
        </EmptyLayout>
    );
};

export default MailingAddressEventTypePage;
