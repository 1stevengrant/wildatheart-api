import React, { useState } from "react";

import { ArrowRightEndOnRectangleIcon } from "@heroicons/react/24/outline";

import { LogoutModal } from "@/Components/LogoutModal";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import profileNavItemClasses from "@/helpers/profileNavItemClasses";

export const LogoutButtonWithModal: React.FC = () => {
    const [open, setOpen] = useState(false);

    return (
        <>
            <button
                onClick={() => setOpen(true)}
                className={`${profileNavItemClasses}`}
            >
                <ArrowRightEndOnRectangleIcon className={defaultIconSize} />
                <p>Logout</p>
            </button>
            <LogoutModal open={open} setOpen={setOpen} />
        </>
    );
};
