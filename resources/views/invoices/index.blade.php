@extends('layouts.app')

@section('title', isset($project) && $project ? 'Invoices & Billing — ' . $project->name : 'Ajath BillBook — Invoices & GST Billing')

@section('content')
@php
    $iframeParams = ['embed' => '1'];
    if (isset($project) && $project) {
        $iframeParams['project_id'] = $project->id;
        $iframeParams['project_name'] = $project->name;
    } elseif (request()->filled('project_id')) {
        $iframeParams['project_id'] = request('project_id');
        if (request()->filled('project_name')) {
            $iframeParams['project_name'] = request('project_name');
        }
    } elseif (request()->filled('view')) {
        $iframeParams['view'] = request('view');
    }
    if (request()->filled('invoice')) {
        $iframeParams['invoice'] = request('invoice');
    }
    $iframeUrl = 'https://invoice.ajath.com/?' . http_build_query($iframeParams);
    $standaloneUrl = 'https://invoice.ajath.com/?' . http_build_query(array_diff_key($iframeParams, ['embed' => 1]));
@endphp

<div class="w-full pt-2 pb-6">
    <!-- Top Workspace Integration Ribbon -->
    <div class="bg-white border border-[#e5e2dc] rounded-t-[14px] px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-[#4f46e5] text-white font-bold text-xs inline-flex items-center justify-center shadow-2xs">
                ₹
            </span>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[15px] font-bold text-[#1d2d35] leading-tight">
                        Ajath BillBook — GST Billing, Invoicing &amp; Inventory
                    </h1>
                    @if(isset($project) && $project)
                        <a href="{{ route('projects.show', $project->id) }}" class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11.5px] font-semibold hover:bg-emerald-100 transition-colors">
                            ⛰️ {{ $project->name }}
                        </a>
                    @endif
                </div>
                <p class="text-[11.5px] text-[#6d6a65] leading-none mt-0.5">
                    Connected to <strong>workspace.ajath.com</strong> Projects &amp; To-dos &bull; Hosted at <code>invoice.ajath.com</code>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('invoices.index', ['view' => 'workspace']) }}"
               class="px-3 py-1.5 rounded-[7px] text-[12.5px] font-semibold bg-[#f2f0ec] hover:bg-[#e5e2dc] text-[#1d2d35] transition-colors">
                ⛰️ Workspace Projects Billing
            </a>
            <a href="{{ route('invoices.index') }}"
               class="px-3 py-1.5 rounded-[7px] text-[12.5px] font-semibold bg-[#f2f0ec] hover:bg-[#e5e2dc] text-[#1d2d35] transition-colors">
                📊 Billing Dashboard
            </a>
            <a href="{{ $standaloneUrl }}"
               target="_blank"
               rel="noopener"
               class="px-3 py-1.5 rounded-[7px] text-[12.5px] font-semibold bg-[#1b6ac9] hover:bg-[#1557a8] text-white transition-colors inline-flex items-center gap-1.5">
                <span>Open Full Screen (invoice.ajath.com)</span>
                <span>↗</span>
            </a>
        </div>
    </div>

    <!-- Embedded Full-Featured Ajath BillBook App -->
    <div class="bg-white border-x border-b border-[#e5e2dc] rounded-b-[14px] overflow-hidden shadow-sm">
        <iframe
            src="{{ $iframeUrl }}"
            title="Ajath BillBook Invoicing Suite"
            class="w-full border-0"
            style="height: calc(100dvh - 155px); min-height: 680px;"
            allow="clipboard-write"
        ></iframe>
    </div>
</div>
@endsection

