@extends('layouts.app')

@section('title', 'Rename project tools')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    editingIndex: null,
    tools: [
        { key: 'todos', name: 'To-dos', defaultName: 'To-dos' },
        { key: 'card_table', name: 'Card Table', defaultName: 'Card Table' },
        { key: 'message_board', name: 'Message Board', defaultName: 'Message Board' },
        { key: 'docs_files', name: 'Docs & Files', defaultName: 'Docs & Files' },
        { key: 'calendar', name: 'Calendar', defaultName: 'Calendar' },
        { key: 'chat', name: 'Chat', defaultName: 'Chat' },
        { key: 'check_ins', name: 'Automatic Check-ins', defaultName: 'Automatic Check-ins' },
    ],
    draftName: '',
    startEdit(index) {
        this.editingIndex = index;
        this.draftName = this.tools[index].name;
    },
    saveEdit(index) {
        if (this.draftName.trim()) {
            this.tools[index].name = this.draftName.trim();
        }
        this.editingIndex = null;
    }
}">
    <div class="bc-sheet bg-white overflow-hidden min-h-[640px] text-[#1d2d35]">

        {{-- Top Breadcrumb Header Bar --}}
        <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2] flex items-center gap-1.5 text-[13px] font-medium text-[#5c5955]">
            <a href="{{ route('adminland.index') }}" class="hover:text-[#1d1c1b] hover:underline">Adminland</a>
        </div>

        {{-- Sheet Body --}}
        <div class="px-8 sm:px-14 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-2">Rename project tools</h1>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-8">
                Change the default project tool names here. If you prefer &ldquo;Announcements&rdquo; over &ldquo;Message Board&rdquo;, for instance, rename the tool and the new name will be used moving forward. Changes won&rsquo;t affect existing projects and templates.
            </p>

            {{-- Tools List --}}
            <div class="divide-y divide-[#e5e2dc]/60">
                <template x-for="(tool, index) in tools" :key="tool.key">
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0 flex-1">
                            {{-- Circular Tool Icon Badge --}}
                            <div class="w-9 h-9 rounded-full bg-[#e8eef3] text-[#1d1c1b] flex items-center justify-center shrink-0">
                                <template x-if="tool.key === 'todos'">
                                    <svg class="w-4 h-4 text-[#1d1c1b]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="tool.key === 'card_table'">
                                    <svg class="w-4 h-4 text-[#1d1c1b]" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="8" height="16" rx="1.5"/><rect x="13" y="4" width="8" height="12" rx="1.5"/></svg>
                                </template>
                                <template x-if="tool.key === 'message_board'">
                                    <svg class="w-4 h-4 text-[#1d1c1b]" fill="currentColor" viewBox="0 0 24 24"><path d="M18 8a3 3 0 013 3v2a3 3 0 01-3 3h-1.2l-5.8 3v-3H7a2 2 0 01-2-2V9a2 2 0 012-2h4l5.8-3H18v4z"/></svg>
                                </template>
                                <template x-if="tool.key === 'docs_files'">
                                    <svg class="w-4 h-4 text-[#1d1c1b]" fill="currentColor" viewBox="0 0 24 24"><path d="M3 6a2 2 0 012-2h4l2 2h8a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V6z"/></svg>
                                </template>
                                <template x-if="tool.key === 'calendar'">
                                    <svg class="w-4 h-4 text-[#1d1c1b]" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2h1V3a1 1 0 011-1zm12 7H5v10h14V9z"/></svg>
                                </template>
                                <template x-if="tool.key === 'chat'">
                                    <svg class="w-4 h-4 text-[#1d1c1b]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c5.5 0 10 3.58 10 8s-4.5 8-10 8c-1.24 0-2.43-.18-3.53-.5C5.55 21 2 21 2 21c2.33-2.33 2.7-3.9 2.75-4.5C3.05 15.07 2 13.13 2 11c0-4.42 4.5-8 10-8z"/></svg>
                                </template>
                                <template x-if="tool.key === 'check_ins'">
                                    <span class="text-[15px] font-black leading-none text-[#1d1c1b]">?</span>
                                </template>
                            </div>

                            {{-- Name & Default Label OR Inline Edit Form --}}
                            <div class="min-w-0 flex-1">
                                <div x-show="editingIndex !== index">
                                    <div class="text-[14px] font-bold text-[#1d1c1b] leading-snug" x-text="tool.name"></div>
                                    <div class="text-[12px] text-[#7c7872]" x-text="'Default: ' + tool.defaultName"></div>
                                </div>
                                <div x-show="editingIndex === index" x-cloak class="flex items-center gap-2">
                                    <input type="text" x-model="draftName" @keydown.enter.prevent="saveEdit(index)" @keydown.escape="editingIndex = null"
                                        class="px-3 py-1.5 rounded-[6px] border border-[#3b82f6] bg-white text-[14px] text-[#1d1c1b] focus:outline-none w-64">
                                    <button type="button" @click="saveEdit(index)" class="px-3 py-1.5 rounded-[6px] bg-[#1d64c8] hover:bg-[#1752a6] text-white text-[12px] font-bold transition-colors">Save</button>
                                    <button type="button" @click="editingIndex = null" class="px-2.5 py-1.5 rounded-[6px] border border-[#d4d0c8] text-[12px] text-[#5c5955] hover:text-[#1d1c1b]">Cancel</button>
                                </div>
                            </div>
                        </div>

                        {{-- Right Pencil Edit Icon --}}
                        <button type="button" x-show="editingIndex !== index" @click="startEdit(index)" title="Rename tool"
                            class="p-1.5 text-[#7c7872] hover:text-[#1d1c1b] transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

