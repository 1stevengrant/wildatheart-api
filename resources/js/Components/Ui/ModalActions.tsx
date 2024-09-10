import React from "react";

export const ModalActions = ({ children }: { children: React.ReactNode }) => {
    return (
        <div className="mt-5 flex items-center justify-end space-x-6 sm:mt-6">
            {children}
        </div>
    );
};
