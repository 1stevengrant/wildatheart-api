import React from "react";
import { EmptyLayout }from "@/Layouts/EmptyLayout";
import TwoColumnWrapper from "@/Layouts/TwoColumnWrapper";
import LeftColumn from "@/Layouts/LeftColumn";
import RightColumn from "@/Layouts/RightColumn";
import HeadingOne from "@/Components/Type/HeadingOne";
import {useForm, usePage} from "@inertiajs/react";
import {PageProps} from "@/types";
import useRoute from "@/Hooks/useRoute";
import {Event} from "@/types/Event";
import axios from "axios";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";

const ConfirmEmail = () => {
    const [reactNotifications, setReactNotifications] = useRecoilState(reactNotificationAtom);
    const page = usePage();
    const {auth} = page.props as unknown as PageProps;
    const route = useRoute();
    const event = page.props.event as Event;

    const resendEmail = () => {
        axios.post(route('resend-confirmation-email', event))
            .then((response) => {
                const newNotification = response.data;
                setReactNotifications([...reactNotifications, newNotification]);
            })
            .catch((error) => {
                const newErrorNotification = {
                    message: error.response.data.message,
                    type: 'error',
                };
                setReactNotifications([...reactNotifications, newErrorNotification]);
            });
    }


    return (
        <EmptyLayout title={'Confirm Email'}>
            <TwoColumnWrapper className={`justify-center flex flex-col md:flex-row`}>
                <LeftColumn className={``}>
                    <div className={`md:max-w-[400px] mx-auto`}>
                        <HeadingOne>Great, one last step {auth.user?.given_name ?? ''}.</HeadingOne>

                        <p className="my-6 text-grey">
                            Please click on the link in the email we just sent you to
                            finalize your listing.
                        </p>

                        <div className={`mt-8 text-center`}>
                            <button
                                className={`border-b border-b-white text-sm text-white hover:border-transparent`}
                                onClick={resendEmail}
                            >
                                Resend confirmation email
                            </button>
                        </div>
                    </div>

                </LeftColumn>
                <RightColumn className={``}>
                    <div className={`mx-auto`}>
                        <img src="/img/fires-gift-image-v2.webp"
                             className={`max-h-[700px] hidden md:block`}
                             alt=""/>
                    </div>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
}

export default ConfirmEmail;
