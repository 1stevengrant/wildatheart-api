import React, { useState } from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Head, Link, router } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventCreateProgressMobile } from "@/Components/Events/EventCreateProgressMobile";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { EmptyLayoutHeader } from "@/Components/Layout/Elements/EmptyLayoutHeader";
import { EmptyLayoutWrapper } from "@/Components/Layout/Elements/EmptyLayoutWrapper";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";

const HostAgreementPage = () => {
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
                    <PageContainer>
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
