import { FormEventHandler } from "react";

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
    const { auth } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm<{
        given_name: string;
        family_name: string;
        email: string;
        subject: string;
        description: string;
    }>({
        given_name: auth.user?.given_name ?? "",
        family_name: auth.user?.family_name ?? "",
        email: auth.user ? auth.user.email : "",
        subject: "",
        description: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (processing) {
            return;
        }

        post(route("support.store"), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setOpen(false);
            },
            onError: () => scrollToError(),
        });
    };

    const handleCancel = () => {
        if (!processing) {
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
                                disabled={processing}
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
                                disabled={processing}
                                onChange={(e) =>
                                    setData("family_name", e.target.value)
                                }
                            />
                            <InputError message={errors.family_name} />
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
                                disabled={processing}
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
                                disabled={processing}
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
                                disabled={processing}
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
                        disabled={processing}
                        onClick={handleCancel}
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit" disabled={processing}>
                        {processing ? "Submitting..." : "Submit"}
                    </PrimaryButton>
                </ModalActions>
            </form>
        </Modal>
    );
};
