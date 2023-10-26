import React from "react";
import Modal from "@/Components/Ui/Modal";
import ModalActions from "@/Components/Ui/ModalActions";
import SecondaryButton from "@/Components/Buttons/SecondaryButton";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {TrashIcon} from "@heroicons/react/24/outline";
import {XMarkIcon} from "@heroicons/react/24/solid";
import ModalHeading from "@/Components/Type/ModalHeading";
import {useForm} from "@inertiajs/react";
import {Fire} from "@/types/Fire";

interface DeleteFireModalProps {
    open: boolean
    setOpen: (open: boolean) => void,
    fire: Fire
}

const DeleteFireModal = ({open, setOpen, fire}: DeleteFireModalProps) => {
    const form = useForm({});
    const deleteFire = async () => {
        form.delete(route('delete.fire', [fire]), {
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
                <TrashIcon className="h-6 w-6"/>
                <span className="hidden md:inline">
                    Delete Fire
                </span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={'flex justify-end'}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={'w-6 h-6 text-white'}/>
                    </button>
                </div>
                <ModalHeading>Delete Fire?</ModalHeading>
                <p>Are you sure you want to delete this Fire?</p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
                    <PrimaryButton onClick={deleteFire}>Delete</PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    )
}

export default DeleteFireModal
