import { ComponentType, ReactNode, SVGProps } from "react";

import { XMarkIcon } from "@heroicons/react/24/solid";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { ModalHeading } from "@/Components/Type/ModalHeading";
import { Modal } from "@/Components/Ui/Modal";
import { ModalActions } from "@/Components/Ui/ModalActions";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import {
    eventAdminActionButtonClass,
    eventAdminActionButtonTextClass,
} from "@/helpers/eventAdminActionButtonClass";

export const EventAdminActionModal = ({
    open,
    setOpen,
    icon: Icon,
    triggerLabel,
    heading,
    children,
    confirmLabel,
    onConfirm,
    processing,
}: {
    open: boolean;
    setOpen: (open: boolean) => void;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    triggerLabel: string;
    heading: ReactNode;
    children: ReactNode;
    confirmLabel: string;
    onConfirm: () => void;
    processing: boolean;
}) => {
    return (
        <>
            <button
                className={eventAdminActionButtonClass}
                onClick={() => setOpen(true)}
            >
                <Icon className={defaultIconSize} />
                <span className={eventAdminActionButtonTextClass}>
                    {triggerLabel}
                </span>
            </button>

            <Modal open={open} setOpen={setOpen}>
                <div className="flex justify-end">
                    <button
                        onClick={() => setOpen(false)}
                        aria-label="Close modal"
                    >
                        <XMarkIcon
                            className={`${defaultIconSize} text-white`}
                        />
                    </button>
                </div>
                <ModalHeading>{heading}</ModalHeading>
                <p>{children}</p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton onClick={onConfirm} disabled={processing}>
                        {confirmLabel}
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
