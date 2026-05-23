<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import { watch, onBeforeUnmount } from 'vue';

// ── Props & Emits ─────────────────────────────────────────────────
interface Props {
    modelValue: string;
    placeholder?: string;
    hasError?:    boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Tulis isi berita dengan lengkap dan jelas...',
    hasError:    false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

// ── Editor ────────────────────────────────────────────────────────
const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3] },
            bulletList:  { keepMarks: true, keepAttributes: false },
            orderedList: { keepMarks: true, keepAttributes: false },
        }),
        Underline,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        Link.configure({
            openOnClick:       false,
            HTMLAttributes: {
                class: 'text-blue-600 underline underline-offset-2 cursor-pointer',
                rel:   'noopener noreferrer',
                target: '_blank',
            },
        }),
        Image.configure({
            HTMLAttributes: { class: 'rounded-lg max-w-full my-3 border border-gray-200' },
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none focus:outline-none min-h-[280px] px-4 py-3',
        },
    },
    onUpdate: ({ editor }) => {
        // Kalau isi kosong (hanya tag kosong) kirim string kosong
        const html = editor.isEmpty ? '' : editor.getHTML();
        emit('update:modelValue', html);
    },
});

// Sync dari luar → editor (misalnya saat edit, data dari server sudah ada)
watch(() => props.modelValue, (val) => {
    if (!editor.value) return;
    if (editor.value.getHTML() === val) return; // hindari loop
    editor.value.commands.setContent(val ?? '', { emitUpdate: false });
});

onBeforeUnmount(() => editor.value?.destroy());

// ── Toolbar helpers ───────────────────────────────────────────────
const setLink = () => {
    const prev = editor.value?.getAttributes('link').href ?? '';
    const url  = window.prompt('Masukkan URL:', prev);
    if (url === null) return;
    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }
    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const insertImage = () => {
    const url = window.prompt('Masukkan URL gambar:');
    if (!url) return;
    editor.value?.chain().focus().setImage({ src: url }).run();
};

// ── Toolbar button class helper ───────────────────────────────────
const btnBase = 'flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 dark:text-gray-400 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed';
const btnActive = 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';

const btn = (isActive: boolean) => `${btnBase} ${isActive ? btnActive : ''}`;
</script>

<template>
    <div :class="[
        'rounded-xl border overflow-hidden bg-white dark:bg-gray-900 transition-colors',
        hasError
            ? 'border-red-400 ring-1 ring-red-400'
            : 'border-gray-200 dark:border-gray-700 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500'
    ]">

        <!-- ── TOOLBAR ── -->
        <div v-if="editor"
            class="flex flex-wrap items-center gap-0.5 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 px-2 py-1.5">

            <!-- HISTORY -->
            <div class="flex items-center gap-0.5">
                <button type="button" :class="btnBase" :disabled="!editor.can().undo()" @click="editor.chain().focus().undo().run()" title="Undo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                </button>
                <button type="button" :class="btnBase" :disabled="!editor.can().redo()" @click="editor.chain().focus().redo().run()" title="Redo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- HEADING -->
            <div class="flex items-center gap-0.5">
                <button type="button"
                    :class="btn(editor.isActive('heading', { level: 2 }))"
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    title="Heading 2">
                    <span class="text-xs font-bold">H2</span>
                </button>
                <button type="button"
                    :class="btn(editor.isActive('heading', { level: 3 }))"
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    title="Heading 3">
                    <span class="text-xs font-bold">H3</span>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- FORMAT TEKS -->
            <div class="flex items-center gap-0.5">
                <button type="button"
                    :class="btn(editor.isActive('bold'))"
                    @click="editor.chain().focus().toggleBold().run()"
                    title="Bold (Ctrl+B)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive('italic'))"
                    @click="editor.chain().focus().toggleItalic().run()"
                    title="Italic (Ctrl+I)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="19" y1="4" x2="10" y2="4" stroke-width="2" stroke-linecap="round"/><line x1="14" y1="20" x2="5" y2="20" stroke-width="2" stroke-linecap="round"/><line x1="15" y1="4" x2="9" y2="20" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive('underline'))"
                    @click="editor.chain().focus().toggleUnderline().run()"
                    title="Underline (Ctrl+U)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" y1="20" x2="20" y2="20" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive('strike'))"
                    @click="editor.chain().focus().toggleStrike().run()"
                    title="Strikethrough">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4H9a3 3 0 0 0-2.83 4M14 12a4 4 0 0 1 0 8H6"/><line x1="4" y1="12" x2="20" y2="12" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- TEXT ALIGN -->
            <div class="flex items-center gap-0.5">
                <button type="button"
                    :class="btn(editor.isActive({ textAlign: 'left' }))"
                    @click="editor.chain().focus().setTextAlign('left').run()"
                    title="Rata Kiri">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="12" x2="15" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="18" x2="18" y2="18" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive({ textAlign: 'center' }))"
                    @click="editor.chain().focus().setTextAlign('center').run()"
                    title="Tengah">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="12" x2="18" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="4" y1="18" x2="20" y2="18" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive({ textAlign: 'right' }))"
                    @click="editor.chain().focus().setTextAlign('right').run()"
                    title="Rata Kanan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="12" x2="21" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="18" x2="21" y2="18" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive({ textAlign: 'justify' }))"
                    @click="editor.chain().focus().setTextAlign('justify').run()"
                    title="Rata Kanan-Kiri">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="12" x2="21" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="18" x2="21" y2="18" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- LIST -->
            <div class="flex items-center gap-0.5">
                <button type="button"
                    :class="btn(editor.isActive('bulletList'))"
                    @click="editor.chain().focus().toggleBulletList().run()"
                    title="Bullet List">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="9" y1="6" x2="20" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="12" x2="20" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="18" x2="20" y2="18" stroke-width="2" stroke-linecap="round"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>
                </button>
                <button type="button"
                    :class="btn(editor.isActive('orderedList'))"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    title="Numbered List">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="10" y1="6" x2="21" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="12" x2="21" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="18" x2="21" y2="18" stroke-width="2" stroke-linecap="round"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-2-2-2"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- BLOCKQUOTE + HR -->
            <div class="flex items-center gap-0.5">
                <button type="button"
                    :class="btn(editor.isActive('blockquote'))"
                    @click="editor.chain().focus().toggleBlockquote().run()"
                    title="Blockquote">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1zm12 0c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
                </button>
                <button type="button"
                    :class="btnBase"
                    @click="editor.chain().focus().setHorizontalRule().run()"
                    title="Garis Pemisah">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- LINK + IMAGE -->
            <div class="flex items-center gap-0.5">
                <button type="button"
                    :class="btn(editor.isActive('link'))"
                    @click="setLink"
                    title="Tambah Link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                </button>
                <button type="button"
                    :class="btnBase"
                    @click="insertImage"
                    title="Sisipkan Gambar (URL)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                </button>
            </div>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Clear formatting -->
            <button type="button"
                :class="btnBase"
                @click="editor.chain().focus().clearNodes().unsetAllMarks().run()"
                title="Hapus semua format">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M17.94 18 6 6"/></svg>
            </button>
        </div>

        <!-- ── EDITOR AREA ── -->
        <EditorContent
            :editor="editor"
            class="text-sm text-gray-900 dark:text-white [&_.ProseMirror]:min-h-[280px] [&_.ProseMirror]:px-4 [&_.ProseMirror]:py-3 [&_.ProseMirror]:focus:outline-none
            [&_.ProseMirror_p]:mb-3
            [&_.ProseMirror_h2]:text-xl [&_.ProseMirror_h2]:font-bold [&_.ProseMirror_h2]:text-gray-900 [&_.ProseMirror_h2]:dark:text-white [&_.ProseMirror_h2]:mt-5 [&_.ProseMirror_h2]:mb-2
            [&_.ProseMirror_h3]:text-lg [&_.ProseMirror_h3]:font-semibold [&_.ProseMirror_h3]:text-gray-900 [&_.ProseMirror_h3]:dark:text-white [&_.ProseMirror_h3]:mt-4 [&_.ProseMirror_h3]:mb-2
            [&_.ProseMirror_ul]:list-disc [&_.ProseMirror_ul]:pl-6 [&_.ProseMirror_ul]:mb-3
            [&_.ProseMirror_ol]:list-decimal [&_.ProseMirror_ol]:pl-6 [&_.ProseMirror_ol]:mb-3
            [&_.ProseMirror_li]:mb-1
            [&_.ProseMirror_blockquote]:border-l-4 [&_.ProseMirror_blockquote]:border-blue-400 [&_.ProseMirror_blockquote]:bg-blue-50 [&_.ProseMirror_blockquote]:dark:bg-blue-900/10 [&_.ProseMirror_blockquote]:pl-4 [&_.ProseMirror_blockquote]:py-2 [&_.ProseMirror_blockquote]:italic [&_.ProseMirror_blockquote]:text-gray-600 [&_.ProseMirror_blockquote]:dark:text-gray-400 [&_.ProseMirror_blockquote]:my-3 [&_.ProseMirror_blockquote]:rounded-r-lg
            [&_.ProseMirror_hr]:border-gray-200 [&_.ProseMirror_hr]:dark:border-gray-700 [&_.ProseMirror_hr]:my-4
            [&_.ProseMirror_strong]:font-bold
            [&_.ProseMirror_em]:italic
            [&_.ProseMirror_u]:underline
            [&_.ProseMirror_s]:line-through
            [&_.ProseMirror_.is-editor-empty:first-child::before]:content-[attr(data-placeholder)] [&_.ProseMirror_.is-editor-empty:first-child::before]:text-gray-400 [&_.ProseMirror_.is-editor-empty:first-child::before]:dark:text-gray-600 [&_.ProseMirror_.is-editor-empty:first-child::before]:float-left [&_.ProseMirror_.is-editor-empty:first-child::before]:pointer-events-none [&_.ProseMirror_.is-editor-empty:first-child::before]:h-0
            "
        />

        <!-- Word count -->
        <div v-if="editor" class="flex items-center justify-end border-t border-gray-100 dark:border-gray-800 px-4 py-1.5 bg-gray-50 dark:bg-gray-800/40">
            <span class="text-xs text-gray-400 dark:text-gray-500">
                {{ editor.storage.characterCount?.words?.() ?? editor.getText().trim().split(/\s+/).filter(Boolean).length }} kata
            </span>
        </div>
    </div>
</template>
