import React from "react";
import Modal from "@/Components/Ui/Modal";
import ModalActions from "@/Components/Ui/ModalActions";
import SecondaryButton from "@/Components/Buttons/SecondaryButton";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {EyeIcon, XMarkIcon} from "@heroicons/react/24/solid";
import ModalHeading from "@/Components/Type/ModalHeading";
import {useForm} from "@inertiajs/react";
import {Event} from "@/types/Event";

interface EventPublishModalProps {
    open: boolean
    setOpen: (open: boolean) => void,
    event: Event
}

const EventPublishModal = ({open, setOpen, event}: EventPublishModalProps) => {
    const form = useForm({
        approved_at: new Date().toISOString()
    });

    const publishEvent = async () => {
        form.put(route('toggle-event-approved-at', [event]), {
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
                <EyeIcon className="h-6 w-6" />
                <span className="hidden md:inline">
                    Publish Event
                </span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={'flex justify-end'}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={'w-6 h-6 text-white'}/>
                    </button>
                </div>
                <ModalHeading>Publish {event.name} Event?</ModalHeading>
                <p>Are you sure you want to publish this Event and allow connections to make contact?</p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
                    <PrimaryButton onClick={publishEvent}>
                        Publish
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    )
}

export default EventPublishModal
