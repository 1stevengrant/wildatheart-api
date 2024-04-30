import Home from "@/Pages/Home";
import SupportModal from "@/Components/SupportModal";
import { useState } from "react";

const SupportRequestPage = () => {
    const [openSupportModal, setOpenSupportModal] = useState(true)
    return (
        <Home>
            <SupportModal
                open={openSupportModal}
                setOpen={setOpenSupportModal}
            />
        </Home>
    );
};

export default SupportRequestPage;
