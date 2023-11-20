import React from "react";
import {Event} from "@/types/Event";
import {Link, usePage} from "@inertiajs/react";
import primaryButtonClasses from "@/helpers/primaryButtonClasses";

interface UpdateAddressProps {
    className?: string;
}

const UpdateAddress = ({
                           className,
                       }: UpdateAddressProps) => {
    const page = usePage();
    const event = page.props.event as Event;
    return (
        <div className={`${className} hidden rounded-lg border border-grey-darker p-8 md:block my-6 md:my-10`}>
            <p className="mb-4">
                We want to fuel your Fire. Share your shipping address with us, and
                we’ll ship you a gift to get you going.
            </p>
            <Link href={route('gift', event)}
                  className={`${primaryButtonClasses} w-full block`}>
                Confirm Shipping Address
            </Link>
        </div>
    )
}

export default UpdateAddress
