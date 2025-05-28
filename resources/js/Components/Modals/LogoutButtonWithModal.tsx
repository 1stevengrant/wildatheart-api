import { useState } from "react";

import { ArrowRightEndOnRectangleIcon } from "@heroicons/react/24/outline";

import { LogoutModal } from "@/Components/Modals/LogoutModal";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import { profileNavItemClasses } from "@/helpers/profileNavItemClasses";

export const LogoutButtonWithModal = () => {
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
