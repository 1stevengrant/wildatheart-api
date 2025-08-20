import React, { useState } from "react";

export const useEventModal = () => {
    const [confirmModalOpen, setConfirmModalOpen] = useState(false);

    const openConfirmModal = (e?: React.FormEvent) => {
        e?.preventDefault();
        setConfirmModalOpen(true);
    };

    const closeConfirmModal = () => {
        setConfirmModalOpen(false);
    };

    return {
        confirmModalOpen,
        setConfirmModalOpen,
        openConfirmModal,
        closeConfirmModal,
    };
};
