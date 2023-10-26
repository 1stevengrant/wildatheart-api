import React from "react";
import Modal from "@/Components/Ui/Modal";
import ModalActions from "@/Components/Ui/ModalActions";
import SecondaryButton from "@/Components/Buttons/SecondaryButton";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {EyeSlashIcon, XMarkIcon} from "@heroicons/react/24/solid";
import ModalHeading from "@/Components/Type/ModalHeading";
import {useForm} from "@inertiajs/react";
import {Fire} from "@/types/Fire";

interface PublishFireModalProps {
    open: boolean
    setOpen: (open: boolean) => void,
    fire: Fire
}

const PublishFireModal = ({open, setOpen, fire}: PublishFireModalProps) => {
    const form = useForm({
        approved_at: new Date().toISOString()
    });

    const publishFire = async () => {
        form.put(route('toggle-campfire-approved-at', [fire]), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                setOpen(false)
            }
        })
    }

    return (
        <>
            <button className="flex items-center space-x-2 hover:text-orange"
                    onClick={() => setOpen(true)}>
                <EyeSlashIcon className={`h-6 w-6`}/>
                <span className="hidden md:inline">
                    Publish Fire
                </span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={'flex justify-end'}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={'w-6 h-6 text-white'}/>
                    </button>
                </div>
                <ModalHeading>Publish {fire.name} Fire?</ModalHeading>
                <p>Are you sure you want to publish this Fire and allow connections to make contact?</p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
                    <PrimaryButton onClick={publishFire}>
                        Publish
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    )
}

export default PublishFireModal
