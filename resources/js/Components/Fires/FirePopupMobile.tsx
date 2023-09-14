import React from 'react';
import FireContent from "./FireContent";
import {XMarkIcon} from "@heroicons/react/24/solid";
import {useRecoilState} from "recoil";
import {currentFireAtom} from "../../store";

interface FirePopupProps {
    className?: string;
}

const FirePopupMobile: React.FC<FirePopupProps> = ({className}) => {
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom)
    return (
        <div
            className={'fixed bottom-[96px] right-4 left-4 text-white rounded-md shadow-lg p-2 md:p-4 bg-black ' + className}>
            <div className={'bg-white mx-auto h-[5px] w-[75px] rounded-full opacity-30 mt-1'}/>
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
export default FirePopupMobile
