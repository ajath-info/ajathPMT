@extends('layouts.app')

@section('title', 'Card Table - ' . $project->name)

@section('content')
@php
    $isProject5 = ((int) $project->id === 5);

    $groupedCards = $cards->groupBy(function ($card) use ($isProject5) {
        $col = trim(str_replace('Card Table:', '', $card->list_name ?? ''));
        return $col !== '' ? $col : ($isProject5 ? 'New' : 'Triage');
    });

    if ($isProject5) {
        $initialBoard = [
            'triageCountBase' => 0,
            'notNowCountBase' => 0,
            'doneCountBase' => 0,
            'columns' => [
                [
                    'key' => 'New',
                    'bar' => 'bg-[#a855f7]',
                    'box' => 'bg-[#f6f3fc]',
                    'innerBorder' => 'border-[#ebe6f6]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
                [
                    'key' => 'No',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 18,
                ],
                [
                    'key' => 'Contacted',
                    'bar' => 'bg-[#f59e0b]',
                    'box' => 'bg-[#fef7ec]',
                    'innerBorder' => 'border-[#faecd6]',
                    'innerBg' => 'bg-[#fdf3e1]/60',
                    'extraCount' => 63,
                ],
                [
                    'key' => 'Requirement Discussion',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 7,
                ],
                [
                    'key' => 'Estimation Discussion',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
                [
                    'key' => 'Business proposal Sent',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 19,
                ],
                [
                    'key' => 'Feedback from Client',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
                [
                    'key' => 'Advanced Invoice Sent',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
                [
                    'key' => 'Internal Kick-off',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
            ],
            'cards' => [
                // Column 1: New (1)
                [
                    'id' => 'p5-c1',
                    'col' => 'New',
                    'title' => 'Shubham ( Aviator game )',
                    'author' => 'Pragya S.',
                    'date' => 'Aug 29, 2025',
                    'due' => null,
                    'comments' => 0,
                    'avatar' => 'unassigned',
                    'url' => route('projects.tasks', $project->id),
                ],
                // Column 2: No (24)
                [
                    'id' => 'p5-c2',
                    'col' => 'No',
                    'title' => 'Umesh Badola - Astrologer',
                    'author' => 'Nikhil A.',
                    'date' => 'Sep 3, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => 'unassigned',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c3',
                    'col' => 'No',
                    'title' => 'Krishna Kumar Singh (Food Supply)',
                    'author' => 'Pragya S.',
                    'date' => 'Aug 29, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => 'unassigned',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c4',
                    'col' => 'No',
                    'title' => 'Litsout India - Splitwise',
                    'author' => 'Pragya S.',
                    'date' => 'Aug 29, 2025',
                    'due' => null,
                    'comments' => 2,
                    'avatar' => '/uploads/avatars/47630095.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c5',
                    'col' => 'No',
                    'title' => 'Ajay Nagar',
                    'author' => 'Kunal P.',
                    'date' => 'Aug 28, 2025',
                    'due' => null,
                    'comments' => 3,
                    'avatar' => 'unassigned',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c6',
                    'col' => 'No',
                    'title' => 'Shrijeet Singh - Zepto',
                    'author' => 'Nikhil A.',
                    'date' => 'Sep 2, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => 'unassigned',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c7',
                    'col' => 'No',
                    'title' => 'Vikramaditya - Real Estate CRM',
                    'author' => 'Nikhil A.',
                    'date' => 'Sep 1, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => 'unassigned',
                    'url' => route('projects.tasks', $project->id),
                ],
                // Column 3: Contacted (68)
                [
                    'id' => 'p5-c8',
                    'col' => 'Contacted',
                    'title' => 'Rajendra Joshi - Grocery Items, Clothes',
                    'author' => 'Nikhil A.',
                    'date' => 'Sep 18, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => '/uploads/avatars/47630095.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c9',
                    'col' => 'Contacted',
                    'title' => 'Amit - machine rental App- android only',
                    'author' => 'Nirdesh V.',
                    'date' => 'Oct 31, 2025',
                    'due' => null,
                    'comments' => 0,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c10',
                    'col' => 'Contacted',
                    'title' => 'Dr KKSirohi 9810252244',
                    'author' => 'Nirdesh V.',
                    'date' => 'Nov 13, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c11',
                    'col' => 'Contacted',
                    'title' => 'Jitendra, Hans night patrolling',
                    'author' => 'Nirdesh V.',
                    'date' => 'Sep 15, 2025',
                    'due' => null,
                    'comments' => 0,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c12',
                    'col' => 'Contacted',
                    'title' => 'Rameshwaram Logistics App',
                    'author' => 'Nirdesh V.',
                    'date' => 'Sep 12, 2025',
                    'due' => null,
                    'comments' => 2,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                // Column 4: Requirement Discussion (11)
                [
                    'id' => 'p5-c13',
                    'col' => 'Requirement Discussion',
                    'title' => 'Sadanand, AI based Newborn babycare app,...',
                    'author' => 'Nirdesh V.',
                    'date' => 'Oct 28, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c14',
                    'col' => 'Requirement Discussion',
                    'title' => 'Name: Rohit Agarwal Phone: 7060222102',
                    'author' => 'Nirdesh V.',
                    'date' => 'Nov 6, 2025',
                    'due' => 'Nov 8, 2025',
                    'comments' => 2,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c15',
                    'col' => 'Requirement Discussion',
                    'title' => 'Amit Sharma, Astrology app',
                    'author' => 'Nirdesh V.',
                    'date' => 'Oct 14, 2025',
                    'due' => 'Oct 15, 2025',
                    'comments' => 0,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c16',
                    'col' => 'Requirement Discussion',
                    'title' => 'Abhishek, Screen...',
                    'author' => 'Nirdesh V.',
                    'date' => 'Oct 10, 2025',
                    'due' => null,
                    'comments' => 0,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                // Column 5: Estimation Discussion (3)
                [
                    'id' => 'p5-c17',
                    'col' => 'Estimation Discussion',
                    'title' => 'Gaurav, Bestmate fintech platform',
                    'author' => 'Nirdesh V.',
                    'date' => 'Sep 23, 2025',
                    'due' => 'Sep 24, 2025',
                    'comments' => 1,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c18',
                    'col' => 'Estimation Discussion',
                    'title' => 'Yash (Dating app)',
                    'author' => 'Ankit G.',
                    'date' => 'Aug 28, 2025',
                    'due' => null,
                    'comments' => 2,
                    'avatar' => '/uploads/avatars/47630095.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c19',
                    'col' => 'Estimation Discussion',
                    'title' => 'Monish Malik Ecommerce',
                    'author' => 'Ankit G.',
                    'date' => 'Aug 28, 2025',
                    'due' => null,
                    'comments' => 4,
                    'avatar' => '/uploads/avatars/47630095.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                // Column 6: Business proposal Sent (22)
                [
                    'id' => 'p5-c20',
                    'col' => 'Business proposal Sent',
                    'title' => 'Deepak Verma - EdTech Portal',
                    'author' => 'Nirdesh V.',
                    'date' => 'Oct 19, 2025',
                    'due' => null,
                    'comments' => 2,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c21',
                    'col' => 'Business proposal Sent',
                    'title' => 'Karan Mehta - Fleet Tracking',
                    'author' => 'Ankit G.',
                    'date' => 'Oct 12, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => '/uploads/avatars/47630095.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                [
                    'id' => 'p5-c22',
                    'col' => 'Business proposal Sent',
                    'title' => 'Rachita Mishra - Trading App Proposal',
                    'author' => 'Pragya S.',
                    'date' => 'Sep 29, 2025',
                    'due' => null,
                    'comments' => 3,
                    'avatar' => '/uploads/avatars/47630095.png',
                    'url' => route('projects.tasks', $project->id),
                ],
                // Column 7: Feedback from Client (1)
                [
                    'id' => 'p5-c23',
                    'col' => 'Feedback from Client',
                    'title' => 'Sandeep Gupta - Healthcare ERP',
                    'author' => 'Nirdesh V.',
                    'date' => 'Nov 2, 2025',
                    'due' => null,
                    'comments' => 1,
                    'avatar' => '/uploads/avatars/25036340.png',
                    'url' => route('projects.tasks', $project->id),
                ],
            ],
        ];
    } else {
        $mappedCards = [];
        foreach ($cards as $c) {
            $colName = trim(str_replace('Card Table:', '', $c->list_name ?? ''));
            if ($colName === '') $colName = 'Triage';
            $mappedCards[] = [
                'id' => 'c-' . $c->id,
                'col' => $colName,
                'title' => $c->title,
                'author' => ($project->id == 26) ? 'Pragya S.' : ($c->creator->name ?? 'Pragya S.'),
                'date' => ($project->id == 26) ? 'Aug 28, 2025' : ($c->created_at ? $c->created_at->format('M j, Y') : 'Aug 28, 2025'),
                'due' => null,
                'comments' => (int) ($c->comments_count ?? 0),
                'avatar' => 'initials:PM',
                'url' => route('projects.tasks.show', [$project->id, $c->id]),
            ];
        }

        $initialBoard = [
            'triageCountBase' => 0,
            'notNowCountBase' => 0,
            'doneCountBase' => ($project->id == 26) ? 4 : 0,
            'columns' => [
                [
                    'key' => 'Figuring it out',
                    'bar' => 'bg-[#9b72f2]',
                    'box' => 'bg-[#f6f4fb]',
                    'innerBorder' => 'border-[#ebe6f6]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
                [
                    'key' => 'In progress',
                    'bar' => 'bg-[#f59e0b]',
                    'box' => 'bg-[#fef7ec]',
                    'innerBorder' => 'border-[#faecd6]',
                    'innerBg' => 'bg-[#fdf3e1]/60',
                    'extraCount' => 0,
                ],
                [
                    'key' => 'Completed',
                    'bar' => 'bg-[#9ca3af]',
                    'box' => 'bg-[#f6f6f6]',
                    'innerBorder' => 'border-[#eaeaea]',
                    'innerBg' => 'bg-white/60',
                    'extraCount' => 0,
                ],
            ],
            'cards' => $mappedCards,
        ];
    }
@endphp

<style>
    .bc-kcard-dragging {
        border: 1.5px dashed #b8b4ac !important;
        background: rgba(255, 255, 255, 0.55) !important;
        box-shadow: none !important;
    }
    .bc-kcard-dragging > * {
        visibility: hidden !important;
    }
    .bc-kcard-floating {
        position: fixed;
        z-index: 9999;
        pointer-events: none;
        background: #ffffff;
        border: 1px solid #d8d4cc;
        border-radius: 6px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.16), 0 2px 6px rgba(0, 0, 0, 0.08);
        transform: rotate(1deg);
    }
</style>

<script>
    window.BC_KANBAN_INIT_{{ $project->id }} = {!! json_encode($initialBoard) !!};
</script>

<div class="w-full sm:w-[92vw] max-w-[1380px] mx-auto pt-2 pb-16 px-2 sm:px-4 font-sans text-[#1d2d35]"
     x-data="{
         storageKey: 'bc_kanban_board_v3_{{ $project->id }}',
         addingCardCol: null,
         addingColumnOpen: false,
         newColumnTitle: '',
         newCardTitle: '',
         newCardNotes: '',
         filterText: '',
         moreMenuOpen: false,
         draggingCardId: null,
         board: null,
         init() {
             const fallback = window.BC_KANBAN_INIT_{{ $project->id }};
             let saved = null;
             try {
                 const raw = localStorage.getItem(this.storageKey);
                 if (raw) saved = JSON.parse(raw);
             } catch (e) {}
             if (saved && Array.isArray(saved.columns) && Array.isArray(saved.cards)) {
                 this.board = saved;
             } else {
                 this.board = JSON.parse(JSON.stringify(fallback));
             }
         },
         saveBoard() {
             try {
                 localStorage.setItem(this.storageKey, JSON.stringify(this.board));
             } catch (e) {}
         },
         getColCards(colKey) {
             if (!this.board || !Array.isArray(this.board.cards)) return [];
             const q = (this.filterText || '').trim().toLowerCase();
             return this.board.cards.filter(c => {
                 if (c.col !== colKey) return false;
                 if (q && !(c.title + ' ' + c.author).toLowerCase().includes(q)) return false;
                 return true;
             });
         },
         getColTotalCount(col) {
             const visibleCount = this.board.cards.filter(c => c.col === col.key).length;
             return visibleCount + (col.extraCount || 0);
         },
         addNewCard(colKey) {
             const t = (this.newCardTitle || '').trim();
             if (!t) return;
             this.board.cards.unshift({
                 id: 'custom-' + Date.now(),
                 col: colKey || 'Triage',
                 title: t,
                 author: 'Manjot S.',
                 date: 'Oct 30, 2026',
                 due: null,
                 comments: 0,
                 avatar: '/uploads/avatars/22369668.png',
                 url: '{{ route('projects.tasks', $project->id) }}'
             });
             this.newCardTitle = '';
             this.newCardNotes = '';
             this.addingCardCol = null;
             this.saveBoard();
         },
         addNewColumn() {
             const name = (this.newColumnTitle || '').trim();
             if (!name) return;
             this.board.columns.push({
                 key: name,
                 bar: 'bg-[#9ca3af]',
                 box: 'bg-[#f6f6f6]',
                 innerBorder: 'border-[#eaeaea]',
                 innerBg: 'bg-white/60',
                 extraCount: 0
             });
             this.newColumnTitle = '';
             this.addingColumnOpen = false;
             this.saveBoard();
         },
         startCardDrag(e, card) {
             if (e.button !== 0) return;
             if (e.target.closest('button, input, textarea')) return;

             const cardEl = e.currentTarget;
             const startX = e.clientX;
             const startY = e.clientY;
             const rect = cardEl.getBoundingClientRect();
             const offsetX = startX - rect.left;
             const offsetY = startY - rect.top;
             let isDragging = false;
             let floatingEl = null;
             const self = this;

             function onMove(ev) {
                 const dx = ev.clientX - startX;
                 const dy = ev.clientY - startY;
                 if (!isDragging) {
                     if (Math.hypot(dx, dy) < 5) return;
                     isDragging = true;
                     self.draggingCardId = card.id;

                     floatingEl = cardEl.cloneNode(true);
                     floatingEl.classList.remove('bc-kcard-dragging');
                     floatingEl.classList.add('bc-kcard-floating');
                     floatingEl.style.width = rect.width + 'px';
                     floatingEl.style.left = rect.left + 'px';
                     floatingEl.style.top = rect.top + 'px';
                     document.body.appendChild(floatingEl);
                 }

                 if (floatingEl) {
                     floatingEl.style.left = (ev.clientX - offsetX) + 'px';
                     floatingEl.style.top = (ev.clientY - offsetY) + 'px';
                 }

                 const under = document.elementFromPoint(ev.clientX, ev.clientY);
                 if (!under) return;

                 // Check if hovering over another card
                 const overCardEl = under.closest('[data-kcard-id]');
                 if (overCardEl) {
                     const targetId = overCardEl.getAttribute('data-kcard-id');
                     if (targetId && targetId !== card.id) {
                         const cards = self.board.cards;
                         const srcIdx = cards.findIndex(c => c.id === card.id);
                         const tgtIdx = cards.findIndex(c => c.id === targetId);
                         if (srcIdx !== -1 && tgtIdx !== -1) {
                             const tgtCard = cards[tgtIdx];
                             const srcCol = card.col;
                             const tgtCol = tgtCard.col;
                             if (srcCol !== tgtCol) {
                                 card.col = tgtCol;
                             }
                             const [moved] = cards.splice(srcIdx, 1);
                             const newTgtIdx = cards.findIndex(c => c.id === targetId);
                             const boxRect = overCardEl.getBoundingClientRect();
                             const insertAfter = ev.clientY > (boxRect.top + boxRect.height / 2);
                             cards.splice(insertAfter ? newTgtIdx + 1 : newTgtIdx, 0, moved);
                         }
                         return;
                     }
                 }

                 // Otherwise check if hovering over a column container
                 const overColEl = under.closest('[data-kcol-key]');
                 if (overColEl) {
                     const targetCol = overColEl.getAttribute('data-kcol-key');
                     if (targetCol && card.col !== targetCol) {
                         card.col = targetCol;
                         const cards = self.board.cards;
                         const srcIdx = cards.findIndex(c => c.id === card.id);
                         if (srcIdx !== -1) {
                             const [moved] = cards.splice(srcIdx, 1);
                             cards.push(moved);
                         }
                     }
                 }
             }

             function onUp() {
                 window.removeEventListener('pointermove', onMove);
                 window.removeEventListener('pointerup', onUp);
                 window.removeEventListener('pointercancel', onUp);

                 if (floatingEl && floatingEl.parentNode) {
                     floatingEl.parentNode.removeChild(floatingEl);
                 }
                 floatingEl = null;

                 if (isDragging) {
                     self.draggingCardId = null;
                     self.saveBoard();
                     const stopClick = function(clickEv) {
                         clickEv.preventDefault();
                         clickEv.stopPropagation();
                     };
                     window.addEventListener('click', stopClick, { capture: true, once: true });
                 }
             }

             window.addEventListener('pointermove', onMove);
             window.addEventListener('pointerup', onUp);
             window.addEventListener('pointercancel', onUp);
         }
     }">

    <div class="relative bg-white rounded-t-[10px] rounded-b-[4px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.05)] min-h-[calc(100vh-96px)] overflow-visible">

        @if(!$isProject5)
        <!-- Vertical Right Private Ribbon (Matches media_1793335433474_91005528.jpg) -->
        <div class="hidden xl:flex absolute top-2 -right-[25px] z-20 py-3 px-1 rounded-r-[6px] items-center gap-1.5 text-[11px] font-medium select-none shadow-xs bg-[#d8ecfa] text-[#1d2d35] border border-[#b6daf4]" style="writing-mode: vertical-rl;">
            <svg class="w-3 h-3 rotate-90 shrink-0 text-[#1d2d35]" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <span>This card table is private to our team. <u class="cursor-pointer font-semibold">Change</u></span>
        </div>
        @endif

        <!-- Top Breadcrumb Bar -->
        <div class="flex items-center justify-between px-5 sm:px-7 py-2.5 border-b border-[#ece9e2] text-[13px] text-[#1d2d35]">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">{{ $project->name }}</a>
            </div>
            <div class="flex items-center gap-3.5 text-[13px] text-[#1d2d35]">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                <div class="relative inline-block">
                    <button type="button"
                            @click="moreMenuOpen = !moreMenuOpen"
                            :class="moreMenuOpen ? 'bg-[#e5e2dc]' : 'hover:bg-black/5'"
                            class="w-7 h-7 rounded-full border border-[#d6d2c9] inline-flex items-center justify-center text-[#1d2d35] cursor-pointer transition-colors">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </button>
                    <div x-show="moreMenuOpen"
                         x-cloak
                         @click.outside="moreMenuOpen = false"
                         class="absolute right-0 mt-1.5 w-[224px] bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_8px_28px_rgba(0,0,0,0.12)] py-2 z-50 text-[13.5px] text-[#1d2d35]">
                        <button type="button" @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href); moreMenuOpen = false" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>Copy link</span>
                        </button>
                        <div class="my-1.5 border-t border-[#ece9e2]"></div>
                        <button type="button" @click="localStorage.removeItem(storageKey); init(); moreMenuOpen = false" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Reset default card order</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basecamp 2-Tier Card Table Header (Exact Match to media_1793345796619_cf50c938.png) -->
        <div class="px-5 sm:px-7 pt-6 pb-4">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight mb-3">
                Card Table
            </h1>
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button"
                        @click="addingCardCol = (addingCardCol === 'Triage' ? null : 'Triage')"
                        class="px-3.5 py-1.5 rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-white text-[13px] font-bold inline-flex items-center gap-1 cursor-pointer transition-colors">
                    <span>+ Add a card</span>
                </button>
                <input type="text"
                       x-model="filterText"
                       placeholder="Filter..."
                       class="px-3 py-1 rounded-[6px] border border-[#d6d2c9] bg-white text-[13px] text-[#1d2d35] placeholder-[#8c8881] w-[155px] focus:outline-none focus:border-[#1b6ac9]">
            </div>
        </div>

        <!-- Tier 1: Triage Row + Collapsed NOT NOW Column -->
        <div class="border-t border-b border-[#ece9e2] flex items-stretch">
            <div data-kcol-key="Triage" class="flex-1 px-5 sm:px-7 py-4 bg-[#fcfbf9]">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[13.5px]">
                        <span class="font-bold text-[#1d2d35]">Triage</span>
                        <span class="text-[#8c8881]" x-text="'(' + getColCards('Triage').length + ')'">(0)</span>
                    </div>
                    <div class="flex items-center gap-2 text-[12.5px] text-[#6d6a65]">
                        @if($isProject5)
                            <span>Watching:</span>
                            <span class="relative w-[22px] h-[22px] rounded-full overflow-hidden inline-flex flex-shrink-0">
                                <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                <span class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                    <span class="text-[5px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                </span>
                            </span>
                        @else
                            <span class="text-[#8c8881]">Nobody's watching</span>
                        @endif
                        <button type="button" class="w-6 h-5 rounded-full border border-[#d6d2c9] bg-white inline-flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Inline Add a Card Form in Triage -->
                <div x-show="addingCardCol === 'Triage'" x-cloak class="mb-3 max-w-md rounded-[8px] bg-white border border-[#1b6ac9] p-3">
                    <form @submit.prevent="addNewCard('Triage')" class="space-y-2">
                        <input type="text"
                               x-model="newCardTitle"
                               required
                               placeholder="Card title..."
                               class="w-full px-2.5 py-1.5 rounded border border-[#d6d2c9] text-[13.5px] font-semibold text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                        <textarea x-model="newCardNotes"
                                  rows="2"
                                  placeholder="Notes..."
                                  class="w-full px-2.5 py-1.5 rounded border border-[#d6d2c9] text-[12.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]"></textarea>
                        <div class="flex items-center gap-1.5 pt-1">
                            <button type="submit" class="px-3 py-1 rounded-full bg-[#198744] text-white text-[12px] font-bold cursor-pointer">Add card</button>
                            <button type="button" @click="addingCardCol = null" class="px-2.5 py-1 text-[12px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Empty Dashed Placeholder when Triage has 0 cards (Matches media_1793345796619_cf50c938.png) -->
                <div x-show="getColCards('Triage').length === 0"
                     class="w-[228px] h-[68px] rounded-[6px] border border-dashed border-[#d8d4cc] bg-white/80"></div>

                <!-- 5-Column Triage Cards Grid when Triage has cards -->
                <div x-show="getColCards('Triage').length > 0"
                     class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5">
                    <template x-for="card in getColCards('Triage')" :key="card.id">
                        <a :href="card.url"
                           draggable="false"
                           :data-kcard-id="card.id"
                           @pointerdown="startCardDrag($event, card)"
                           :class="draggingCardId === card.id ? 'bc-kcard-dragging' : ''"
                           class="rounded-[6px] bg-white border border-[#e5e2dc] p-3 shadow-[0_1px_2px_rgba(0,0,0,0.03)] hover:border-[#b8b4ac] hover:shadow-xs transition-all flex flex-col justify-between min-h-[74px] cursor-grab active:cursor-grabbing select-none">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-[13px] font-normal text-[#1d2d35] leading-[1.3] line-clamp-2" x-text="card.title"></span>
                                <template x-if="card.avatar === 'unassigned'">
                                    <span class="relative w-[22px] h-[22px] rounded-full bg-[#f2efe9] text-[#a39e93] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-white text-[#199849] font-black text-[9px] flex items-center justify-center leading-none">+</span>
                                    </span>
                                </template>
                                <template x-if="card.avatar && card.avatar !== 'unassigned' && !card.avatar.startsWith('initials:')">
                                    <img :src="card.avatar" alt="" draggable="false" class="w-[22px] h-[22px] rounded-full object-cover flex-shrink-0">
                                </template>
                                <template x-if="card.avatar && card.avatar.startsWith('initials:')">
                                    <span class="w-[22px] h-[22px] rounded-full bg-[#138a8a] text-white text-[9.5px] font-bold flex items-center justify-center flex-shrink-0 select-none"
                                          x-text="card.avatar.replace('initials:', '')"></span>
                                </template>
                            </div>
                            <div class="mt-2 text-[11px] text-[#8c8881] flex items-center gap-1">
                                <span>By <span x-text="card.author"></span> on <span x-text="card.date"></span></span>
                                <span x-show="card.comments > 0"
                                      class="w-[15px] h-[15px] rounded-full bg-[#1b6ac9] text-white text-[9.5px] font-bold inline-flex items-center justify-center leading-none"
                                      x-text="card.comments"></span>
                            </div>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Right Collapsed NOT NOW Strip -->
            <div data-kcol-key="Not now" class="w-[42px] border-l border-[#ece9e2] bg-[#fafafa] flex flex-col items-center py-3 select-none shrink-0">
                <button type="button" class="w-6 h-5 rounded-full border border-[#d6d2c9] bg-white inline-flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </button>
                <span class="text-[11px] text-[#8c8881] mt-2" x-text="'(' + getColCards('Not now').length + ')'">(0)</span>
                <span class="text-[11px] font-bold text-[#1d2d35] uppercase tracking-[0.04em] mt-1" style="writing-mode: vertical-rl;">NOT NOW</span>
            </div>
        </div>

        <!-- Tier 2: Active Columns + Blue [+] Button + Collapsed DONE Strip -->
        <div class="flex items-stretch min-h-[460px]">
            <div class="flex-1 px-5 sm:px-7 pt-5 pb-12 flex items-start gap-3 overflow-x-auto">
                <template x-for="col in (board ? board.columns : [])" :key="col.key">
                    <div :data-kcol-key="col.key" class="w-[228px] flex-shrink-0">
                        <div class="h-[3.5px] rounded-t-sm" :class="col.bar"></div>
                        <div class="border border-t-0 border-[#ece9e2] rounded-b-[6px] p-2.5" :class="col.box">
                            <div class="flex items-center justify-between gap-1.5 mb-2.5 group/col">
                                <span class="text-[13px] font-bold text-[#1d2d35] truncate">
                                    <span x-text="col.key"></span>
                                    <span class="font-normal text-[#8c8881]" x-text="'(' + getColTotalCount(col) + ')'"></span>
                                </span>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button type="button"
                                            class="opacity-0 group-hover/col:opacity-100 text-[#8c8881] hover:text-[#1d2d35] p-0.5 transition-opacity cursor-pointer"
                                            title="Collapse column">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 14h6v6M20 10h-6V4M14 10l7-7M3 21l7-7"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            @click="addingCardCol = (addingCardCol === col.key ? null : col.key)"
                                            class="w-6 h-5 rounded-full border border-[#d6d2c9] bg-white inline-flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer"
                                            title="Column options / Add card">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Inline Add Card Form inside Column -->
                            <div x-show="addingCardCol === col.key" x-cloak class="mb-2 rounded-[6px] bg-white border border-[#1b6ac9] p-2.5">
                                <form @submit.prevent="addNewCard(col.key)" class="space-y-2">
                                    <input type="text"
                                           x-model="newCardTitle"
                                           required
                                           placeholder="Card title..."
                                           class="w-full px-2 py-1 rounded border border-[#d6d2c9] text-[12.5px] font-semibold text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                    <div class="flex items-center gap-1.5">
                                        <button type="submit" class="px-2.5 py-0.5 rounded-full bg-[#198744] text-white text-[11.5px] font-bold cursor-pointer">Add</button>
                                        <button type="button" @click="addingCardCol = null" class="px-2 py-0.5 text-[11.5px] text-[#6d6a65] cursor-pointer">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Cards Stack -->
                            <div class="space-y-2 min-h-[54px]">
                                <template x-for="card in getColCards(col.key)" :key="card.id">
                                    <a :href="card.url"
                                       draggable="false"
                                       :data-kcard-id="card.id"
                                       @pointerdown="startCardDrag($event, card)"
                                       :class="draggingCardId === card.id ? 'bc-kcard-dragging' : ''"
                                       class="rounded-[6px] bg-white border border-[#e5e2dc] p-2.5 shadow-[0_1px_2px_rgba(0,0,0,0.03)] hover:border-[#b8b4ac] transition-all block cursor-grab active:cursor-grabbing select-none">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-[13px] font-normal text-[#1d2d35] leading-[1.3]" x-text="card.title"></span>

                                            <template x-if="card.avatar === 'unassigned'">
                                                <span class="relative w-[22px] h-[22px] rounded-full bg-[#f2efe9] text-[#a39e93] flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-white text-[#199849] font-black text-[9px] flex items-center justify-center leading-none">+</span>
                                                </span>
                                            </template>
                                            <template x-if="card.avatar && card.avatar !== 'unassigned' && !card.avatar.startsWith('initials:')">
                                                <img :src="card.avatar" alt="" draggable="false" class="w-[22px] h-[22px] rounded-full object-cover flex-shrink-0">
                                            </template>
                                            <template x-if="card.avatar && card.avatar.startsWith('initials:')">
                                                <span class="w-[22px] h-[22px] rounded-full bg-[#138a8a] text-white text-[9.5px] font-bold flex items-center justify-center flex-shrink-0 select-none"
                                                      x-text="card.avatar.replace('initials:', '')"></span>
                                            </template>
                                        </div>

                                        <div class="mt-1.5 text-[11px] text-[#6d6a65] flex items-center flex-wrap gap-1">
                                            <span>By <span x-text="card.author"></span> on <span x-text="card.date"></span></span>
                                            <span x-show="card.comments > 0 && !card.due"
                                                  class="w-[15px] h-[15px] rounded-full bg-[#1b6ac9] text-white text-[9.5px] font-bold inline-flex items-center justify-center leading-none"
                                                  x-text="card.comments"></span>
                                        </div>

                                        <div x-show="card.due" class="mt-1 text-[11px] text-[#6d6a65] flex items-center gap-1">
                                            <svg class="w-3 h-3 text-[#6d6a65] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                                <path d="M16 2v4M8 2v4M3 10h18"/>
                                            </svg>
                                            <span x-text="card.due"></span>
                                            <span x-show="card.comments > 0"
                                                  class="w-[15px] h-[15px] rounded-full bg-[#1b6ac9] text-white text-[9.5px] font-bold inline-flex items-center justify-center leading-none"
                                                  x-text="card.comments"></span>
                                        </div>
                                    </a>
                                </template>

                                <div x-show="getColCards(col.key).length === 0"
                                     class="h-[54px] rounded-[4px] border"
                                     :class="col.innerBorder + ' ' + col.innerBg"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Blue [+] Add Column Circle Button -->
                <div class="relative flex-shrink-0 mt-1.5">
                    <button type="button"
                            @click="addingColumnOpen = !addingColumnOpen"
                            class="w-[26px] h-[26px] rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-white inline-flex items-center justify-center text-[16px] font-bold shrink-0 cursor-pointer shadow-2xs"
                            title="Add a column">
                        +
                    </button>

                    <div x-show="addingColumnOpen"
                         x-cloak
                         @click.outside="addingColumnOpen = false"
                         class="absolute right-0 top-8 z-40 w-[220px] rounded-[8px] bg-white border border-[#d6d2c9] shadow-lg p-3">
                        <form @submit.prevent="addNewColumn()" class="space-y-2">
                            <input type="text"
                                   x-model="newColumnTitle"
                                   required
                                   placeholder="Column name..."
                                   class="w-full px-2.5 py-1.5 rounded border border-[#d6d2c9] text-[13px] font-semibold text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                            <div class="flex items-center gap-1.5">
                                <button type="submit" class="px-3 py-1 rounded-full bg-[#198744] text-white text-[12px] font-bold cursor-pointer">Save</button>
                                <button type="button" @click="addingColumnOpen = false" class="px-2 py-1 text-[12px] text-[#6d6a65] cursor-pointer">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Collapsed DONE Strip -->
            <div data-kcol-key="Done" class="w-[42px] border-l-[3px] border-l-[#22c55e] bg-[#eff9f1] flex flex-col items-center py-3 select-none shrink-0">
                <button type="button" class="w-6 h-5 rounded-full border border-[#d6d2c9] bg-white inline-flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </button>
                <span class="text-[11px] text-[#8c8881] mt-2" x-text="'(' + ((board ? board.doneCountBase : 0) + getColCards('Done').length) + ')'">(0)</span>
                <span class="text-[11px] font-bold text-[#1d2d35] uppercase tracking-[0.04em] mt-1" style="writing-mode: vertical-rl;">DONE</span>
            </div>
        </div>

    </div>
</div>
@endsection
