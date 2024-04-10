import React, { useState } from "react";

import { ArrowLeftOnRectangleIcon } from "@heroicons/react/24/outline";

import LogoutModal from "@/Components/LogoutModal";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import profileNavItemClasses from "@/helpers/profileNavItemClasses";

const LogoutButtonWithModal: React.FC = () => {
    const [open, setOpen] = useState(false);

    return (
        <>
            <button
                onClick={() => setOpen(true)}
                className={`${profileNavItemClasses}`}
            >
                <ArrowLeftOnRectangleIcon className={defaultIconSize} />
                <p>Logout</p>
            </button>
            <LogoutModal open={open} setOpen={setOpen} />
        </>
    );
};

export default LogoutButtonWithModal;
