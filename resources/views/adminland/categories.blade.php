@extends('layouts.app')

@section('title', 'Change message categories')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    categories: [
        { icon: '📢', name: 'Announcement' },
        { icon: '✨', name: 'FYI' },
        { icon: '❤️', name: 'Heartbeat' },
        { icon: '💡', name: 'Pitch' },
        { icon: '👋', name: 'Question' },
    ],
    editingIndex: null,
    draftIcon: '📌',
    draftName: '',
    addingNew: false,
    newIcon: '📌',
    newName: '',
    startEdit(index) {
        this.editingIndex = index;
        this.draftIcon = this.categories[index].icon;
        this.draftName = this.categories[index].name;
    },
    saveEdit(index) {
        if (this.draftName.trim()) {
            this.categories[index].icon = this.draftIcon.trim() || '📌';
            this.categories[index].name = this.draftName.trim();
        }
        this.editingIndex = null;
    },
    removeCategory(index) {
        this.categories.splice(index, 1);
    },
    addCategory() {
        if (this.newName.trim()) {
            this.categories.push({ icon: this.newIcon.trim() || '📌', name: this.newName.trim() });
            this.newName = '';
            this.addingNew = false;
        }
    }
}">
    <div class="bc-sheet bg-white overflow-hidden min-h-[640px] text-[#1d2d35]">

        {{-- Top Breadcrumb Header Bar --}}
        <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2] flex items-center gap-1.5 text-[13px] font-medium text-[#5c5955]">
            <a href="{{ route('adminland.index') }}" class="hover:text-[#1d1c1b] hover:underline">Adminland</a>
        </div>

        {{-- Sheet Body --}}
        <div class="px-8 sm:px-14 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-2">Change message categories</h1>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-3">
                Message categories help create structure and shared meaning for messages in your projects. <a href="javascript:void(0)" class="underline hover:text-[#1d64c8]">See an example...</a>
            </p>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-6">
                Note: Changes you make below won&rsquo;t apply to existing projects or project templates. You can customize categories on individual projects or templates from each Message Board&rsquo;s ••• menu in the top right.
            </p>

            {{-- Categories List --}}
            <div class="border-t border-[#e5e2dc] divide-y divide-[#e5e2dc]">
                <template x-for="(cat, index) in categories" :key="index">
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div x-show="editingIndex !== index" class="flex items-center gap-2.5 text-[15px] text-[#1d1c1b]">
                            <span class="text-[16px]" x-text="cat.icon"></span>
                            <span x-text="cat.name"></span>
                        </div>

                        <div x-show="editingIndex === index" x-cloak class="flex items-center gap-2 flex-1">
                            <input type="text" x-model="draftIcon" class="w-12 px-2 py-1 rounded-[6px] border border-[#d4d0c8] text-center text-[15px]">
                            <input type="text" x-model="draftName" @keydown.enter.prevent="saveEdit(index)" class="px-3 py-1 rounded-[6px] border border-[#3b82f6] text-[14px] text-[#1d1c1b] focus:outline-none w-56">
                            <button type="button" @click="saveEdit(index)" class="px-3 py-1 rounded-[6px] bg-[#1d64c8] text-white text-[12px] font-bold">Save</button>
                            <button type="button" @click="editingIndex = null" class="px-2.5 py-1 rounded-[6px] border border-[#d4d0c8] text-[12px] text-[#5c5955]">Cancel</button>
                        </div>

                        <div x-show="editingIndex !== index" class="flex items-center gap-2">
                            <button type="button" @click="startEdit(index)" title="Edit category" class="p-1 text-[#7c7872] hover:text-[#1d1c1b] transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                            </button>
                            <button type="button" @click="removeCategory(index)" title="Delete category" class="p-1 text-[#dc2626] hover:text-[#b91c1c] transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Add a category row --}}
                <div class="py-3">
                    <button type="button" x-show="!addingNew" @click="addingNew = true"
                        class="flex items-center gap-2.5 text-[15px] text-[#7c7872] hover:text-[#1d1c1b] transition-colors">
                        <span class="w-5 h-5 rounded border border-dashed border-[#c7c3bc] flex items-center justify-center text-[11px]">✨</span>
                        <span>Add a category</span>
                    </button>

                    <div x-show="addingNew" x-cloak class="flex items-center gap-2">
                        <input type="text" x-model="newIcon" placeholder="📌" class="w-12 px-2 py-1 rounded-[6px] border border-[#d4d0c8] text-center text-[15px]">
                        <input type="text" x-model="newName" @keydown.enter.prevent="addCategory()" placeholder="Category name..." class="px-3 py-1 rounded-[6px] border border-[#3b82f6] text-[14px] text-[#1d1c1b] focus:outline-none w-56">
                        <button type="button" @click="addCategory()" class="px-3 py-1 rounded-[6px] bg-[#1d64c8] text-white text-[12px] font-bold">Add</button>
                        <button type="button" @click="addingNew = false" class="px-2.5 py-1 rounded-[6px] border border-[#d4d0c8] text-[12px] text-[#5c5955]">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

