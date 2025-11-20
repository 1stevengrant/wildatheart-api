import React from "react";

import { Form } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import useRoute from "@/Hooks/useRoute";

interface UpdateNamesPageProps {
    user: {
        uuid: string;
        email: string;
        given_name: string | null;
        family_name: string | null;
    };
}

const UpdateNamesPage = ({ user }: UpdateNamesPageProps) => {
    const route = useRoute();

    return (
        <div className="min-h-screen bg-n900 flex items-center justify-center px-4 py-8">
            <div className="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
                <div className="mb-6 text-center">
                    <HeadingOne className="!text-n900">
                        Complete Your Profile
                    </HeadingOne>
                    <p className="mt-2 text-gray-600">
                        Please add your name to complete your Wild at Heart
                        profile.
                    </p>
                </div>

                <Form
                    method="put"
                    href={route("profile.update.names.submit", {
                        user: user.uuid,
                    })}
                >
                    {({ errors, processing }) => (
                        <div className="space-y-6">
                            <div>
                                <InputLabel
                                    htmlFor="given_name"
                                    value="First Name"
                                />
                                <TextInput
                                    id="given_name"
                                    name="given_name"
                                    type="text"
                                    className="mt-1 block w-full"
                                    defaultValue={user.given_name ?? ""}
                                    required
                                    autoFocus
                                />
                                {errors.given_name && (
                                    <InputError
                                        message={errors.given_name}
                                        className="mt-2"
                                    />
                                )}
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="family_name"
                                    value="Last Name"
                                />
                                <TextInput
                                    id="family_name"
                                    name="family_name"
                                    type="text"
                                    className="mt-1 block w-full"
                                    defaultValue={user.family_name ?? ""}
                                    required
                                />
                                {errors.family_name && (
                                    <InputError
                                        message={errors.family_name}
                                        className="mt-2"
                                    />
                                )}
                            </div>

                            <div className="pt-4">
                                <SecondaryButton
                                    type="submit"
                                    disabled={processing}
                                    className="w-full justify-center"
                                >
                                    {processing
                                        ? "Saving..."
                                        : "Update Profile"}
                                </SecondaryButton>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </div>
    );
};

export default UpdateNamesPage;
