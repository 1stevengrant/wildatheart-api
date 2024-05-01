import { useRecoilState } from "recoil";

import Spinner from "@/Components/Ui/Spinner";
import { searchingLocationState } from "@/store/coordinatesAtom";

export const IsGettingLocation = ({ classnames = "", text = "" }) => {
    const [searchingLocation] = useRecoilState(searchingLocationState);
    return (
        <>
            {searchingLocation && (
                <div
                    className={`flex justify-center items-center ${classnames}`}
                >
                    <div
                        className={`bg-white px-4 py-2 inline-flex items-center text-black rounded shadow-md`}
                    >
                        <Spinner className={`h-5 w-5 mr-4 text-black`} />
                        <div className={`font-semibold`}>{text}...</div>
                    </div>
                </div>
            )}
        </>
    );
};
