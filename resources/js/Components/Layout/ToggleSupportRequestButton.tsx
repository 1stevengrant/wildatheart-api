import React, { useState } from "react";

import { QuestionMarkCircleIcon } from "@heroicons/react/24/outline";

import { SupportModal } from "@/Components/Modals/SupportModal";
import { linkClass } from "@/helpers/linkClass";

export const ToggleSupportRequestButton = () => {
    const [open, setOpen] = useState(false);

    return (
        <>
            <button onClick={() => setOpen(true)} className={linkClass}>
                <QuestionMarkCircleIcon className={"w-6"} />
            </button>
            <SupportModal open={open} setOpen={setOpen} />
        </>
    );
};
