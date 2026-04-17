import { FormEventHandler, useState } from "react";

import { Dialog } from "@headlessui/react";
import { useForm, usePage } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { FormFieldsWrapper } from "@/Components/Forms/FormFieldsWrapper";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import TextArea from "@/Components/Forms/TextArea";
import TextInput from "@/Components/Forms/TextInput";
import { Modal } from "@/Components/Ui/Modal";
import { ModalActions } from "@/Components/Ui/ModalActions";
import { scrollToError } from "@/helpers/scrollToError";

export const SupportModal = ({
    open,
    setOpen,
}: {
    open: boolean;
    setOpen: (open: boolean) => void;
}) => {
    const { auth } = usePage<any>().props;
    const [isSubmitting, setIsSubmitting] = useState(false);

    const { data, setData, post, errors, reset } = useForm<{
        name: string;
        email: string;
        subject: string;
        description: string;
    }>({
        name: auth.user
            ? `${auth.user.given_name} ${auth.user.family_name}`
            : "",
        email: auth.user ? auth.user.email : "",
        subject: "",
        description: "",
    });

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (isSubmitting) return;

        setIsSubmitting(true);

        post(route("support.store"), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setIsSubmitting(false);
                setOpen(false);
            },
            onError: () => {
                setIsSubmitting(false);
                scrollToError();
            },
        });
    };

    const handleCancel = () => {
        if (!isSubmitting) {
            setOpen(false);
        }
    };

    return (
        <Modal open={open} setOpen={handleCancel}>
            <form onSubmit={submit}>
                <div>
                    <Dialog.Title as="h3" className="font-semibold leading-6">
                        Report an Issue or Provide Feedback
                    </Dialog.Title>
                    <FormFieldsWrapper>
                        <div>
                            <InputLabel htmlFor="name" value="Name" required />
                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                className="mt-1 block w-full"
                                autoComplete="name"
                                isFocused={true}
                                disabled={isSubmitting}
                                onChange={(e) =>
                                    setData("name", e.target.value)
                                }
                            />
                            <InputError message={errors.name} />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="email"
                                value="Email"
                                required
                            />
                            <TextInput
                                id="email"
                                name="email"
                                value={data.email}
                                className="mt-1 block w-full"
                                autoComplete="email"
                                disabled={isSubmitting}
                                onChange={(e) =>
                                    setData("email", e.target.value)
                                }
                            />
                            <InputError message={errors.email} />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="subject"
                                value="Subject"
                                required
                            />
                            <TextInput
                                id="subject"
                                name="subject"
                                value={data.subject}
                                className="mt-1 block w-full"
                                autoComplete="subject"
                                disabled={isSubmitting}
                                onChange={(e) =>
                                    setData("subject", e.target.value)
                                }
                            />
                            <InputError message={errors.subject} />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="description"
                                value="Description"
                                required
                            />
                            <TextArea
                                id="description"
                                name="description"
                                value={data.description}
                                disabled={isSubmitting}
                                onChange={(e) =>
                                    setData("description", e.target.value)
                                }
                            />
                            <InputError message={errors.description} />
                        </div>
                    </FormFieldsWrapper>
                </div>
                <ModalActions>
                    <SecondaryButton
                        type="button"
                        disabled={isSubmitting}
                        onClick={handleCancel}
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit" disabled={isSubmitting}>
                        {isSubmitting ? "Submitting..." : "Submit"}
                    </PrimaryButton>
                </ModalActions>
            </form>
        </Modal>
    );
};
