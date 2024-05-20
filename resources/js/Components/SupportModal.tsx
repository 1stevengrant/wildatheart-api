import React, { FormEventHandler } from "react";

import { Dialog } from "@headlessui/react";
import { useForm, usePage } from "@inertiajs/react";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import SecondaryButton from "@/Components/Buttons/SecondaryButton";
import FormFieldsWrapper from "@/Components/Forms/FormFieldsWrapper";
import InputError from "@/Components/Forms/InputError";
import InputLabel from "@/Components/Forms/InputLabel";
import TextArea from "@/Components/Forms/TextArea";
import TextInput from "@/Components/Forms/TextInput";
import Modal from "@/Components/Ui/Modal";
import ModalActions from "@/Components/Ui/ModalActions";

interface SupportModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
}

export const SupportModal = ({ open, setOpen }: SupportModalProps) => {
    const { auth } = usePage<any>().props;
    const { data, setData, post, errors, reset } = useForm({
        name: auth.user
            ? `${auth.user.given_name} ${auth.user.family_name}`
            : "",
        email: auth.user ? auth.user.email : "",
        subject: "",
        description: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("support.store"), {
            preserveScroll: true,
            onSuccess: () => handleSuccess(),
        });
    };

    const handleSuccess = () => {
        setOpen(false);
        reset();
    };

    return (
        <Modal open={open} setOpen={setOpen}>
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
                        className=""
                        onClick={() => setOpen(false)}
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton type="submit">Submit</PrimaryButton>
                </ModalActions>
            </form>
        </Modal>
    );
};
