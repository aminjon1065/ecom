import { HistoryPlugin } from '@lexical/react/LexicalHistoryPlugin';
import { ListPlugin } from '@lexical/react/LexicalListPlugin';
import { CheckListPlugin } from '@lexical/react/LexicalCheckListPlugin';
import { LexicalErrorBoundary } from '@lexical/react/LexicalErrorBoundary';
import { RichTextPlugin } from '@lexical/react/LexicalRichTextPlugin';
import { TabIndentationPlugin } from '@lexical/react/LexicalTabIndentationPlugin';
import { useState } from 'react';

import { ContentEditable } from '@/components/editor/editor-ui/content-editable';
import { AutoLinkPlugin } from '@/components/editor/plugins/auto-link-plugin';
import { FloatingLinkEditorPlugin } from '@/components/editor/plugins/floating-link-editor-plugin';
import { LinkPlugin } from '@/components/editor/plugins/link-plugin';
import { ListMaxIndentLevelPlugin } from '@/components/editor/plugins/list-max-indent-level-plugin';
import { BlockFormatDropDown } from '@/components/editor/plugins/toolbar/block-format-toolbar-plugin';
import { FormatBulletedList } from '@/components/editor/plugins/toolbar/block-format/format-bulleted-list';
import { FormatCheckList } from '@/components/editor/plugins/toolbar/block-format/format-check-list';
import { FormatHeading } from '@/components/editor/plugins/toolbar/block-format/format-heading';
import { FormatNumberedList } from '@/components/editor/plugins/toolbar/block-format/format-numbered-list';
import { FormatParagraph } from '@/components/editor/plugins/toolbar/block-format/format-paragraph';
import { FormatQuote } from '@/components/editor/plugins/toolbar/block-format/format-quote';
import { ElementFormatToolbarPlugin } from '@/components/editor/plugins/toolbar/element-format-toolbar-plugin';
import { FontFormatToolbarPlugin } from '@/components/editor/plugins/toolbar/font-format-toolbar-plugin';
import { FontSizeToolbarPlugin } from '@/components/editor/plugins/toolbar/font-size-toolbar-plugin';
import { LinkToolbarPlugin } from '@/components/editor/plugins/toolbar/link-toolbar-plugin';
import { ToolbarPlugin } from '@/components/editor/plugins/toolbar/toolbar-plugin';

export function Plugins() {
    const [floatingAnchorElem, setFloatingAnchorElem] =
        useState<HTMLDivElement | null>(null);
    const [isLinkEditMode, setIsLinkEditMode] = useState(false);

    const onRef = (_floatingAnchorElem: HTMLDivElement) => {
        if (_floatingAnchorElem !== null) {
            setFloatingAnchorElem(_floatingAnchorElem);
        }
    };

    return (
        <div className="relative">
            {/* toolbar plugins */}
            <ToolbarPlugin>
                {({ blockType }) => (
                    <div className="vertical-align-middle sticky top-0 z-10 flex gap-2 overflow-auto border-b p-1">
                        <BlockFormatDropDown>
                            <FormatParagraph />
                            <FormatHeading levels={['h1', 'h2', 'h3']} />
                            <FormatNumberedList />
                            <FormatBulletedList />
                            <FormatCheckList />
                            <FormatQuote />
                        </BlockFormatDropDown>
                        <FontFormatToolbarPlugin />
                        <LinkToolbarPlugin setIsLinkEditMode={setIsLinkEditMode} />
                        <FontSizeToolbarPlugin />
                        <ElementFormatToolbarPlugin />
                    </div>
                )}
            </ToolbarPlugin>

            <div className="relative">
                <RichTextPlugin
                    contentEditable={
                        <div className="">
                            <div className="" ref={onRef}>
                                <ContentEditable
                                    placeholder={'Начните вводить текст...'}
                                />
                            </div>
                        </div>
                    }
                    ErrorBoundary={LexicalErrorBoundary}
                />
                {/* editor plugins */}
                <HistoryPlugin />
                <ListPlugin />
                <CheckListPlugin />
                <LinkPlugin />
                <AutoLinkPlugin />
                <ListMaxIndentLevelPlugin maxDepth={7} />
                <TabIndentationPlugin />
                <FloatingLinkEditorPlugin
                    anchorElem={floatingAnchorElem}
                    isLinkEditMode={isLinkEditMode}
                    setIsLinkEditMode={setIsLinkEditMode}
                />
            </div>
        </div>
    );
}
