@extends('layouts.app')

@section('title', 'My Notes - Basecamp')

@section('content')
<div class="max-w-[920px] mx-auto pb-20 font-sans text-[#1d2d35]"
     x-data="{
         noteHtml: @js($notes ?? ''),
         isEmpty: {{ empty(trim(strip_tags($notes ?? ''))) ? 'true' : 'false' }},
         saveTimer: null,
         exec(cmd, val = null) {
             this.$refs.editor.focus();
             document.execCommand(cmd, false, val);
             this.onInput();
         },
         insertLink() {
             const url = prompt('Enter a URL:', 'https://');
             if (url && url !== 'https://') {
                 this.exec('createLink', url);
             }
         },
         insertTable() {
             this.exec('insertHTML', '<table style=\'width:100%;border-collapse:collapse;margin:8px 0;\'><tr><td style=\'border:1px solid #d6d2c9;padding:6px 10px;\'>&nbsp;</td><td style=\'border:1px solid #d6d2c9;padding:6px 10px;\'>&nbsp;</td></tr><tr><td style=\'border:1px solid #d6d2c9;padding:6px 10px;\'>&nbsp;</td><td style=\'border:1px solid #d6d2c9;padding:6px 10px;\'>&nbsp;</td></tr></table><p><br></p>');
         },
         onInput() {
             const html = this.$refs.editor.innerHTML;
             const text = (this.$refs.editor.innerText || '').trim();
             this.isEmpty = text.length === 0 && !html.includes('<img') && !html.includes('<table') && !html.includes('<hr');
             clearTimeout(this.saveTimer);
             this.saveTimer = setTimeout(() => {
                 const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                 fetch('{{ route('my.notes.save') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': token || ''
                     },
                     body: JSON.stringify({ notes: this.isEmpty ? '' : html })
                 }).catch(() => {});
             }, 400);
         }
     }">

    <!-- Basecamp Sheet Container (Exact Match to media_1793278205548_4153ff9f.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] px-7 sm:px-10 pt-7 pb-12 relative">

        <!-- Top Right Bordered Close X Button -->
        <a href="{{ route('home') }}"
           class="absolute top-3.5 right-3.5 w-[32px] h-[32px] rounded-[6px] border border-[#d6d2c9] flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] hover:bg-[#f6f4f0] transition-colors"
           title="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </a>

        <!-- Title: My Notes -->
        <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
            My Notes
        </h1>

        <!-- Basecamp Rich Text Toolbar (Exact Match to media_1793278205548_4153ff9f.jpg) -->
        <div class="mt-5 border-t border-b border-[#e5e2dc] py-1.5 flex items-center justify-between gap-2 select-none">
            <!-- Left Formatting Tools -->
            <div class="flex items-center flex-wrap gap-1 sm:gap-2 text-[#1d2d35]">
                <!-- 1. Image -->
                <button type="button" @click="$refs.imageInput.click()" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Insert image">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </button>
                <input type="file" x-ref="imageInput" accept="image/*" class="hidden"
                       @change="if ($event.target.files[0]) { const r = new FileReader(); r.onload = e => exec('insertImage', e.target.result); r.readAsDataURL($event.target.files[0]); }">

                <!-- 2. Paperclip Attachment -->
                <button type="button" @click="$refs.imageInput.click()" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Attach file">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </button>

                <!-- 3. Bold -->
                <button type="button" @click="exec('bold')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer font-extrabold text-[14px] leading-none" title="Bold">
                    B
                </button>

                <!-- 4. Italic -->
                <button type="button" @click="exec('italic')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer font-serif italic font-bold text-[15px] leading-none" title="Italic">
                    I
                </button>

                <!-- 5. Strikethrough -->
                <button type="button" @click="exec('strikeThrough')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer font-bold text-[14px] line-through leading-none" title="Strikethrough">
                    S
                </button>

                <!-- 6. Heading (Tt v) -->
                <button type="button" @click="exec('formatBlock', 'H2')" class="px-1.5 py-1 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer inline-flex items-center gap-0.5 text-[13.5px] font-bold leading-none" title="Heading">
                    <span>Tt</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- 7. Color / Highlight (Marker v) -->
                <button type="button" @click="exec('hiliteColor', '#fef08a')" class="px-1.5 py-1 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer inline-flex items-center gap-0.5 leading-none" title="Highlight">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- 8. Link -->
                <button type="button" @click="insertLink()" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </button>

                <!-- 9. Quote -->
                <button type="button" @click="exec('formatBlock', 'BLOCKQUOTE')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer font-serif font-extrabold text-[17px] leading-none" title="Quote">
                    ”
                </button>

                <!-- 10. Code -->
                <button type="button" @click="exec('formatBlock', 'PRE')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Code">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>
                    </svg>
                </button>

                <!-- 11. Bulleted List -->
                <button type="button" @click="exec('insertUnorderedList')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Bulleted list">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                        <circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/>
                    </svg>
                </button>

                <!-- 12. Numbered List -->
                <button type="button" @click="exec('insertOrderedList')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Numbered list">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/>
                        <path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
                    </svg>
                </button>

                <!-- 13. Table -->
                <button type="button" @click="insertTable()" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Table">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="12" y1="3" x2="12" y2="21"/>
                    </svg>
                </button>

                <!-- 14. Divider / Horizontal Rule -->
                <button type="button" @click="exec('insertHorizontalRule')" class="p-1.5 rounded hover:bg-[#f2f0ec] transition-colors cursor-pointer" title="Divider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="4" y1="12" x2="20" y2="12"/><line x1="8" y1="7" x2="16" y2="7" stroke-width="1.5"/><line x1="8" y1="17" x2="16" y2="17" stroke-width="1.5"/>
                    </svg>
                </button>
            </div>

            <!-- Right Undo / Redo Tools -->
            <div class="flex items-center gap-1 text-[#8c8881]">
                <button type="button" @click="exec('undo')" class="p-1.5 rounded hover:bg-[#f2f0ec] hover:text-[#1d2d35] transition-colors cursor-pointer" title="Undo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/>
                    </svg>
                </button>
                <button type="button" @click="exec('redo')" class="p-1.5 rounded hover:bg-[#f2f0ec] hover:text-[#1d2d35] transition-colors cursor-pointer" title="Redo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Seamless Rich-Text Editor Area (Jot something down...) -->
        <div class="relative mt-4">
            <div x-show="isEmpty"
                 @click="$refs.editor.focus()"
                 class="absolute top-0 left-0 text-[16.5px] text-[#8c8881] pointer-events-none select-none">
                Jot something down...
            </div>

            <div x-ref="editor"
                 contenteditable="true"
                 @input="onInput()"
                 class="min-h-[500px] w-full focus:outline-none text-[16.5px] text-[#1d2d35] leading-[1.6]">{!! $notes !!}</div>
        </div>

    </div>

</div>
@endsection
