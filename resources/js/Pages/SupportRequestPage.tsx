import { useState } from "react";

import { SupportModal } from "@/Components/SupportModal";
import HomePage from "@/Pages/HomePage";

const SupportRequestPage = () => {
    const [openSupportModal, setOpenSupportModal] = useState(true);
    return (
        <HomePage>
            <SupportModal
                open={openSupportModal}
                setOpen={setOpenSupportModal}
            />
        </HomePage>
    );
};

export default SupportRequestPage;
