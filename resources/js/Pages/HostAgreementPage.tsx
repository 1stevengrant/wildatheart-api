import React, { useState } from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Head, Link, router, usePage } from "@inertiajs/react";
import ReactPlayer from "react-player";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventCreateProgressMobile } from "@/Components/Events/EventCreateProgressMobile";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { EmptyLayoutHeader } from "@/Components/Layout/Elements/EmptyLayoutHeader";
import { EmptyLayoutWrapper } from "@/Components/Layout/Elements/EmptyLayoutWrapper";
import { Image } from "@/Components/Media/Image";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { Heading } from "@/Components/Type/Heading";
import { Paragraph } from "@/Components/Type/Paragraph";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";

const HostAgreementPage = () => {
    const { hostAgreement } = usePage().props as any;
    const handleAgree = (event: React.FormEvent) => {
        event.preventDefault();
        router.post(route("host.agreement.store"), {
            preserveScroll: true,
        });
    };

    const [hasAgreed, setHasAgreed] = useState(false);

    return (
        <>
            <Head title={`Host Agreement`} />
            <EmptyLayoutWrapper>
                <EmptyLayoutHeader>
                    <EventCreateProgressMobile />
                </EmptyLayoutHeader>
                <EmptyLayoutBody>
                    <PageContainer className={`pt-12`}>
                        {hostAgreement.map((item: any, index: number) => {
                            switch (item.type) {
                                case "heading":
                                    return <Heading item={item} key={index} />;
                                case "paragraph":
                                    return (
                                        <Paragraph item={item} key={index} />
                                    );
                                case "set":
                                    if (item.attrs.values.type === "video") {
                                        return (
                                            <ReactPlayer
                                                url={item.attrs.values.video}
                                                key={index}
                                                controls={true}
                                            />
                                        );
                                    } else if (
                                        item.attrs.values.type === "image"
                                    ) {
                                        return (
                                            <Image
                                                key={index}
                                                url={item.attrs.values.image}
                                            />
                                        );
                                    }
                                    return null;
                                default:
                                    return null;
                            }
                        })}
                        <label
                            className={`flex items-center space-x-2 hover:cursor-pointer`}
                        >
                            <SwitchComponent
                                checked={hasAgreed}
                                onChange={() => setHasAgreed(!hasAgreed)}
                            />
                            <p>I agree with the Wild at Heart Host Agreement</p>
                        </label>
                    </PageContainer>
                </EmptyLayoutBody>
                <EmptyLayoutFooter>
                    <Link
                        href={route("home")}
                        className={circularButtonClasses}
                    >
                        <ArrowLeftIcon className={`w-4 h-4`} />
                    </Link>
                    <PrimaryButton disabled={!hasAgreed} onClick={handleAgree}>
                        Agree and continue
                        <ArrowRightIcon className={`w-4 h-4 ml-2`} />
                    </PrimaryButton>
                </EmptyLayoutFooter>
            </EmptyLayoutWrapper>
        </>
    );
};

export default HostAgreementPage;
