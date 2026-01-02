import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface Props {
    value: string;
    onChange: (value: string) => void;
}

export function RichTextEditor({ value, onChange }: Props) {
    const editor = useEditor({
        extensions: [StarterKit],
        content: value,
        onUpdate({ editor }) {
            onChange(editor.getHTML());
        },
    });

    if (!editor) return null;

    return (
        <div className="rounded-md border">
            {/* Toolbar */}
            <div className="flex flex-wrap gap-1 border-b bg-muted p-2">
                <Button
                    size="sm"
                    variant={editor.isActive('bold') ? 'default' : 'outline'}
                    onClick={() =>
                        editor.chain().focus().toggleBold().run()
                    }
                >
                    B
                </Button>

                <Button
                    size="sm"
                    variant={
                        editor.isActive('italic') ? 'default' : 'outline'
                    }
                    onClick={() =>
                        editor.chain().focus().toggleItalic().run()
                    }
                >
                    I
                </Button>

                <Button
                    size="sm"
                    variant={
                        editor.isActive('bulletList')
                            ? 'default'
                            : 'outline'
                    }
                    onClick={() =>
                        editor.chain().focus().toggleBulletList().run()
                    }
                >
                    • List
                </Button>

                <Button
                    size="sm"
                    variant={
                        editor.isActive('orderedList')
                            ? 'default'
                            : 'outline'
                    }
                    onClick={() =>
                        editor.chain().focus().toggleOrderedList().run()
                    }
                >
                    1. List
                </Button>
            </div>

            {/* Editor */}
            <EditorContent
                editor={editor}
                className={cn(
                    'prose prose-sm max-w-none p-4 focus:outline-none',
                )}
            />
        </div>
    );
}
