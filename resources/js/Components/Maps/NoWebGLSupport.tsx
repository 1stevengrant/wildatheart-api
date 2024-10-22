import { ExclamationCircleIcon } from "@heroicons/react/24/outline";

export const NoWebGLSupport = ({ className = "" }: { className?: string }) => {
    return (
        <div
            className={`text-red-500 w-full h-full flex items-center justify-center ${className}`}
        >
            <div
                className={`p-4 bg-amber-50 border-l-4 border-l-amber-300 prose prose-p:text-gray-900 prose-h1:text-gray-900 prose-a:hover:text-orange prose-h1:m-0 prose-h1:text-xl`}
            >
                <div className={`flex items-center`}>
                    <ExclamationCircleIcon className={`w-6 h-6 mr-2`} />
                    <h1 className={`font-bold`}>Mapbox GL not supported</h1>
                </div>

                <p>
                    Mapbox GL is unsupported due to insufficient WebGL2 support
                    in this browser version.{" "}
                </p>
                <p>
                    <a
                        target={`_blank`}
                        href={`https://www.google.com/search?q=enable+webgl+in+browser&sca_esv=5de61c6dbcae3990&sxsrf=ADLYWIKyqD26Z3azbIEvdiywAVEXjjpL1Q%3A1727900804108&ei=hKz9ZuCyBpOcseMPmcLqYA&ved=0ahUKEwjghIuNxPCIAxUTTmwGHRmhGgwQ4dUDCBE&uact=5&oq=enable+webgl+in+browser&gs_lp=Egxnd3Mtd2l6LXNlcnAiF2VuYWJsZSB3ZWJnbCBpbiBicm93c2VyMgUQABiABDIGEAAYFhgeMgYQABgWGB4yBhAAGBYYHjIGEAAYFhgeMgsQABiABBiGAxiKBTILEAAYgAQYhgMYigUyCxAAGIAEGIYDGIoFMggQABiABBiiBDIIEAAYgAQYogRIvxVQ-ARY_RRwAXgBkAEAmAGhAqABng-qAQUwLjkuMbgBA8gBAPgBAZgCC6ACyQ_CAgoQABiwAxjWBBhHwgINEAAYgAQYsAMYQxiKBcICChAAGIAEGEMYigXCAgsQABiABBiRAhiKBZgDAIgGAZAGCpIHBTEuOC4yoAe0RQ&sclient=gws-wiz-serp`}
                    >
                        Find out how to enable WebGL2
                    </a>{" "}
                    or{" "}
                    <a href={route("upcoming.events")}>
                        view a list of events without the map
                    </a>
                    .
                </p>
            </div>
        </div>
    );
};
