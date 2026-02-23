import test from 'node:test';
import assert from 'node:assert/strict';

import { lexicalDescriptionToHtml } from './lexical-description.ts';

const lexicalPayload = {
    root: {
        children: [
            {
                children: [
                    {
                        detail: 0,
                        format: 0,
                        mode: 'normal',
                        style: '',
                        text: ' ',
                        type: 'text',
                        version: 1,
                    },
                    {
                        detail: 0,
                        format: 1,
                        mode: 'normal',
                        style: '',
                        text: 'asdaa asda',
                        type: 'text',
                        version: 1,
                    },
                ],
                direction: 'ltr',
                format: '',
                indent: 0,
                type: 'paragraph',
                version: 1,
                textFormat: 0,
                textStyle: '',
            },
        ],
        direction: 'ltr',
        format: '',
        indent: 0,
        type: 'root',
        version: 1,
    },
};

test('renders lexical JSON string description', () => {
    const html = lexicalDescriptionToHtml(JSON.stringify(lexicalPayload));

    assert.equal(html, '<p> <strong>asdaa asda</strong></p>');
});

test('renders lexical object description', () => {
    const html = lexicalDescriptionToHtml(lexicalPayload);

    assert.equal(html, '<p> <strong>asdaa asda</strong></p>');
});
