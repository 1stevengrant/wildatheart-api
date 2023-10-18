import React from "react";
import HeadingOne from "@/Components/Type/HeadingOne";
import {Link, usePage} from "@inertiajs/react";
import useRoute from "@/Hooks/useRoute";
import {Fire} from "@/types/Fire";

const GiftComing = () => {
    const page = usePage();
    const fire = page.props.campfire as Fire;
    const route = useRoute();

    return (
        <div className={'max-w-[500px] mx-auto p-4'}>
            <HeadingOne>Yay!
                <br/>
                Your gift is on its way!
            </HeadingOne>
            <div className={`mt-8`}>
                <Link
                    href={route('view.fire', fire)}
                    className="rounded-md border border-transparent bg-orange px-4 py-2 text-center font-medium text-white transition duration-150 ease-in-out hover:bg-transparent-orange disabled:cursor-not-allowed disabled:bg-grey-200 disabled:text-grey-darker"
                >
                    Visit Your Fire Listing
                </Link>
            </div>
        </div>
    )
};

export default GiftComing;
