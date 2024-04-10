import React, { FC } from "react";

interface ModalActionsProps {
    children: React.ReactNode;
}

const ModalActions: FC<ModalActionsProps> = ({ children }) => {
    return (
        <div className="mt-5 flex items-center justify-end space-x-6 sm:mt-6">
            {children}
        </div>
    );
};

export default ModalActions;
