import React, { useRef, useState } from "react";

import { Link, router, useForm } from "@inertiajs/react";
import { PhoneInput } from "react-international-phone";
import { isValidPhoneNumber } from "react-phone-number-input";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import TextInput from "@/Components/Forms/TextInput";
import { Spinner } from "@/Components/Ui/Spinner";
import { UserAvatar } from "@/Components/UserAvatar";
import { scrollToError } from "@/helpers/scrollToError";
import useRoute from "@/Hooks/useRoute";

const photoButtonClass = `px-4 py-2 border-2 border-white hover:border-p200 hover:text-p200 transition ease-in-out duration-100 font-semibold text-sm`;

export const UpdateProfileForm = ({
    user,
    className,
    redirectTo,
    buttonText,
}: any) => {
    const form = useForm({
        _method: "PUT",
        given_name: user.given_name ?? "",
        family_name: user.family_name ?? "",
        phone: user.phone ?? "",
        email: user.email ?? "",
        image: null as File | null,
        opted_into_marketing: Boolean(user.opted_into_marketing) ?? true,
    });

    const route = useRoute();
    const [photoPreview, setPhotoPreview] = useState<string | null>(null);
    const [confirmingPhotoDelete, setConfirmingPhotoDelete] = useState(false);
    const photoRef = useRef<HTMLInputElement>(null);

    const isDefaultAvatar = user.image?.startsWith("data:image/");

    const updateProfileInformation = (event: React.FormEvent) => {
        event.preventDefault();

        form.post(route("user.profile"), {
            preserveScroll: "errors",
            onSuccess: () => {
                clearPhotoFileInput();
                router.get(redirectTo);
            },
            onError: () => scrollToError(),
        });
    };

    const selectNewPhoto = () => {
        photoRef.current?.click();
    };

    const deletePhoto = () => {
        router.delete(route("user.profile-photo.destroy"), {
            preserveScroll: true,
            onSuccess: () => {
                setPhotoPreview(null);
                clearPhotoFileInput();
                setConfirmingPhotoDelete(false);
            },
        });
    };

    const clearPhotoFileInput = () => {
        if (photoRef.current?.value) {
            photoRef.current.value = "";
            form.setData("image", null);
        }
    };

    const updatePhotoPreview = () => {
        const photo = photoRef.current?.files?.[0];

        if (!photo) {
            return;
        }

        form.setData("image", photo);

        const reader = new FileReader();

        reader.onload = (e) => {
            setPhotoPreview(e.target?.result as string);
        };

        reader.readAsDataURL(photo);
    };

    return (
        <form
            onSubmit={updateProfileInformation}
            className={`mt-4 ${className}`}
        >
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel
                        htmlFor="given_name"
                        value="First Name"
                        required
                    />
                    <TextInput
                        id="given_name"
                        name="given_name"
                        value={form.data.given_name}
                        autoComplete="given_name"
                        isFocused={true}
                        onChange={(e) =>
                            form.setData("given_name", e.target.value)
                        }
                    />
                    <InputError message={form.errors.given_name} />
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
                        value={form.data.family_name}
                        autoComplete="family_name"
                        isFocused={true}
                        onChange={(e) =>
                            form.setData("family_name", e.target.value)
                        }
                    />
                    <InputError message={form.errors.family_name} />
                </div>

                <div className="col-span-2">
                    <InputLabel htmlFor="email" value="Email" required />
                    <div>{form.data.email}</div>
                    <InputError message={form.errors.email} />
                </div>

                <div className="col-span-2">
                    <InputLabel htmlFor="phone" value="Phone" required />
                    <PhoneInput
                        value={form.data.phone ?? ""}
                        defaultCountry={"us"}
                        onChange={(phone) =>
                            form.setData("phone", phone ? phone.toString() : "")
                        }
                    />
                    <InputError message={form.errors.phone} />
                    {!isValidPhoneNumber(form.data.phone) && (
                        <InputError
                            message={`Please enter a valid phone number.`}
                        />
                    )}
                </div>

                <div className="col-span-2">
                    <input
                        type="file"
                        className={"hidden"}
                        ref={photoRef}
                        onChange={updatePhotoPreview}
                    />

                    <InputLabel
                        htmlFor="image"
                        value="Profile Photo"
                        className={`mb-4`}
                    />

                    <div className="space-y-4">
                        {/* Photo Preview/Display */}
                        {photoPreview ? (
                            <div
                                className="w-[100px] h-[100px] rounded-full bg-cover bg-center"
                                style={{
                                    backgroundImage: `url('${photoPreview}')`,
                                }}
                            />
                        ) : user.image ? (
                            <UserAvatar
                                image={user.image}
                                className="w-[100px] h-[100px]"
                            />
                        ) : null}

                        {/* Buttons */}
                        <div className="flex flex-wrap gap-2">
                            {!confirmingPhotoDelete && (
                                <>
                                    {/* Photo selection button */}
                                    <button
                                        type="button"
                                        className={photoButtonClass}
                                        onClick={selectNewPhoto}
                                    >
                                        {photoPreview
                                            ? "Choose Different Photo"
                                            : user.image && !isDefaultAvatar
                                              ? "Change Photo"
                                              : "Add Photo"}
                                    </button>

                                    {/* Remove photo button - only show for non-default photos */}
                                    {(photoPreview ||
                                        (user.image && !isDefaultAvatar)) && (
                                        <button
                                            type="button"
                                            className={photoButtonClass}
                                            onClick={() => {
                                                if (photoPreview) {
                                                    setPhotoPreview(null);
                                                    clearPhotoFileInput();
                                                } else {
                                                    setConfirmingPhotoDelete(
                                                        true
                                                    );
                                                }
                                            }}
                                        >
                                            {photoPreview
                                                ? "Remove New Photo"
                                                : "Remove Photo"}
                                        </button>
                                    )}
                                </>
                            )}

                            {/* Photo deletion confirmation */}
                            {confirmingPhotoDelete && (
                                <>
                                    <div className="w-full text-sm text-red-600 mb-2">
                                        Are you sure you want to remove your
                                        photo?
                                    </div>
                                    <button
                                        type="button"
                                        className={`${photoButtonClass}`}
                                        onClick={deletePhoto}
                                    >
                                        Yes, Remove Photo
                                    </button>
                                    <button
                                        type="button"
                                        className={photoButtonClass}
                                        onClick={() =>
                                            setConfirmingPhotoDelete(false)
                                        }
                                    >
                                        Cancel
                                    </button>
                                </>
                            )}
                        </div>
                    </div>

                    <InputError message={form.errors.image} />
                </div>

                <div className="col-span-2">
                    <InputLabel
                        htmlFor="emailUpdates"
                        value="Updates & Resources"
                        className={`mb-2`}
                    />
                    <SwitchComponent
                        checked={Boolean(form.data.opted_into_marketing)}
                        onChange={() =>
                            form.setData(
                                "opted_into_marketing",
                                !form.data.opted_into_marketing
                            )
                        }
                    />
                    <Instruction>
                        {form.data.opted_into_marketing
                            ? "Yes! Send"
                            : "No! Don't send"}{" "}
                        me updates and resources from Wild at Heart.
                    </Instruction>
                </div>
            </div>

            <div className="mt-4">
                <PrimaryButton
                    className={"w-full"}
                    disabled={
                        form.processing || !isValidPhoneNumber(form.data.phone)
                    }
                >
                    {form.processing ? (
                        <>
                            <Spinner className="mr-2 h-4 w-4" />
                            Saving Profile...
                        </>
                    ) : (
                        buttonText ?? "Save"
                    )}
                </PrimaryButton>
                <Link
                    href={route("home")}
                    className="block py-3 text-center text-sm text-n40"
                >
                    Cancel
                </Link>
            </div>
        </form>
    );
};
