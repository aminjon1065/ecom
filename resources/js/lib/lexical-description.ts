type LexicalNode = {
    type?: string;
    text?: string;
    format?: number;
    tag?: string;
    url?: string;
    listType?: 'bullet' | 'number' | 'check';
    checked?: boolean;
    children?: LexicalNode[];
};

type LexicalState = {
    root?: LexicalNode;
};

function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function escapeAttribute(value: string): string {
    return escapeHtml(value);
}

function sanitizeUrl(value: string): string {
    const normalized = value.trim();

    if (normalized === '') {
        return '#';
    }

    if (/^(https?:|mailto:|tel:|\/|#)/i.test(normalized)) {
        return normalized;
    }

    return '#';
}

function renderTextNode(node: LexicalNode): string {
    const text = escapeHtml(node.text ?? '');
    const format = typeof node.format === 'number' ? node.format : 0;

    let html = text;

    if (format & 16) {
        html = `<code>${html}</code>`;
    }
    if (format & 8) {
        html = `<u>${html}</u>`;
    }
    if (format & 4) {
        html = `<s>${html}</s>`;
    }
    if (format & 2) {
        html = `<em>${html}</em>`;
    }
    if (format & 1) {
        html = `<strong>${html}</strong>`;
    }

    return html;
}

function renderChildren(nodes: LexicalNode[] | undefined): string {
    if (!Array.isArray(nodes)) {
        return '';
    }

    return nodes.map(renderNode).join('');
}

function renderNode(node: LexicalNode): string {
    switch (node.type) {
        case 'root':
            return renderChildren(node.children);
        case 'paragraph': {
            const content = renderChildren(node.children);
            if (content.trim() === '') {
                return '';
            }

            return `<p>${content}</p>`;
        }
        case 'linebreak':
            return '<br />';
        case 'text':
            return renderTextNode(node);
        case 'heading': {
            const tag = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(
                node.tag ?? '',
            )
                ? node.tag
                : 'h2';

            return `<${tag}>${renderChildren(node.children)}</${tag}>`;
        }
        case 'quote':
            return `<blockquote>${renderChildren(node.children)}</blockquote>`;
        case 'list': {
            const tag = node.listType === 'number' ? 'ol' : 'ul';
            return `<${tag}>${renderChildren(node.children)}</${tag}>`;
        }
        case 'listitem': {
            const checkedAttr =
                node.checked === true ? ' data-checked="true"' : '';
            return `<li${checkedAttr}>${renderChildren(node.children)}</li>`;
        }
        case 'link': {
            const href = typeof node.url === 'string' ? node.url.trim() : '';
            const safeHref = escapeAttribute(sanitizeUrl(href));
            return `<a href="${safeHref}" target="_blank" rel="noopener noreferrer">${renderChildren(node.children)}</a>`;
        }
        default:
            return renderChildren(node.children);
    }
}

function isLexicalState(value: unknown): value is LexicalState {
    if (!value || typeof value !== 'object') {
        return false;
    }

    const state = value as LexicalState;

    return Boolean(state.root && state.root.type === 'root');
}

function parseLexicalState(value: unknown): LexicalState | null {
    if (isLexicalState(value)) {
        return value;
    }

    if (typeof value !== 'string' || value.trim() === '') {
        return null;
    }

    try {
        const parsed: unknown = JSON.parse(value);

        if (isLexicalState(parsed)) {
            return parsed;
        }

        if (typeof parsed === 'string') {
            try {
                const nestedParsed: unknown = JSON.parse(parsed);

                if (isLexicalState(nestedParsed)) {
                    return nestedParsed;
                }
            } catch {
                return null;
            }
        }
    } catch {
        return null;
    }

    return null;
}

export function lexicalDescriptionToHtml(description: unknown): string {
    if (description === null || description === undefined) {
        return '';
    }

    const lexicalState = parseLexicalState(description);

    if (lexicalState) {
        return renderNode(lexicalState.root ?? { type: 'root', children: [] });
    }

    if (typeof description === 'string') {
        return description;
    }

    return '';
}
