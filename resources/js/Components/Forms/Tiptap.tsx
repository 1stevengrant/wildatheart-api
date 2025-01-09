import React, {
    forwardRef,
    useEffect,
    useImperativeHandle,
    useRef,
} from "react";

import CharacterCount from "@tiptap/extension-character-count";
import { Color } from "@tiptap/extension-color";
import Link from "@tiptap/extension-link";
import Placeholder from "@tiptap/extension-placeholder";
import TextStyle from "@tiptap/extension-text-style";
import { EditorContent, useEditor } from "@tiptap/react";
import StarterKit from "@tiptap/starter-kit";

import { TiptapMenuBar } from "./TiptapMenuBar";

type HTMLAttributesWithoutOnChange = Omit<
    React.HTMLAttributes<HTMLDivElement>,
    "onChange"
>;

interface TiptapProps extends HTMLAttributesWithoutOnChange {
    value?: string | null;
    onChange?: ((value: string) => void) | undefined;
    isFocused?: boolean;
    className?: string;
    disabled?: boolean;
    name?: string;
    id?: string;
    placeholderText?: string;
    limit?: number;
}

export const Tiptap = forwardRef<any, TiptapProps>(
    (
        {
            limit = 500,
            value = "",
            onChange,
            isFocused = false,
            className = "",
            disabled = false,
            placeholderText = "Write something amazing...",
            ...props
        },
        ref
    ) => {
        const editorRef = useRef(null);

        const extensions = [
            StarterKit,
            Placeholder.configure({
                placeholder: placeholderText,
            }),
            Link.configure({
                openOnClick: false,
                autolink: true,
                HTMLAttributes: {
                    class: "text-p200 underline",
                },
            }),
            TextStyle,
            Color,
            CharacterCount.configure({
                limit,
            }),
        ];

        const editor = useEditor({
            extensions,
            content: value,
            editable: !disabled,
            onUpdate: ({ editor }) => {
                const html = editor.getHTML();
                const isOnlyEmptyParagraph = html === "<p></p>";
                onChange?.(isOnlyEmptyParagraph ? "" : html);
            },
            editorProps: {
                attributes: {
                    class: "prose prose-invert max-w-none focus:outline-none",
                },
            },
        });

        useImperativeHandle(ref, () => ({
            focus: () => editor?.chain().focus().run(),
            getContent: () => editor?.getHTML(),
            setContent: (content: string) =>
                editor?.commands.setContent(content),
        }));

        useEffect(() => {
            if (isFocused && editor && !editor.isDestroyed) {
                editor.chain().focus().run();
            }
        }, [isFocused, editor]);

        if (!editor) {
            return null;
        }

        const percentage = editor
            ? Math.round(
                  (100 / limit) * editor.storage.characterCount.characters()
              )
            : 0;

        return (
            <>
                <div
                    className={`border border-n400 bg-n900 rounded-md ${className}`}
                    {...props}
                    ref={editorRef}
                >
                    <TiptapMenuBar editor={editor} disabled={disabled} />
                    <EditorContent
                        editor={editor}
                        className="p-2 bg-n900 text-white focus-within:border-p200 focus-within:ring-p200 min-h-[150px]"
                    />
                </div>
                <div
                    className={`text-white text-sm mt-2 flex items-center justify-between ${editor.storage.characterCount.characters() === limit ? "text-y75" : ""}`}
                >
                    <div className={`flex space-x-2 items-center`}>
                        <svg
                            height="20"
                            width="20"
                            viewBox="0 0 20 20"
                            className={`text-p200`}
                        >
                            <circle r="10" cx="10" cy="10" fill="#6F777A" />
                            <circle
                                r="5"
                                cx="10"
                                cy="10"
                                fill="transparent"
                                stroke="currentColor"
                                strokeWidth="10"
                                strokeDasharray={`calc(${percentage} * 31.4 / 100) 31.4`}
                                transform="rotate(-90) translate(-20)"
                            />
                            <circle r="6" cx="10" cy="10" fill="text-p200" />
                        </svg>
                        <span>
                            {editor.storage.characterCount.characters()} /{" "}
                            {limit}
                        </span>
                        <span>characters</span>
                    </div>
                    <span>{editor.storage.characterCount.words()} words</span>
                </div>
            </>
        );
    }
);
