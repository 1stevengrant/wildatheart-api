import React from "react";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";
import AppLayout from "@/Layouts/AppLayout";
import primaryButtonClasses from "@/helpers/primaryButtonClasses";

const Resources = () => {
    const pageTitle = 'Resources';
    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                <h2 className={`font-semibold text-lg mb-4`}>Firestarter Kit</h2>
                <p>Access the electronic version of the Firestarter Kit here:</p>
                <div className={`mt-4`}>
                    <a href={`https://wildatheart.org/firestarter`} className={`${primaryButtonClasses} inline-block`}>
                        Electronic Firestarter Kit
                    </a>
                </div>

                <h2 className={`font-semibold text-lg my-4`}>Order Additional Books</h2>
                <p>If you need more copies of Wild at Heart or Becoming a King, you can purchase more (at cost)
                    here:</p>
                <div className={`mt-4`}>
                    <a href={`https://shop.wildatheart.org`} className={`${primaryButtonClasses} inline-block`}>
                        Order Books
                    </a>
                </div>
            </MyProfileWrapper>
        </AppLayout>
    )
}

export default Resources;
