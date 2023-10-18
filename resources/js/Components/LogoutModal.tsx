import React from "react";
import ModalActions from "@/Components/Ui/ModalActions";
import SecondaryButton from "@/Components/Buttons/SecondaryButton";
import primaryButtonClasses from "@/helpers/primaryButtonClasses";
import Modal from "@/Components/Ui/Modal";
import ModalHeading from "@/Components/Type/ModalHeading";

interface LogoutModalProps {
    open: boolean;
    setOpen: React.Dispatch<React.SetStateAction<boolean>>;
}

const LogoutModal = ({open, setOpen}: LogoutModalProps) => {
   return (
       <Modal open={open} setOpen={setOpen}>
           <ModalHeading>Logout?</ModalHeading>
           <p> Are you sure you want to logout?</p>
           <ModalActions>
               <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
               <a href={route('logout')} className={`${primaryButtonClasses}`}>
                   Logout
               </a>
           </ModalActions>
       </Modal>
   )
}

export default LogoutModal;
