import Spinner from "@/Components/Ui/Spinner";
import { useLoaderState } from "@/Hooks/useLoaderState";

export const IsLoading = ({ classnames = "", text = "" }) => {
    const { showLoader } = useLoaderState();
    return (
        <>
            {showLoader && (
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
