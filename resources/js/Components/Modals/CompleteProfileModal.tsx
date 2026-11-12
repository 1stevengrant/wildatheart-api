import { FormEventHandler, useState } from "react";

import { Dialog } from "@headlessui/react";
import { useForm, usePage } from "@inertiajs/react";
import { PhoneInput } from "react-international-phone";
import { isValidPhoneNumber } from "react-phone-number-input";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { FormFieldsWrapper } from "@/Components/Forms/FormFieldsWrapper";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";
import { Modal } from "@/Components/Ui/Modal";
import { ModalActions } from "@/Components/Ui/ModalActions";
import { scrollToError } from "@/helpers/scrollToError";

export const CompleteProfileModal = ({
    open,
    setOpen,
    onSuccess,
    title = "Complete Your Profile",
    description = "Please update the required fields below.",
}: {
    open: boolean;
    setOpen: (open: boolean) => void;
    onSuccess?: () => void;
    title?: string;
    description?: string;
}) => {
    const { auth } = usePage().props;
    const [isSubmitting, setIsSubmitting] = useState(false);

    const { data, setData, post, errors } = useForm<{
        _method: string;
        given_name: string;
        family_name: string;
        phone: string;
        email: string;
        opted_into_marketing: boolean;
    }>({
        _method: "PUT",
        given_name: auth.user?.given_name ?? "",
        family_name: auth.user?.family_name ?? "",
        phone: auth.user?.phone ?? "",
        email: auth.user?.email ?? "",
        opted_into_marketing: Boolean(auth.user?.opted_into_marketing),
    });

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (isSubmitting) return;

        setIsSubmitting(true);

        post(route("user.profile"), {
            preserveScroll: true,
            onSuccess: () => {
                setIsSubmitting(false);
                setOpen(false);
                onSuccess?.();
            },
            onError: () => {
                setIsSubmitting(false);
                scrollToError();
            },
        });
    };

    return (
        <Modal open={open} setOpen={() => {}}>
            <form onSubmit={submit}>
                <div>
                    <Dialog.Title
                        as="h3"
                        className="font-semibold leading-6 text-lg"
                    >
                        {title}
                    </Dialog.Title>
                    <p className="mt-2 text-sm text-n40">{description}</p>
                    <FormFieldsWrapper>
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
                                className="mt-1 block w-full"
                                autoComplete="given-name"
                                isFocused={true}
                                disabled={isSubmitting}
                                onChange={(e) =>
                                    setData("given_name", e.target.value)
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
                                className="mt-1 block w-full"
                                autoComplete="family-name"
                                disabled={isSubmitting}
                                onChange={(e) =>
                                    setData("family_name", e.target.value)
                                }
                            />
                            <InputError message={errors.family_name} />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="phone"
                                value="Phone"
                                required
                            />
                            <PhoneInput
                                value={data.phone ?? ""}
                                defaultCountry={"us"}
                                disabled={isSubmitting}
                                onChange={(phone) =>
                                    setData(
                                        "phone",
                                        phone ? phone.toString() : ""
                                    )
                                }
                            />
                            <InputError message={errors.phone} />
                            {data.phone && !isValidPhoneNumber(data.phone) && (
                                <InputError message="Please enter a valid phone number." />
                            )}
                        </div>
                    </FormFieldsWrapper>
                </div>
                <ModalActions>
                    <PrimaryButton
                        type="submit"
                        disabled={
                            isSubmitting ||
                            !data.given_name.trim() ||
                            !data.family_name.trim() ||
                            !isValidPhoneNumber(data.phone)
                        }
                    >
                        {isSubmitting ? "Saving..." : "Save & Continue"}
                    </PrimaryButton>
                </ModalActions>
            </form>
        </Modal>
    );
};
