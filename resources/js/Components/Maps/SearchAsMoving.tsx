import React from "react";
import {useRecoilState} from "recoil";
import {searchAsMovingAtom} from "../../store";

const SearchAsMoving = () => {
    const [checked, setChecked] = useRecoilState(searchAsMovingAtom)

    return (
        <div
            className={
                'absolute z-30 top-4 right-4 bg-white text-black p-3 rounded-lg text-sm shadow-lg'
            }
        >
            <input
                defaultChecked={checked}
                type={'checkbox'}
                className={'rounded mr-2'}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                    setChecked(e.target.checked)
                }
            />
            Search when I move the map
        </div>
    )
}

export default SearchAsMoving
