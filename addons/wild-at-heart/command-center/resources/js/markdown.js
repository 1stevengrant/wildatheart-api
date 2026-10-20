import { marked } from "marked";
import DOMPurify from "dompurify";

// Answers can quote host-entered text, so the rendered HTML is always sanitised.
export function renderMarkdown(content) {
    return DOMPurify.sanitize(
        marked.parse(content ?? "", { gfm: true, breaks: false })
    );
}
