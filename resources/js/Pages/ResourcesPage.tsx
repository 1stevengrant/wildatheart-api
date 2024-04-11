import React from "react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import secondaryButtonClasses from "@/helpers/secondaryButtonClasses";
import { AppLayout } from "@/Layouts/AppLayout";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";

const ResourcesPage = () => {
    const pageTitle = "Resources";
    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                <h2 className={`font-semibold text-lg mb-4`}>
                    Firestarter Kit
                </h2>
                <p>
                    Access the electronic version of the Firestarter Kit here:
                </p>
                <div className={`mt-4`}>
                    <a
                        href={`https://wildatheart.org/firestarter`}
                        className={`${primaryButtonClasses} inline-block`}
                    >
                        Electronic Firestarter Kit
                    </a>
                </div>

                <h2 className={`font-semibold text-lg my-4`}>
                    Order Additional Books
                </h2>
                <p>
                    Need more copies of Wild at Heart or Becoming a King to give
                    to your Fire attendees? Order more copies here and a coupon
                    will be applied at checkout:
                </p>
                <div className={`mt-4`}>
                    <a
                        href={`https://shop.wildatheart.org/discount/RCFH23RNRE8J`}
                        className={`${primaryButtonClasses} inline-block`}
                    >
                        Order Books
                    </a>
                </div>
                <p className={`mt-4`}>
                    If you'd like to donate to help cover the cost of additional
                    books, click here:
                </p>
                <div className={`mt-4`}>
                    <a
                        href={`https://my.wildatheart.org/`}
                        className={`${secondaryButtonClasses} inline-block`}
                    >
                        Donate
                    </a>
                </div>
            </MyProfileWrapper>
        </AppLayout>
    );
};

export default ResourcesPage;
