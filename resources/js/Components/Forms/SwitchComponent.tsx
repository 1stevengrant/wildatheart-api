import { Switch } from "@headlessui/react";
import classNames from "classnames";

interface SwitchComponentProps {
    checked: boolean;
    onChange: (checked: boolean) => void;
    label?: string;
}

export const SwitchComponent = ({
    checked,
    onChange,
    label,
}: SwitchComponentProps) => {
    return (
        <>
            {label && (
                <label className="mb-1 block text-sm font-medium text-n20">
                    {label}
                </label>
            )}
            <Switch
                checked={checked}
                onChange={onChange}
                className={classNames(
                    checked ? "bg-p200" : "bg-n600",
                    `relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-p200 focus:ring-offset-2`
                )}
            >
                <div className="sr-only">Use setting</div>
                <div
                    className={classNames(
                        checked ? "translate-x-5" : "translate-x-0",
                        `pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow  ring-0 transition duration-200 ease-in-out`
                    )}
                >
                    <div
                        className={classNames(
                            checked
                                ? "opacity-0 duration-100 ease-out"
                                : "opacity-100 duration-200 ease-in",
                            `absolute inset-0 flex h-full w-full items-center justify-center transition-opacity`
                        )}
                        aria-hidden="true"
                    />

                    <div
                        className={classNames(
                            checked
                                ? "opacity-100 duration-200 ease-in"
                                : "opacity-0 duration-100 ease-out",
                            `absolute inset-0 flex h-full w-full items-center justify-center transition-opacity`
                        )}
                        aria-hidden="true"
                    ></div>
                </div>
            </Switch>
        </>
    );
};
