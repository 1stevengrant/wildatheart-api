import React from "react";

import {
    FontBoldIcon,
    FontItalicIcon,
    ListBulletIcon,
    QuoteIcon,
    Link2Icon,
    LinkBreak2Icon,
    HeadingIcon,
    PilcrowIcon,
    ResetIcon,
} from "@radix-ui/react-icons";
import { Editor } from "@tiptap/react";

interface MenuBarProps {
    editor: Editor | null;
    disabled?: boolean;
}

const buttonClass = `text-xs border-2 border-n600 p-1 rounded-md mb-2 text-n50`;

export const TiptapMenuBar: React.FC<MenuBarProps> = ({ editor, disabled }) => {
    if (!editor) {
        return null;
    }

    const setLink = () => {
        const previousUrl = editor.getAttributes("link").href;
        const url = window.prompt("URL", previousUrl);

        if (url === null) {
            return;
        }

        if (url === "") {
            editor.chain().focus().extendMarkRange("link").unsetLink().run();
            return;
        }

        editor
            .chain()
            .focus()
            .extendMarkRange("link")
            .setLink({ href: url })
            .run();
    };

    const getButtonClass = (isActive: boolean) =>
        `${isActive ? "border-p200 bg-p200 text-white" : ""} ${buttonClass}`.trim();

    const levels = [2, 3] as const;

    return (
        <div className="ml-2 mt-2 flex flex-wrap items-center space-x-2">
            {levels.map((level) => (
                <button
                    type="button"
                    key={level}
                    disabled={disabled}
                    onClick={() =>
                        editor.chain().focus().toggleHeading({ level }).run()
                    }
                    className={`${getButtonClass(
                        editor.isActive("heading", { level })
                    )} flex items-center`}
                >
                    <HeadingIcon />
                    <span className="font-serif">{level}</span>
                </button>
            ))}
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().setParagraph().run()}
                className={getButtonClass(editor.isActive("paragraph"))}
            >
                <PilcrowIcon />
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().toggleBold().run()}
                className={getButtonClass(editor.isActive("bold"))}
            >
                <FontBoldIcon />
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().toggleItalic().run()}
                className={getButtonClass(editor.isActive("italic"))}
            >
                <FontItalicIcon />
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().toggleBulletList().run()}
                className={getButtonClass(editor.isActive("bulletList"))}
            >
                <ListBulletIcon />
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().toggleOrderedList().run()}
                className={getButtonClass(editor.isActive("orderedList"))}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    strokeWidth="1.5"
                    stroke="currentColor"
                    className="w-4"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99"
                    />
                </svg>
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().toggleBlockquote().run()}
                className={getButtonClass(editor.isActive("blockquote"))}
            >
                <QuoteIcon />
            </button>
            <button
                type="button"
                disabled={disabled}
                className={buttonClass}
                onClick={() => editor.chain().focus().undo().run()}
            >
                <ResetIcon />
            </button>
            <button
                type="button"
                disabled={disabled}
                className={buttonClass}
                onClick={() => editor.chain().focus().redo().run()}
            >
                <ResetIcon className="transform rotate-180" />
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={setLink}
                className={getButtonClass(editor.isActive("link"))}
            >
                <Link2Icon />
            </button>
            <button
                type="button"
                disabled={disabled}
                onClick={() => editor.chain().focus().unsetLink().run()}
                className={getButtonClass(editor.isActive("link"))}
            >
                <LinkBreak2Icon />
            </button>
        </div>
    );
};
