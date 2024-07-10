import React, {useRef, useState} from "react";
import {Link, router, useForm} from "@inertiajs/react";
import useRoute from "@/Hooks/useRoute";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import Instruction from "@/Components/Forms/Instruction";
import InputLabel from "@/Components/Forms/InputLabel";
import {PhoneInput} from "react-international-phone";
import TextInput from "@/Components/Forms/TextInput";
import InputError from "@/Components/Forms/InputError";
import {isValidPhoneNumber} from "react-phone-number-input";
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";

const photoButtonClass = `px-4 py-2 border-2 border-white hover:border-orange hover:text-orange transition ease-in-out duration-100 font-semibold text-sm`

const UpdateProfileForm = ({
                               user,
                               className,
                               redirectTo,
                               buttonText,
                           }: any) => {
    const form = useForm({
        _method: 'PUT',
        given_name: user.given_name ?? '',
        family_name: user.family_name ?? '',
        phone: user.phone ?? '',
        email: user.email ?? '',
        image: null as File | null,
        opted_into_marketing: user.opted_into_marketing ?? true,
    });

    const route = useRoute();
    const [photoPreview, setPhotoPreview] = useState<string | null>(null);
    const photoRef = useRef<HTMLInputElement>(null);

    const updateProfileInformation = (event: React.FormEvent) => {
        event.preventDefault();
        form.post(route('user.profile'), {
            preserveScroll: true,
            onSuccess: () => {
                clearPhotoFileInput()
                router.get(redirectTo);
            },
        });
    }

    const selectNewPhoto = () => {
        photoRef.current?.click();
    }

    const deletePhoto = () => {
        router.delete(route('user.profile-photo.destroy'), {
            preserveScroll: true,
            onSuccess: () => {
                setPhotoPreview(null);
                clearPhotoFileInput();
            },
        });
    }

    const clearPhotoFileInput = () => {
        if (photoRef.current?.value) {
            photoRef.current.value = '';
            form.setData('image', null);
        }
    }

    const updatePhotoPreview = () => {
        const photo = photoRef.current?.files?.[0];

        if (!photo) {
            return;
        }

        form.setData('image', photo);

        const reader = new FileReader();

        reader.onload = e => {
            setPhotoPreview(e.target?.result as string);
        };

        reader.readAsDataURL(photo);
    }

    return (
        <form onSubmit={updateProfileInformation} className={`mt-4 ${className}`}>
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel
                        htmlFor="given_name"
                        value="First Name"
                        required/>
                    <TextInput
                        id="given_name"
                        name="given_name"
                        value={form.data.given_name}
                        autoComplete="given_name"
                        isFocused={true}
                        onChange={(e) => form.setData('given_name', e.target.value)}
                    />
                    <InputError message={form.errors.given_name}/>
                </div>

                <div>
                    <InputLabel
                        htmlFor="family_name"
                        value="Last Name"
                        required/>
                    <TextInput
                        id="family_name"
                        name="family_name"
                        value={form.data.family_name}
                        autoComplete="family_name"
                        isFocused={true}
                        onChange={(e) => form.setData('family_name', e.target.value)}
                    />
                    <InputError message={form.errors.family_name}/>
                </div>

                <div className="col-span-2">
                    <InputLabel
                        htmlFor="email"
                        value="Email"
                        required/>
                    <div>{form.data.email}</div>
                    <InputError message={form.errors.email}/>
                </div>

                <div className="col-span-2">
                    <InputLabel
                        htmlFor="phone"
                        value="Phone"
                        required/>
                    <PhoneInput
                        value={form.data.phone ?? ''}
                        defaultCountry={'us'}
                        onChange={phone => form.setData('phone', phone ? phone.toString() : '')}
                    />
                    <InputError message={form.errors.phone}/>
                    {!isValidPhoneNumber(form.data.phone) && (
                      <InputError message={`Please enter a valid phone number.`}/>
                    )}
                </div>

                <div className="col-span-2">
                    <input type="file"
                           className={'hidden'}
                           ref={photoRef}
                           onChange={updatePhotoPreview}/>

                    <InputLabel htmlFor="image" value="Profile Photo"/>
                    {photoPreview ? (
                            <div className="mt-2 inline-flex justify-center space-x-4">
                                <div
                                    className="block rounded-full bg-cover bg-center bg-no-repeat w-[134px] h-[134px]"
                                    style={{backgroundImage: `url('${photoPreview}')`}}
                                />
                                <div className={`flex flex-col justify-center`}>
                                    <button type="button"
                                            className={`${photoButtonClass}`}
                                            onClick={deletePhoto}>
                                        Remove Photo
                                    </button>
                                </div>
                            </div>) :
                        (
                            <>
                                {user.image ? (
                                    <div className={`inline-flex justify-center space-x-4`}>
                                        <UserAvatar image={user.image}
                                                    className={'w-[134px] h-[134px]'}
                                                    width={134}/>
                                        <div className={`flex flex-col justify-center`}>
                                            <button type="button"
                                                    className={`${photoButtonClass}`}
                                                    onClick={deletePhoto}>
                                                Remove Photo
                                            </button>
                                        </div>
                                    </div>

                                ) : (
                                    <>
                                        <div className={'inline-flex justify-center space-x-4'}>
                                            <UserCircleIcon className={'w-24 h-24 flex-shrink-0'}/>
                                            <div className={`flex flex-col justify-center`}>
                                                <button type="button"
                                                        className={`${photoButtonClass}`}
                                                        onClick={selectNewPhoto}>
                                                    Select New Photo
                                                </button>
                                            </div>
                                        </div>

                                    </>
                                )}
                            </>
                        )
                    }
                    <InputError message={form.errors.image}/>
                </div>

                <div className="col-span-2">
                    <InputLabel
                        htmlFor="emailUpdates"
                        value="Updates & Resources"/>
                    <div
                        className="flex flex-col space-y-4 md:space-x-4 md:space-y-0 md:flex-row md:items-center">
                        <div className="flex items-center rounded-full bg-form">
                            <button type={'button'}
                                    onClick={() => form.setData('opted_into_marketing', true)}
                                    className={`w-1/2 px-10 py-3 text-sm font-semibold ${form.data.opted_into_marketing ? 'border border-white rounded-l-full' : ''}`}>
                                Yes
                            </button>
                            <button type={'button'}
                                    onClick={() => form.setData('opted_into_marketing', false)}
                                    className={`w-1/2 px-10 py-3 text-sm font-semibold ${!form.data.opted_into_marketing ? 'border border-white rounded-r-full' : ''}`}>
                                No
                            </button>
                        </div>
                    </div>
                    <Instruction>
                        Send me updates and resources from Wild at Heart.
                    </Instruction>
                </div>
            </div>

            <div className="mt-4">
                <PrimaryButton className={'w-full'}
                               disabled={form.processing || !isValidPhoneNumber(form.data.phone)}>
                    {buttonText ?? 'Save'}
                </PrimaryButton>
                <Link href={route('home')}
                      className="block py-3 text-center text-sm text-grey-200">
                    Cancel
                </Link>
            </div>

        </form>
    )
};

export default UpdateProfileForm;
