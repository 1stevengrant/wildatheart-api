import React from 'react';
import FireContent from "./FireContent";
import {XMarkIcon} from "@heroicons/react/24/solid";
import {useRecoilState} from "recoil";
import {currentFireAtom} from "../store";

const FirePopup = (props: any) => {
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom)
    return (
        <div className="rounded-md shadow-lg p-2 md:p-4 bg-black relative hidden md:block">
            <button
                className="float-right hover:text-orange transition ease-in-out duration-75"
                onClick={() => setCurrentFire(undefined)}>
                <XMarkIcon className="h-5 w-5"/>
            </button>
            <a href={currentFire?.url}
               className="flex items-stretch space-x-4 clear-both">
                <FireContent fire={currentFire}/>
            </a>
        </div>
    );
};
export default FirePopup
