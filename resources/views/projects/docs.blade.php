@extends('layouts.app')

@php
    $folderParam = request()->query('folder');
    $fileParam = request()->query('file');
    $docIdParam = request()->query('doc_id');
    $viewRoot = request()->query('view') === 'root';

    $activeFile = null;
    $activeDoc = null;
    if ($docIdParam) {
        $activeDoc = $docs->firstWhere('id', (int)$docIdParam) ?? \App\Models\Document::with('author')->find($docIdParam);
    }

    $folderItemCounts = [
        'Diwali Celebration 2023' => '30 items',
        'Inauguration Day 2023 kirti nagar' => '343 items',
        'Independence day 2023' => '13 items',
        'Janmaashtami 2022 team outing' => '17 items',
        '4TH Foundation Day Images' => '514 items',
        'Independence Day Celebration 2022' => '79 items',
        'Configuration Management (CM)' => '0 items',
        'Project Planning (PP)' => '2 items',
        'Project Monitoring and Control (PMC)' => '5 items',
        'Ziqqi Somalia Translation' => '1 item',
        'GEM/2026/B/8176205' => '3 items',
        'BID NO: GEM/2026/B/8159428' => '4 items',
    ];

    // Determine active vault/folder for Tender (project 29) or general projects
    if ($project->id == 29 && !$viewRoot && !$activeDoc) {
        if ($folderParam === '8176205' || str_contains((string)$folderParam, '8176205')) {
            $vaultCode = '8176205';
            $vaultTitle = 'GEM/2026/B/8176205';
            $vaultFiles = [
                (object)[
                    'name' => 'UPDC Technical bid.docx.pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 11:17am',
                    'size' => '293 KB',
                    'thumb_url' => '/uploads/docs/thumb_updc_technical.png',
                    'preview_url' => '/uploads/docs/preview_updc_technical.png',
                    'pdf_url' => '/uploads/docs/UPDC Technical bid.docx.pdf',
                    'bid_no' => 'GEM/2026/B/8176205',
                    'authority' => 'Uttar Pradesh Development Systems Corporation Ltd. (UPDC)',
                    'summary' => 'Part-I Technical Bid Proposal, System Architecture, Pre-Qualification Compliance Matrix, and SLA Timelines for UPDC Tender GEM/2026/B/8176205.',
                ],
                (object)[
                    'name' => 'Commercial bid.docx (1).pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 11:17am',
                    'size' => '237 KB',
                    'thumb_url' => '/uploads/docs/thumb_commercial_bid.png',
                    'preview_url' => '/uploads/docs/preview_commercial_bid.png',
                    'pdf_url' => '/uploads/docs/Commercial bid.docx (1).pdf',
                    'bid_no' => 'GEM/2026/B/8176205',
                    'authority' => 'Uttar Pradesh Development Systems Corporation Ltd. (UPDC)',
                    'summary' => 'Part-II Commercial Bid & Financial Proposal containing the itemized Schedule of Rates (BOQ), GST breakdown, and milestone payment schedule.',
                ],
                (object)[
                    'name' => 'GeM-Bidding-10033548.pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 11:17am',
                    'size' => '96.9 KB',
                    'thumb_url' => '/uploads/docs/thumb_gem_bidding.png',
                    'preview_url' => '/uploads/docs/preview_gem_bidding.png',
                    'pdf_url' => '/uploads/docs/GeM-Bidding-10033548.pdf',
                    'bid_no' => 'GEM/2026/B/8176205',
                    'authority' => 'Government e-Marketplace (GeM)',
                    'summary' => 'Official GeM Bid Document specifying Bid End Date/Time, Bid Offer Validity (180 Days), Minimum Average Annual Turnover, Experience Criteria, and ATC clauses.',
                ],
            ];
        } else {
            $vaultCode = $folderParam ?: '8159428';
            $vaultTitle = ($folderParam && $folderParam !== '8159428')
                ? (str_starts_with($folderParam, 'GEM/') || str_starts_with($folderParam, 'BID') ? $folderParam : 'BID NO: GEM/2026/B/' . $folderParam)
                : 'BID NO: GEM/2026/B/8159428';
            $bidRef = str_replace('BID NO: ', '', $vaultTitle);

            $vaultFiles = [
                (object)[
                    'name' => '1791962446.pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 12:00pm',
                    'size' => '813 KB',
                    'thumb_url' => '/uploads/docs/thumb_1791962446.png',
                    'preview_url' => '/uploads/docs/preview_1791962446.png',
                    'pdf_url' => '/uploads/docs/1791962446.pdf',
                    'bid_no' => $bidRef,
                    'authority' => 'Uttar Pradesh Development Systems Corporation Ltd. (UPDC)',
                    'summary' => 'Master GeM Bid Submission Dossier (Ref: 1791962446) containing bidder particulars, EMD verification, statutory certifications, and complete submission checklist.',
                ],
                (object)[
                    'name' => 'Copy of Commercial bid.pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 12:00pm',
                    'size' => '237 KB',
                    'thumb_url' => '/uploads/docs/thumb_commercial_bid.png',
                    'preview_url' => '/uploads/docs/preview_commercial_bid.png',
                    'pdf_url' => '/uploads/docs/Copy of Commercial bid.pdf',
                    'bid_no' => $bidRef,
                    'authority' => 'Uttar Pradesh Development Systems Corporation Ltd. (UPDC)',
                    'summary' => 'Part-II Commercial Bid & Financial Proposal containing the itemized Schedule of Rates (BOQ), GST breakdown, and milestone payment schedule.',
                ],
                (object)[
                    'name' => 'Copy of UPDC Technical bid.pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 12:00pm',
                    'size' => '293 KB',
                    'thumb_url' => '/uploads/docs/thumb_updc_technical.png',
                    'preview_url' => '/uploads/docs/preview_updc_technical.png',
                    'pdf_url' => '/uploads/docs/Copy of UPDC Technical bid.pdf',
                    'bid_no' => $bidRef,
                    'authority' => 'Uttar Pradesh Development Systems Corporation Ltd. (UPDC)',
                    'summary' => 'Part-I UPDC Technical Bid Proposal covering technical stack architecture, pre-qualification eligibility compliance, CERT-In/STQC security standards, and 3-year O&M SLA.',
                ],
                (object)[
                    'name' => 'GeM-Bidding-10033548.pdf',
                    'author' => 'Pawandeep Kaur',
                    'date' => 'Oct 29',
                    'full_date' => 'Oct 29, 2026 at 12:00pm',
                    'size' => '96.9 KB',
                    'thumb_url' => '/uploads/docs/thumb_gem_bidding.png',
                    'preview_url' => '/uploads/docs/preview_gem_bidding.png',
                    'pdf_url' => '/uploads/docs/GeM-Bidding-10033548.pdf',
                    'bid_no' => $bidRef,
                    'authority' => 'Government e-Marketplace (GeM)',
                    'summary' => 'Official GeM Bid Document specifying Bid End Date/Time, Bid Offer Validity (180 Days), Minimum Average Annual Turnover, Experience Criteria, and ATC clauses.',
                ],
            ];
        }

        if ($fileParam) {
            $activeFile = collect($vaultFiles)->first(function ($item) use ($fileParam) {
                return strcasecmp($item->name, $fileParam) === 0 || stripos($item->name, $fileParam) !== false;
            }) ?? $vaultFiles[0];
        }
    } elseif ($folderParam && !$activeDoc) {
        $vaultCode = $folderParam;
        $vaultTitle = $folderParam;

        if ($folderParam === 'Inauguration Day 2023 kirti nagar' || $folderParam === '4TH Foundation Day Images' || $folderParam === 'Independence Day Celebration 2022') {
            $topInaugItems = [
                ['name' => 'DSC_0347.JPG', 'size' => '6.2 MB',  'thumb' => '/uploads/docs/inaug_0347.jpg', 'full' => '/uploads/docs/inaug_0347_full.jpg', 'landscape' => true],
                ['name' => 'DSC_0346.JPG', 'size' => '5.95 MB', 'thumb' => '/uploads/docs/inaug_0346.jpg', 'full' => '/uploads/docs/inaug_0346_full.jpg', 'landscape' => false],
                ['name' => 'DSC_0345.JPG', 'size' => '6.17 MB', 'thumb' => '/uploads/docs/inaug_0345.jpg', 'full' => '/uploads/docs/inaug_0345_full.jpg', 'landscape' => true],
                ['name' => 'DSC_0344.JPG', 'size' => '6.02 MB', 'thumb' => '/uploads/docs/inaug_0344.jpg', 'full' => '/uploads/docs/inaug_0344_full.jpg', 'landscape' => true],
                ['name' => 'DSC_0343.JPG', 'size' => '6.14 MB', 'thumb' => '/uploads/docs/inaug_0347.jpg', 'full' => '/uploads/docs/inaug_0347_full.jpg', 'landscape' => true],
            ];
            $specificInaugMap = [
                316 => ['size' => '6.87 MB', 'thumb' => '/uploads/docs/inaug_0316.jpg', 'full' => '/uploads/docs/inaug_0316_full.jpg', 'landscape' => true],
                315 => ['size' => '6.35 MB', 'thumb' => '/uploads/docs/inaug_0315.jpg', 'full' => '/uploads/docs/inaug_0315_full.jpg', 'landscape' => true],
                314 => ['size' => '6.77 MB', 'thumb' => '/uploads/docs/inaug_0314.jpg', 'full' => '/uploads/docs/inaug_0314_full.jpg', 'landscape' => true],
                313 => ['size' => '6.87 MB', 'thumb' => '/uploads/docs/inaug_0313.jpg', 'full' => '/uploads/docs/inaug_0313_full.jpg', 'landscape' => true],
                312 => ['size' => '6.96 MB', 'thumb' => '/uploads/docs/inaug_0312.jpg', 'full' => '/uploads/docs/inaug_0312_full.jpg', 'landscape' => true],
                311 => ['size' => '5.64 MB', 'thumb' => '/uploads/docs/inaug_0316.jpg', 'full' => '/uploads/docs/inaug_0316_full.jpg', 'landscape' => true],
            ];
            $totalItems = $folderParam === '4TH Foundation Day Images' ? 514 : ($folderParam === 'Independence Day Celebration 2022' ? 79 : 343);
            $dateLabel = $folderParam === '4TH Foundation Day Images' ? 'Nov 18, 2022' : ($folderParam === 'Independence Day Celebration 2022' ? 'Aug 15, 2022' : 'Aug 29, 2023');
            $fullDateLabel = $folderParam === '4TH Foundation Day Images' ? 'November 18, 2022' : ($folderParam === 'Independence Day Celebration 2022' ? 'August 15, 2022' : 'August 29, 2023');
            $vaultFiles = [];
            for ($i = 0; $i < $totalItems - 1; $i++) {
                if ($i < count($topInaugItems)) {
                    $entry = $topInaugItems[$i];
                    $fileName = $entry['name'];
                    $fileSize = $entry['size'];
                    $thumbUrl = $entry['thumb'];
                    $fullUrl = $entry['full'];
                    $isLand = $entry['landscape'];
                } else {
                    $num = max(1, 347 - $i);
                    $fileName = sprintf('DSC_%04d.JPG', $num);
                    if (isset($specificInaugMap[$num])) {
                        $fileSize = $specificInaugMap[$num]['size'];
                        $thumbUrl = $specificInaugMap[$num]['thumb'];
                        $fullUrl = $specificInaugMap[$num]['full'];
                        $isLand = $specificInaugMap[$num]['landscape'];
                    } else {
                        $fallbackSeed = $topInaugItems[$i % count($topInaugItems)];
                        $fileSize = $fallbackSeed['size'];
                        $thumbUrl = $fallbackSeed['thumb'];
                        $fullUrl = $fallbackSeed['full'];
                        $isLand = $fallbackSeed['landscape'];
                    }
                }
                $vaultFiles[] = (object)[
                    'name' => $fileName,
                    'author' => 'Vivek Gupta',
                    'date' => $dateLabel,
                    'full_date' => $fullDateLabel,
                    'size' => $fileSize,
                    'thumb_url' => $thumbUrl,
                    'preview_url' => $fullUrl,
                    'pdf_url' => $fullUrl,
                    'is_photo' => true,
                    'is_video' => false,
                    'is_landscape' => $isLand,
                ];
            }
            // Add DSC_0141.MOV video item (Matches media_1793345979436_1a59376e.png & media_1793374705295_e9c1e9d1.jpg)
            $vaultFiles[] = (object)[
                'name' => 'DSC_0141.MOV',
                'author' => 'Vivek Gupta',
                'date' => $dateLabel,
                'full_date' => $fullDateLabel,
                'size' => '41.4 MB',
                'thumb_url' => '/uploads/docs/inaug_mov_0141.jpg',
                'preview_url' => '/uploads/docs/inaug_mov_0141_full.jpg',
                'pdf_url' => '/uploads/docs/inauguration_0141.mp4?v=6',
                'video_url' => '/uploads/docs/inauguration_0141.mp4?v=6',
                'is_photo' => false,
                'is_video' => true,
                'is_landscape' => true,
            ];
        } elseif ($project->id == 4 || in_array($folderParam, [
            'Diwali Celebration 2023',
            'Independence day 2023',
            'Janmaashtami 2022 team outing',
        ], true)) {
            $diwaliSeed = [
                ['name' => 'IMG_3445.JPG', 'size' => '3.82 MB', 'thumb' => '/uploads/docs/diwali_3445.jpg', 'full' => '/uploads/docs/diwali_3445_full.jpg'],
                ['name' => 'IMG_3446.JPG', 'size' => '4.23 MB', 'thumb' => '/uploads/docs/diwali_3446.jpg', 'full' => '/uploads/docs/diwali_3446_full.jpg'],
                ['name' => 'IMG_3447.JPG', 'size' => '3.9 MB',  'thumb' => '/uploads/docs/diwali_3447.jpg', 'full' => '/uploads/docs/diwali_3447_full.jpg'],
                ['name' => 'IMG_3448.JPG', 'size' => '3.99 MB', 'thumb' => '/uploads/docs/diwali_3448.jpg', 'full' => '/uploads/docs/diwali_3448_full.jpg'],
                ['name' => 'IMG_3449.JPG', 'size' => '3.45 MB', 'thumb' => '/uploads/docs/diwali_3449.jpg', 'full' => '/uploads/docs/diwali_3449_full.jpg'],
            ];
            $extraSizes = ['3.68 MB', '4.12 MB', '3.77 MB', '4.05 MB', '3.54 MB'];
            $totalPhotos = $folderParam === 'Independence day 2023' ? 13 : ($folderParam === 'Janmaashtami 2022 team outing' ? 17 : 30);
            $vaultFiles = [];
            for ($i = 0; $i < $totalPhotos; $i++) {
                $seed = $diwaliSeed[$i % 5];
                $imgNum = 3445 + $i;
                $fileName = $i < 5 ? $seed['name'] : ('IMG_' . $imgNum . '.JPG');
                $fileSize = $i < 5 ? $seed['size'] : $extraSizes[$i % 5];
                $vaultFiles[] = (object)[
                    'name' => $fileName,
                    'author' => 'Kanika Bisht',
                    'date' => 'Nov 10, 2023',
                    'full_date' => 'November 10, 2023',
                    'size' => $fileSize,
                    'thumb_url' => $seed['thumb'],
                    'preview_url' => $seed['full'],
                    'pdf_url' => $seed['full'],
                    'is_photo' => true,
                    'is_video' => false,
                    'is_landscape' => false,
                ];
            }
        } elseif ($folderParam === 'Configuration Management (CM)') {
            $vaultFiles = [];
        } elseif ($folderParam === 'Project Planning (PP)') {
            $vaultFiles = [
                (object)[
                    'name' => 'Project_Plan_and_Schedule_v1.0.pdf',
                    'author' => 'Manjot Singh',
                    'date' => 'Aug 12, 2025',
                    'full_date' => 'August 12, 2025',
                    'size' => '293 KB',
                    'thumb_url' => '/uploads/docs/thumb_updc_technical.png',
                    'preview_url' => '/uploads/docs/preview_updc_technical.png',
                    'pdf_url' => '/uploads/docs/UPDC Technical bid.docx.pdf',
                ],
                (object)[
                    'name' => 'Resource_Allocation_and_Milestones.pdf',
                    'author' => 'Manjot Singh',
                    'date' => 'Aug 14, 2025',
                    'full_date' => 'August 14, 2025',
                    'size' => '237 KB',
                    'thumb_url' => '/uploads/docs/thumb_commercial_bid.png',
                    'preview_url' => '/uploads/docs/preview_commercial_bid.png',
                    'pdf_url' => '/uploads/docs/Commercial bid.docx (1).pdf',
                ],
            ];
        } elseif ($folderParam === 'Project Monitoring and Control (PMC)') {
            $vaultFiles = [
                (object)[
                    'name' => 'PMC_Sprint_Review_Report.pdf',
                    'author' => 'Manjot Singh',
                    'date' => 'Sep 2, 2025',
                    'full_date' => 'September 2, 2025',
                    'size' => '293 KB',
                    'thumb_url' => '/uploads/docs/thumb_updc_technical.png',
                    'preview_url' => '/uploads/docs/preview_updc_technical.png',
                    'pdf_url' => '/uploads/docs/UPDC Technical bid.docx.pdf',
                ],
                (object)[
                    'name' => 'Quality_Assurance_Signoff.pdf',
                    'author' => 'Manjot Singh',
                    'date' => 'Sep 5, 2025',
                    'full_date' => 'September 5, 2025',
                    'size' => '237 KB',
                    'thumb_url' => '/uploads/docs/thumb_commercial_bid.png',
                    'preview_url' => '/uploads/docs/preview_commercial_bid.png',
                    'pdf_url' => '/uploads/docs/Commercial bid.docx (1).pdf',
                ],
                (object)[
                    'name' => 'Risk_and_Issue_Tracker.pdf',
                    'author' => 'Manjot Singh',
                    'date' => 'Sep 9, 2025',
                    'full_date' => 'September 9, 2025',
                    'size' => '96.9 KB',
                    'thumb_url' => '/uploads/docs/thumb_gem_bidding.png',
                    'preview_url' => '/uploads/docs/preview_gem_bidding.png',
                    'pdf_url' => '/uploads/docs/GeM-Bidding-10033548.pdf',
                ],
                (object)[
                    'name' => 'Architecture_Walkthrough.JPG',
                    'author' => 'Manjot Singh',
                    'date' => 'Sep 11, 2025',
                    'full_date' => 'September 11, 2025',
                    'size' => '3.82 MB',
                    'thumb_url' => '/uploads/docs/diwali_3445.jpg',
                    'preview_url' => '/uploads/docs/diwali_3445_full.jpg',
                    'pdf_url' => '/uploads/docs/diwali_3445_full.jpg',
                    'is_photo' => true,
                ],
                (object)[
                    'name' => 'Sprint_Demo_Recording.MOV',
                    'author' => 'Manjot Singh',
                    'date' => 'Sep 12, 2025',
                    'full_date' => 'September 12, 2025',
                    'size' => '14.5 MB',
                    'thumb_url' => '/uploads/docs/inaug_mov_0141.jpg',
                    'preview_url' => '/uploads/docs/inaug_0316_full.jpg',
                    'pdf_url' => '/uploads/docs/sample_video_preview.mp4',
                    'video_url' => '/uploads/docs/sample_video_preview.mp4',
                    'is_video' => true,
                    'is_landscape' => true,
                ],
            ];
        } else {
            $vaultFiles = [
                (object)[
                    'name' => $folderParam . ' - Overview.pdf',
                    'author' => 'Manjot Singh',
                    'date' => 'Aug 12, 2025',
                    'full_date' => 'August 12, 2025',
                    'size' => '293 KB',
                    'thumb_url' => '/uploads/docs/thumb_updc_technical.png',
                    'preview_url' => '/uploads/docs/preview_updc_technical.png',
                    'pdf_url' => '/uploads/docs/UPDC Technical bid.docx.pdf',
                ],
                (object)[
                    'name' => $folderParam . ' - Screen.JPG',
                    'author' => 'Manjot Singh',
                    'date' => 'Aug 14, 2025',
                    'full_date' => 'August 14, 2025',
                    'size' => '3.82 MB',
                    'thumb_url' => '/uploads/docs/diwali_3445.jpg',
                    'preview_url' => '/uploads/docs/diwali_3445_full.jpg',
                    'pdf_url' => '/uploads/docs/diwali_3445_full.jpg',
                    'is_photo' => true,
                ],
                (object)[
                    'name' => $folderParam . ' - Walkthrough.MOV',
                    'author' => 'Manjot Singh',
                    'date' => 'Aug 15, 2025',
                    'full_date' => 'August 15, 2025',
                    'size' => '18.4 MB',
                    'thumb_url' => '/uploads/docs/inaug_mov_0141.jpg',
                    'preview_url' => '/uploads/docs/inaug_0316_full.jpg',
                    'pdf_url' => '/uploads/docs/sample_video_preview.mp4',
                    'video_url' => '/uploads/docs/sample_video_preview.mp4',
                    'is_video' => true,
                    'is_landscape' => true,
                ],
            ];
        }

        if ($fileParam && !empty($vaultFiles)) {
            $activeFile = collect($vaultFiles)->first(function ($item) use ($fileParam) {
                return strcasecmp($item->name, $fileParam) === 0 || stripos($item->name, $fileParam) !== false;
            }) ?? $vaultFiles[0];
        }
    } else {
        $vaultCode = null;
        $vaultTitle = $activeDoc ? $activeDoc->title : ($folderParam ?: 'Docs & Files');
        $vaultFiles = null;
    }

    $vaultItemsArray = [];
    if ($vaultFiles !== null) {
        foreach (array_values($vaultFiles) as $idx => $f) {
            $rawExt = strtolower(pathinfo($f->name, PATHINFO_EXTENSION) ?: 'pdf');
            $ext = preg_replace('/^(pdf|mov|jpg|jpeg|png|gif|docx|xlsx|pptx|amr|wav)\d+$/i', '$1', $rawExt);
            $isPhoto = !empty($f->is_photo) || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true);
            $isVideo = !empty($f->is_video) || in_array($ext, ['mov', 'mp4', 'webm', 'mkv', 'avi', 'm4v'], true);
            $vaultItemsArray[] = [
                'idx' => $idx,
                'name' => $f->name,
                'author' => $f->author,
                'date' => $f->date,
                'size' => $f->size,
                'ext' => $ext,
                'is_photo' => $isPhoto,
                'is_video' => $isVideo,
                'is_landscape' => !empty($f->is_landscape),
                'thumb_url' => $f->thumb_url,
                'preview_url' => $f->preview_url,
                'pdf_url' => $f->pdf_url,
                'video_url' => $f->video_url ?? ($isVideo ? '/uploads/docs/sample_video_preview.mp4' : null),
                'page_url' => route('projects.docs', ['id' => $project->id, 'folder' => $vaultCode, 'file' => $f->name]),
            ];
        }
    }

    $rootExts = [];
    $hasRootFolders = false;
    $hasImagesInView = false;
    foreach ($docs as $dItem) {
        $tTitle = trim($dItem->title);
        $rExt = strtolower(pathinfo($tTitle, PATHINFO_EXTENSION));
        $rExt = preg_replace('/^(pdf|mov|jpg|jpeg|png|gif|docx|xlsx|pptx|amr|wav)\d+$/i', '$1', $rExt);
        $dIsFolder = array_key_exists($tTitle, $folderItemCounts)
            || str_contains($tTitle, 'GEM/')
            || str_contains($tTitle, 'BID NO:')
            || ($project->id == 4 && $rExt === '');
        if ($dIsFolder) {
            $hasRootFolders = true;
        }
        if (in_array($rExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true)) {
            $hasImagesInView = true;
        }
        if ($rExt !== '' && !in_array($rExt, $rootExts, true)) {
            $rootExts[] = $rExt;
        }
    }
    if (!empty($vaultItemsArray)) {
        foreach ($vaultItemsArray as $vItem) {
            if (!empty($vItem['is_photo'])) {
                $hasImagesInView = true;
                break;
            }
        }
    }
    if (empty($rootExts)) {
        $rootExts = ['pdf', 'jpg', 'mov'];
    }
    $initialFileType = strtolower((string)request()->query('file_type', 'all'));
@endphp

@section('title', ($activeFile ? $activeFile->name : $vaultTitle) . ' - ' . $project->name)

@section('content')
<script>
    window.BC_VAULT_ITEMS = {!! json_encode($vaultItemsArray) !!};
    window.BC_ROOT_EXTS = {!! json_encode($rootExts) !!};
</script>
<div x-data="{ 
    newDocModal: false,
    newMenuOpen: false,
    newDocMode: 'doc',
    externalService: '',
    docFilter: '',
    viewMode: 'list',
    fileTypeMenu: false,
    fileTypeFilter: '{{ addslashes($initialFileType ?: 'all') }}',
    sortMenu: false,
    sortBy: 'manual',
    keepFoldersOpen: false,
    vaultItems: window.BC_VAULT_ITEMS || [],
    get availableFileTypes() {
        if (this.vaultItems && this.vaultItems.length > 0) {
            const set = [];
            this.vaultItems.forEach(function(item) {
                if (item.ext && !set.includes(item.ext)) set.push(item.ext);
            });
            return set.length ? set : ['pdf', 'jpg', 'mov'];
        }
        return (window.BC_ROOT_EXTS && window.BC_ROOT_EXTS.length) ? window.BC_ROOT_EXTS : ['pdf', 'jpg', 'mov'];
    },
    get sortedVaultItems() {
        const q = (this.docFilter || '').trim().toLowerCase();
        const ft = this.fileTypeFilter;
        const items = this.vaultItems.filter(function(item) {
            if (ft === 'images') {
                if (!item.is_photo) return false;
            } else if (ft !== 'all' && item.ext !== ft) {
                return false;
            }
            if (q && !(item.name + ' ' + item.author).toLowerCase().includes(q)) return false;
            return true;
        });
        const mode = this.sortBy;
        return items.slice().sort(function(a, b) {
            if (mode === 'name') return a.name.localeCompare(b.name);
            if (mode === 'created') return b.idx - a.idx;
            if (mode === 'modified') return a.size.localeCompare(b.size);
            return a.idx - b.idx;
        });
    },
    pdfLightbox: false,
    pdfLightboxClosing: false,
    pdfSidebar: true,
    pdfPage: 1,
    pdfZoom: 100,
    pdfRotate: 0,
    lightboxFile: {
        name: '{{ addslashes($activeFile->name ?? 'Copy of Commercial bid.pdf') }}',
        size: '{{ addslashes($activeFile->size ?? '237 KB') }}',
        preview_url: '{{ addslashes($activeFile->preview_url ?? '/uploads/docs/preview_commercial_bid.png') }}',
        pdf_url: '{{ addslashes($activeFile->pdf_url ?? '/uploads/docs/Copy of Commercial bid.pdf') }}',
        video_url: '{{ addslashes($activeFile->video_url ?? '/uploads/docs/sample_video_preview.mp4') }}',
        page_url: '{{ $activeFile ? route('projects.docs', ['id' => $project->id, 'folder' => $vaultCode, 'file' => $activeFile->name]) : route('projects.docs', ['id' => $project->id, 'folder' => $vaultCode ?: '8159428', 'file' => 'Copy of Commercial bid.pdf']) }}',
        is_photo: {{ !empty($activeFile->is_photo) ? 'true' : 'false' }},
        is_video: {{ !empty($activeFile->is_video) ? 'true' : 'false' }}
    },
    isPhotoFile(f) {
        if (!f) return false;
        if (f.is_photo) return true;
        return /\.(jpg|jpeg|png|gif|webp|svg|bmp)\d*$/i.test(f.name || '');
    },
    isVideoFile(f) {
        if (!f) return false;
        if (f.is_video) return true;
        return /\.(mov|mp4|webm|mkv|avi|m4v)\d*$/i.test(f.name || '');
    },
    get currentLightboxIdx() {
        const list = this.sortedVaultItems;
        if (!list || !list.length || !this.lightboxFile) return -1;
        return list.findIndex(item => item.name === this.lightboxFile.name);
    },
    nextLightboxItem() {
        const list = this.sortedVaultItems;
        const idx = this.currentLightboxIdx;
        if (idx >= 0 && idx < list.length - 1) {
            this.openPdfViewer(list[idx + 1]);
        }
    },
    prevLightboxItem() {
        const list = this.sortedVaultItems;
        const idx = this.currentLightboxIdx;
        if (idx > 0) {
            this.openPdfViewer(list[idx - 1]);
        }
    },
    openPdfViewer(f) {
        const isImg = this.isPhotoFile(f);
        const isVid = this.isVideoFile(f);
        this.lightboxFile = Object.assign({}, f, {
            is_photo: isImg,
            is_video: isVid,
            video_url: f.video_url || (isVid ? '/uploads/docs/sample_video_preview.mp4' : null)
        });
        this.pdfPage = 1;
        this.pdfZoom = 100;
        this.pdfRotate = 0;
        this.pdfLightboxClosing = false;
        this.pdfLightbox = true;
        if (!isImg && !isVid) {
            this.preloadPdfPrintFrame(f.pdf_url);
        }
    },
    closePdfViewer() {
        if (!this.pdfLightbox || this.pdfLightboxClosing) return;
        this.pdfLightboxClosing = true;
        setTimeout(() => {
            this.pdfLightbox = false;
            this.pdfLightboxClosing = false;
        }, 190);
    },
    preloadPdfPrintFrame(pdfUrl) {
        if (!pdfUrl) return;
        let frame = document.getElementById('bc-pdf-print-frame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'bc-pdf-print-frame';
            frame.style.cssText = 'position:fixed;right:0;bottom:0;width:1px;height:1px;border:0;opacity:0;pointer-events:none;z-index:-1;';
            document.body.appendChild(frame);
        }
        const cleanUrl = pdfUrl.split('?')[0] + '?v=5';
        if (frame.getAttribute('data-pdf-src') !== cleanUrl) {
            frame.setAttribute('data-pdf-src', cleanUrl);
            frame.setAttribute('data-ready', '0');
            frame.onload = function() {
                frame.setAttribute('data-ready', '1');
            };
            frame.src = cleanUrl;
        }
    },
    printPdfDocument(pdfUrl) {
        const rawUrl = (pdfUrl || (this.lightboxFile && this.lightboxFile.pdf_url) || '').split('?')[0];
        if (!rawUrl) {
            window.print();
            return;
        }
        const targetUrl = rawUrl + '?v=5';
        let frame = document.getElementById('bc-pdf-print-frame');
        if (!frame) {
            this.preloadPdfPrintFrame(targetUrl);
            frame = document.getElementById('bc-pdf-print-frame');
        }
        const triggerPrint = function() {
            try {
                if (frame && frame.contentWindow) {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                    return;
                }
            } catch (e) {}
            window.print();
        };
        if (frame.getAttribute('data-pdf-src') === targetUrl && frame.getAttribute('data-ready') === '1') {
            triggerPrint();
        } else {
            frame.setAttribute('data-pdf-src', targetUrl);
            frame.setAttribute('data-ready', '0');
            frame.onload = function() {
                frame.setAttribute('data-ready', '1');
                setTimeout(triggerPrint, 120);
            };
            frame.src = targetUrl;
        }
    },
    scrollToPdfPage(p) {
        this.pdfPage = p;
        const el = document.getElementById('pdf-viewer-page-' + p);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    commenting: false,
    notifyMuted: false,
    editDocSubscribersOpen: false,
    notifyDocNewMode: 'next_comment',
    docSubscribers: JSON.parse(localStorage.getItem('bc_doc_subs_{{ $project->id }}') || 'null') || [
        { id: 1, name: 'Manjot Singh', role: 'Global VP & Partner', initials: 'M', bg: 'bg-[#1d4ed8]', avatar: '/uploads/avatars/22369668.png', isOoo: true, active: true },
        { id: 60, name: 'Shachish Sneh', role: 'Solution Architect & Technology Master', initials: 'SS', bg: 'bg-[#4f46e5]', avatar: '/uploads/avatars/25036340.png', isOoo: false, active: true },
        { id: 2, name: 'Pawandeep Kaur', role: 'Manager', initials: 'PK', bg: 'bg-[#00897b]', avatar: null, isOoo: false, active: true },
        { id: 3, name: 'Prashant', role: 'Developer', initials: 'P', bg: 'bg-[#0284c7]', avatar: '/uploads/avatars/22369670.png', isOoo: false, active: true },
        { id: 5, name: 'Rajesh Kumar', role: 'Developer', initials: 'RK', bg: 'bg-[#d81b60]', avatar: null, isOoo: false, active: true },
        { id: 4, name: 'Gaurav Khulbey', role: 'Cybersecurity Expert', initials: 'GK', bg: 'bg-[#d946ef]', avatar: null, isOoo: false, active: false },
        { id: 62, name: 'Shiv Narayan', role: 'Developer', initials: 'SN', bg: 'bg-[#9333ea]', avatar: null, isOoo: false, active: false }
    ],
    saveDocSubscribers() {
        localStorage.setItem('bc_doc_subs_{{ $project->id }}', JSON.stringify(this.docSubscribers));
        this.editDocSubscribersOpen = false;
    }
}"
@keydown.escape.window="pdfLightbox = false"
@keydown.arrow-right.window="if (pdfLightbox && lightboxFile.is_photo) { $event.preventDefault(); nextLightboxItem(); }"
@keydown.arrow-left.window="if (pdfLightbox && lightboxFile.is_photo) { $event.preventDefault(); prevLightboxItem(); }"
@keydown.meta.p.window="if ((pdfLightbox || {{ $activeFile ? 'true' : 'false' }}) && !lightboxFile.is_photo) { $event.preventDefault(); printPdfDocument(lightboxFile.pdf_url); }"
@keydown.ctrl.p.window="if ((pdfLightbox || {{ $activeFile ? 'true' : 'false' }}) && !lightboxFile.is_photo) { $event.preventDefault(); printPdfDocument(lightboxFile.pdf_url); }"
class="max-w-[920px] mx-auto pb-20 bc-docs-wrapper">

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 0;
    }
    body, html {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    nav, header, footer, .bc-no-print, .bc-pdf-toolbar, .bc-pdf-sidebar, .bc-pdf-close, .bc-pdf-caption, .bc-main-sheet {
        display: none !important;
    }
    .bc-docs-wrapper {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .bc-pdf-lightbox {
        position: static !important;
        display: block !important;
        padding: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
    }
    .bc-pdf-chrome {
        max-width: 100% !important;
        background: #ffffff !important;
        box-shadow: none !important;
        overflow: visible !important;
        display: block !important;
    }
    .bc-pdf-body {
        display: block !important;
        overflow: visible !important;
    }
    .bc-pdf-canvas {
        background: #ffffff !important;
        overflow: visible !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
    }
    .bc-pdf-page {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        box-shadow: none !important;
        page-break-after: always !important;
        break-after: page !important;
    }
    .bc-pdf-page:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
    }
}
</style>

    <!-- Basecamp White Paper Sheet -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-96px)] overflow-hidden bc-main-sheet">

        <!-- Top Breadcrumb & Actions Bar -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center justify-between text-[13.5px] bg-white sticky top-0 z-10">
            <!-- Left Breadcrumb -->
            @php
                $isAtRootDocs = !$activeFile && !$activeDoc && !$folderParam && ($project->id != 29 || $viewRoot);
            @endphp
            <div class="flex items-center gap-1.5 truncate">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] {{ ($activeDoc || $isAtRootDocs || $folderParam) ? 'hover:underline' : 'underline decoration-[#8c8881] hover:text-black' }}">
                    {{ $project->name }}
                </a>
                @if(!$isAtRootDocs)
                    <span class="text-[#6d6a65] text-[12px]">‹</span>
                    <a href="{{ route('projects.docs', $project->id == 29 ? ['id' => $project->id, 'view' => 'root'] : ['id' => $project->id]) }}" class="text-[#1d2d35] {{ ($activeDoc || $folderParam) ? 'hover:underline' : 'underline decoration-[#8c8881] hover:text-black' }}">
                        Docs & Files
                    </a>
                @endif
                @if($activeFile && $vaultTitle)
                    <span class="text-[#6d6a65] text-[12px]">‹</span>
                    <a href="{{ route('projects.docs', ['id' => $project->id, 'folder' => $vaultCode]) }}" class="text-[#1d2d35] underline decoration-[#8c8881] hover:text-black truncate">
                        {{ $vaultTitle }}
                    </a>
                @endif
            </div>

            <!-- Right: Bookmark, Edit & Menu -->
            <div class="flex items-center gap-3.5 text-[#1d2d35] flex-shrink-0">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[13px] text-[#1d2d35] hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                @if($activeFile || $activeDoc)
                    <button type="button" @click="commenting = true" class="text-[13px] text-[#1d2d35] hover:underline cursor-pointer">
                        Edit
                    </button>
                @endif
                <button type="button" class="p-1 text-[#1d2d35] hover:bg-slate-100 rounded-md transition-colors cursor-pointer" title="More options">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </button>
            </div>
        </div>

        @if($activeFile)
            @php
                $afRawExt = strtolower(pathinfo($activeFile->name, PATHINFO_EXTENSION));
                $afCleanExt = preg_replace('/^(pdf|mov|jpg|jpeg|png|gif|docx|xlsx|pptx|amr|wav)\d+$/i', '$1', $afRawExt);
                $isActiveFilePhoto = !empty($activeFile->is_photo) || in_array($afCleanExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true);
                $isActiveFileVideo = !empty($activeFile->is_video) || in_array($afCleanExt, ['mov', 'mp4', 'webm', 'mkv', 'avi', 'm4v'], true);
                $afVideoSrc = $activeFile->video_url ?? '/uploads/docs/sample_video_preview.mp4';
                $afAuthorFull = $activeFile->author ?? 'Pawandeep Kaur';
                $afParts = explode(' ', trim($afAuthorFull));
                $afAuthorShort = count($afParts) > 1 ? ($afParts[0] . ' ' . strtoupper(substr(end($afParts), 0, 1)) . '.') : $afAuthorFull;
                $afInitials = collect($afParts)->map(fn($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('');
                $afBg = str_contains($afAuthorFull, 'Kanika') ? 'bg-[#7c3aed]' : (str_contains($afAuthorFull, 'Vivek') ? 'bg-[#ef4444]' : 'bg-[#00897b]');
                $afDateDisplay = $activeFile->full_date ?? $activeFile->date ?? 'October 29, 2026';
            @endphp
            <!-- ==================================================================== -->
            <!-- BASECAMP FILE PREVIEW & DETAIL VIEW (/projects/29/docs?file=...)     -->
            <!-- ==================================================================== -->
            <div class="px-8 sm:px-14 pt-8 pb-16">

                <!-- 1. File Title & Posted By Sub-Header -->
                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-[1.15]">
                        {{ $activeFile->name }}
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[15.5px] text-[#1d2d35]">
                        <span>Posted by</span>
                        <span class="w-[22px] h-[22px] rounded-full {{ $afBg }} text-white font-bold text-[10px] inline-flex items-center justify-center select-none mx-0.5">{{ $afInitials }}</span>
                        <span>{{ $afAuthorShort }}</span>
                        <span class="text-[#6d6a65]">•</span>
                        <span>{{ $afDateDisplay }}</span>
                        <span class="text-[#6d6a65]">•</span>
                        <span>{{ $activeFile->size }}</span>
                    </div>
                </div>

                <!-- 2. Video, Image, or Document Page Preview directly on the white paper sheet -->
                <div class="mt-7">
                    @if($isActiveFileVideo)
                        <div class="rounded-[8px] overflow-hidden bg-black max-w-[680px] shadow-sm border border-[#d8d4cc]">
                            <video controls playsinline preload="metadata" poster="{{ $activeFile->preview_url }}?v=6" src="{{ $afVideoSrc }}{{ str_contains($afVideoSrc, '?') ? '' : '?v=6' }}" class="w-full max-h-[420px] object-contain bg-black">
                                Your browser does not support HTML5 video playback.
                            </video>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3 text-[13px]">
                            <a href="{{ $afVideoSrc }}"
                               download="{{ $activeFile->name }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                </svg>
                                <span>Download</span>
                            </a>
                            <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Replace with a new version</span>
                                <input type="file" class="hidden">
                            </label>
                        </div>
                    @else
                        <div @click="openPdfViewer({
                                name: '{{ addslashes($activeFile->name) }}',
                                size: '{{ addslashes($activeFile->size) }}',
                                preview_url: '{{ addslashes($activeFile->preview_url) }}',
                                pdf_url: '{{ addslashes($activeFile->pdf_url) }}',
                                page_url: '{{ route('projects.docs', ['id' => $project->id, 'folder' => $vaultCode, 'file' => $activeFile->name]) }}',
                                is_photo: {{ $isActiveFilePhoto ? 'true' : 'false' }},
                                is_video: false
                             })"
                             class="inline-block max-w-full {{ $isActiveFilePhoto ? 'sm:max-w-[540px]' : 'sm:max-w-[680px]' }} cursor-zoom-in group relative"
                             title="{{ $isActiveFilePhoto ? 'Click to view image in lightbox' : 'Click to open interactive multi-page PDF viewer' }}">
                            <img src="{{ $activeFile->preview_url }}?v=5"
                                 alt="{{ $activeFile->name }}"
                                 class="w-full h-auto bg-white rounded-[2px] object-contain">
                        </div>

                        @if($isActiveFilePhoto)
                            <!-- Basecamp Image Action Row -->
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-[13px]">
                                <a href="{{ $activeFile->pdf_url }}?v=5"
                                   download="{{ $activeFile->name }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                    </svg>
                                    <span>Download</span>
                                </a>
                                <a href="{{ $activeFile->pdf_url }}?v=5"
                                   target="_blank"
                                   class="text-[#1d2d35] underline hover:text-[#1b6ac9]">
                                    View full-size
                                </a>
                                <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Replace with a new version</span>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                        @else
                            <!-- Subtle Action Row Below Document Preview -->
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-[13.5px] text-[#6d6a65]">
                                <button type="button"
                                        @click="openPdfViewer({
                                            name: '{{ addslashes($activeFile->name) }}',
                                            size: '{{ addslashes($activeFile->size) }}',
                                            preview_url: '{{ addslashes($activeFile->preview_url) }}',
                                            pdf_url: '{{ addslashes($activeFile->pdf_url) }}',
                                            page_url: '{{ route('projects.docs', ['id' => $project->id, 'folder' => $vaultCode, 'file' => $activeFile->name]) }}',
                                            is_photo: false,
                                            is_video: false
                                        })"
                                        class="underline hover:text-[#1d2d35] font-medium cursor-pointer">
                                    Open PDF Preview (5 pages)
                                </button>
                                <span>·</span>
                                <a href="{{ $activeFile->pdf_url }}?v=2"
                                   download="{{ $activeFile->name }}"
                                   class="underline hover:text-[#1d2d35] font-medium">
                                    Download ({{ $activeFile->size }})
                                </a>
                                <span>·</span>
                                <a href="{{ $activeFile->pdf_url }}?v=2"
                                   target="_blank"
                                   class="underline hover:text-[#1d2d35] font-medium">
                                    Open raw PDF in new tab ↗
                                </a>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Basecamp Add Comment Row -->
                <div class="border-t border-[#ece9e2] mt-8 pt-6">
                    <div class="flex items-start gap-3.5">
                        <img src="{{ auth()->user()->avatar_url && !str_contains(auth()->user()->avatar_url, 'dicebear') ? auth()->user()->avatar_url : '/uploads/avatars/22369668.png' }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-[44px] h-[44px] rounded-full object-cover flex-shrink-0 mt-0.5">

                        <div class="flex-1 min-w-0">
                            <div x-show="!commenting"
                                 @click="commenting = true"
                                 class="w-full rounded-[8px] border border-[#d8d4cc] bg-white px-4 py-3 text-[16px] text-[#8c8881] cursor-text hover:border-[#b8b4ac] transition-colors">
                                Add your comment...
                            </div>

                            <div x-show="commenting" x-cloak class="space-y-3">
                                <div class="rounded-[8px] border border-[#1b6ac9] bg-white overflow-hidden">
                                    <textarea rows="4" placeholder="Add your comment..." class="w-full px-4 py-3 text-[16px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none resize-y"></textarea>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="commenting = false" class="px-4 py-1.5 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white font-bold text-[13.5px] cursor-pointer">
                                        Add this comment
                                    </button>
                                    <button type="button" @click="commenting = false" class="px-3.5 py-1.5 rounded-full border border-[#d8d4cc] bg-white text-[13.5px] text-[#1d2d35] cursor-pointer">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basecamp Subscribers Notification Footer -->
                <div class="border-t border-[#ece9e2] mt-10 pt-5">
                    <p class="text-[14px] text-[#1d2d35] mb-2.5">
                        <span x-text="docSubscribers.filter(s => s.active).length">5</span> people will be notified when someone comments on this file.
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <template x-for="sub in docSubscribers.filter(s => s.active)" :key="sub.id">
                            <span class="relative w-[30px] h-[30px] rounded-full overflow-hidden flex-shrink-0 inline-flex items-center justify-center" :title="sub.name">
                                <template x-if="sub.avatar">
                                    <img :src="sub.avatar" :alt="sub.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!sub.avatar">
                                    <span class="w-full h-full text-white font-bold text-[11px] flex items-center justify-center select-none" :class="sub.bg" x-text="sub.initials"></span>
                                </template>
                                <span x-show="sub.isOoo && localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                    <span class="text-[6px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                </span>
                            </span>
                        </template>

                        <button type="button"
                                @click="editDocSubscribersOpen = !editDocSubscribersOpen"
                                class="ml-1 px-3.5 py-1 rounded-full border border-[#d8d4cc] bg-white hover:bg-[#fbfaf8] text-[13px] text-[#1d2d35] transition-colors cursor-pointer">
                            Add/remove people...
                        </button>
                        <button type="button"
                                @click="notifyMuted = !notifyMuted; docSubscribers[0].active = !notifyMuted; saveDocSubscribers()"
                                class="px-3.5 py-1 rounded-full border border-[#d8d4cc] bg-white hover:bg-[#fbfaf8] text-[13px] text-[#1d2d35] inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                            <span x-text="notifyMuted ? 'Notify me about new comments' : 'Don\'t notify me'">Don't notify me</span>
                        </button>
                    </div>

                    <div x-show="editDocSubscribersOpen" x-cloak class="mt-6 pt-6 border-t border-[#ece9e2] max-w-[540px]">
                        <h2 class="text-[22px] font-extrabold text-[#1d2d35] leading-tight">
                            Who should be notified when someone comments on this?
                        </h2>
                        <div class="mt-2 text-[13.5px]">
                            <button type="button" @click="docSubscribers.forEach(s => s.active = true)" class="text-[#1b6ac9] underline cursor-pointer">Select everyone</button>
                            <span class="mx-1">•</span>
                            <button type="button" @click="docSubscribers.forEach(s => s.active = false)" class="text-[#1b6ac9] underline cursor-pointer">Select no one</button>
                        </div>
                        <div class="mt-4 mb-1.5 text-[14px] font-bold text-[#1d2d35]">Ajath Infotech Pvt Ltd</div>
                        <div class="divide-y divide-[#ece9e2] border-b border-[#ece9e2]">
                            <template x-for="sub in docSubscribers" :key="'doc-sub-' + sub.id">
                                <div @click="sub.active = !sub.active" class="py-2.5 flex items-center justify-between gap-3 cursor-pointer select-none">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="relative w-[32px] h-[32px] rounded-full overflow-hidden flex-shrink-0 inline-flex items-center justify-center">
                                            <template x-if="sub.avatar">
                                                <img :src="sub.avatar" :alt="sub.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!sub.avatar">
                                                <span class="w-full h-full text-white font-bold text-[12px] flex items-center justify-center" :class="sub.bg" x-text="sub.initials"></span>
                                            </template>
                                        </span>
                                        <div class="text-[14px] text-[#1d2d35] truncate">
                                            <span class="font-bold" x-text="sub.name"></span><span x-show="sub.role" x-text="', ' + sub.role"></span>
                                        </div>
                                    </div>
                                    <span class="w-[18px] h-[18px] rounded-[4px] flex items-center justify-center flex-shrink-0"
                                          :class="sub.active ? 'bg-[#2da562] border border-[#2da562] text-white' : 'bg-white border border-[#b8b4ac]'">
                                        <svg x-show="sub.active" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                </div>
                            </template>
                        </div>
                        <div class="mt-5 flex items-center gap-2">
                            <button type="button" @click="saveDocSubscribers()" class="px-4 py-1.5 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[13.5px] cursor-pointer">Save</button>
                            <button type="button" @click="editDocSubscribersOpen = false" class="px-4 py-1.5 rounded-[6px] border border-[#93c5fd] bg-white hover:bg-[#eff6ff] text-[#1b6ac9] text-[13.5px] cursor-pointer">Never mind</button>
                        </div>
                    </div>
                </div>

            </div>

        @elseif($activeDoc)
            <!-- ==================================================================== -->
            <!-- GENERAL PROJECT DOCUMENT DETAIL & PREVIEW VIEW (?doc_id=...)         -->
            <!-- ==================================================================== -->
            @php
                $rawDocExt = strtolower(pathinfo($activeDoc->title, PATHINFO_EXTENSION));
                $docExt = preg_replace('/^(pdf|mov|jpg|jpeg|png|gif|docx|xlsx|pptx|amr|wav)\d+$/i', '$1', $rawDocExt);
                $isAudioDoc = in_array($docExt, ['amr', 'mp3', 'wav', 'm4a', 'ogg', 'aac', 'flac', 'opus'], true);
                $isVideoDoc = in_array($docExt, ['mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v'], true);
                $isImageDoc = in_array($docExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true);
                $isWordDoc = in_array($docExt, ['docx', 'doc', 'rtf', 'odt'], true);
                $isSheetDoc = in_array($docExt, ['xlsx', 'xls', 'csv'], true);
                $docAuthorFullName = $activeDoc->author->name ?? 'Manjot Singh';
                $docAuthorParts = explode(' ', trim($docAuthorFullName));
                $docAuthorShort = count($docAuthorParts) > 1
                    ? $docAuthorParts[0] . ' ' . strtoupper(substr(end($docAuthorParts), 0, 1)) . '.'
                    : $docAuthorFullName;
                $docDateFormatted = ($activeDoc->id == 7)
                    ? 'October 20, 2023'
                    : (($activeDoc->id == 8 || stripos($activeDoc->title, 'Flutter App Business Proposal') !== false)
                        ? 'January 30, 2023'
                        : ($activeDoc->created_at ? $activeDoc->created_at->format('F j, Y') : 'October 20, 2023'));
                $docSizeFormatted = ($activeDoc->id == 7 || $docExt === 'amr')
                    ? '801 KB'
                    : (($activeDoc->id == 8 || $isWordDoc || stripos($activeDoc->title, 'Flutter App Business Proposal') !== false)
                        ? '60.9 KB'
                        : ($isSheetDoc ? '142 KB' : ($isAudioDoc ? '801 KB' : ($isVideoDoc ? '4.2 MB' : ($isImageDoc ? '3.82 MB' : '245 KB')))));
                $mediaSizeText = $docSizeFormatted;
                $mediaSourceUrl = $isAudioDoc
                    ? '/uploads/docs/sample_call_recording.m4a?v=2'
                    : ($isVideoDoc
                        ? '/uploads/docs/sample_video_preview.mp4'
                        : ($isImageDoc
                            ? '/uploads/docs/diwali_3445_full.jpg'
                            : ($isWordDoc ? '/uploads/docs/Flutter_App_Business_Proposal_2.0.1.pdf' : '/uploads/docs/GeM-Bidding-10033548.pdf')));
                $mediaDownloadUrl = $isAudioDoc
                    ? (($docExt === 'amr' || $activeDoc->id == 7) ? '/uploads/docs/%2B919810935250-2310201131.amr' : '/uploads/docs/sample_call_recording.m4a')
                    : ($isWordDoc ? '/uploads/docs/Flutter%20App%20Business%20Proposal%202.0.1.docx' : $mediaSourceUrl);
                $docPreviewImageUrl = $isWordDoc ? '/uploads/docs/preview_flutter_proposal.png' : '/uploads/docs/preview_gem_bidding.png';
                $docLightboxPreviewUrl = $isWordDoc ? '/uploads/docs/preview_flutter_proposal.png' : '/uploads/docs/comm_page_1.png';
                $docLightboxPdfUrl = $isWordDoc ? '/uploads/docs/Flutter_App_Business_Proposal_2.0.1.pdf' : '/uploads/docs/Copy_of_Commercial_bid.pdf';
            @endphp
            <div class="px-8 sm:px-14 pt-8 pb-16"
                 x-data="{
                     audioPlaying: false,
                     audioCurrent: 0,
                     audioDuration: 497,
                     boosted: false,
                     docCommenting: false,
                     formatTime(sec) {
                         sec = Math.max(0, Math.floor(sec || 0));
                         const m = Math.floor(sec / 60);
                         const s = sec % 60;
                         return m + ':' + (s < 10 ? '0' + s : s);
                     },
                     toggleAudio() {
                         const el = this.$refs.bcDocAudio;
                         if (!el) return;
                         if (el.paused) {
                             el.play();
                         } else {
                             el.pause();
                         }
                     },
                     seekAudio(e) {
                         const el = this.$refs.bcDocAudio;
                         if (!el || !el.duration) return;
                         const rect = e.currentTarget.getBoundingClientRect();
                         const ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                         el.currentTime = ratio * el.duration;
                         this.audioCurrent = el.currentTime;
                     }
                 }">
                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-tight">
                        {{ $activeDoc->title }}
                    </h1>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[15px] text-[#1d2d35]">
                        <span>Posted by</span>
                        <span class="relative inline-flex items-center mx-0.5">
                            <img src="/uploads/avatars/22369668.png" alt="{{ $docAuthorFullName }}" class="w-[22px] h-[22px] rounded-full object-cover">
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[6.5px] font-black uppercase px-1 py-[0.5px] rounded-[2px] leading-none tracking-tight shadow-2xs">OUT</span>
                        </span>
                        <span>{{ $docAuthorShort }}</span>
                        <span>•</span>
                        <span>{{ $docDateFormatted }}</span>
                        <span>•</span>
                        <span>{{ $docSizeFormatted }}</span>
                    </div>
                </div>

                @if($isAudioDoc)
                    <!-- Basecamp Interactive Audio Player Layout (Matches media_1793336713098_29c7581a.jpg) -->
                    <audio x-ref="bcDocAudio"
                           src="{{ $mediaSourceUrl }}"
                           preload="metadata"
                           @loadedmetadata="if ($el.duration && isFinite($el.duration)) audioDuration = $el.duration"
                           @timeupdate="audioCurrent = $el.currentTime"
                           @play="audioPlaying = true"
                           @pause="audioPlaying = false"
                           @ended="audioPlaying = false; audioCurrent = 0"></audio>

                    <div class="mt-7 flex items-start gap-6">
                        <!-- Left Folded Document Icon (54x68px with 4 lines) -->
                        <div class="flex-shrink-0 pt-0.5">
                            <svg class="w-[54px] h-[68px]" viewBox="0 0 54 68" fill="none">
                                <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#dedbd6"/>
                                <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#c7c3bc"/>
                                <rect x="9" y="25" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                <rect x="9" y="34" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                <rect x="9" y="43" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                <rect x="9" y="52" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                            </svg>
                        </div>

                        <!-- Right Audio Player Box + Download / Replace Actions -->
                        <div class="flex flex-col items-start">
                            <!-- Basecamp Audio Player Box -->
                            <div class="w-[222px] bg-[#f7f6f3] rounded-[5px] pt-2.5 pb-3 px-3.5 relative overflow-hidden">
                                <div class="flex items-center gap-3">
                                    <button type="button"
                                            @click="toggleAudio()"
                                            class="w-[30px] h-[30px] rounded-full bg-white shadow-xs flex items-center justify-center text-[#1d2d35] hover:scale-105 transition-transform cursor-pointer flex-shrink-0"
                                            :aria-label="audioPlaying ? 'Pause' : 'Play'">
                                        <svg x-show="!audioPlaying" class="w-3.5 h-3.5 ml-0.5 fill-current" viewBox="0 0 16 16">
                                            <path d="M4 2.5v11l9-5.5-9-5.5z"/>
                                        </svg>
                                        <svg x-show="audioPlaying" x-cloak class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16">
                                            <path d="M3.5 2.5h3v11h-3v-11zm6 0h3v11h-3v-11z"/>
                                        </svg>
                                    </button>
                                    <div class="min-w-0 flex-1 leading-[1.2]">
                                        <div class="text-[12.5px] font-bold text-[#1d2d35] break-all">
                                            {{ $activeDoc->title }}
                                        </div>
                                        <div class="text-[11px] text-[#6d6a65] mt-0.5" x-text="formatTime(audioCurrent)">0:00</div>
                                    </div>
                                </div>
                                <!-- Bottom Progress Track -->
                                <div class="absolute bottom-0 left-0 right-0 h-[3.5px] bg-[#e2dfd9] cursor-pointer"
                                     @click="seekAudio($event)">
                                    <div class="h-full bg-[#1b6ac9] transition-all duration-100"
                                         :style="'width: ' + (audioDuration ? Math.min(100, (audioCurrent / audioDuration) * 100) : 0) + '%'"></div>
                                </div>
                            </div>

                            <!-- Download & Replace Row -->
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-[13px]">
                                <a href="{{ $mediaDownloadUrl }}"
                                   download="{{ $activeDoc->title }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                    </svg>
                                    <span>Download</span>
                                </a>
                                <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Replace with a new version</span>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                        </div>
                    </div>

                @elseif($isVideoDoc)
                    <!-- Basecamp Interactive Video Player Layout -->
                    <div class="mt-7">
                        <div class="rounded-[8px] overflow-hidden bg-black max-w-[680px] shadow-sm border border-[#d8d4cc]">
                            <video controls playsinline preload="metadata" src="{{ $mediaSourceUrl }}" class="w-full max-h-[420px] object-contain bg-black">
                                Your browser does not support HTML5 video playback.
                            </video>
                        </div>

                        <div class="mt-3.5 flex flex-wrap items-center gap-3 text-[13px]">
                            <a href="{{ $mediaSourceUrl }}"
                               download="{{ $activeDoc->title }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                </svg>
                                <span>Download</span>
                            </a>
                            <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Replace with a new version</span>
                                <input type="file" class="hidden">
                            </label>
                        </div>
                    </div>

                @elseif($isImageDoc)
                    <!-- Basecamp Image Preview Layout -->
                    <div class="mt-7">
                        <div @click="openPdfViewer({
                                name: '{{ addslashes($activeDoc->title) }}',
                                size: '{{ addslashes($docSizeFormatted) }}',
                                preview_url: '{{ addslashes($mediaSourceUrl) }}',
                                pdf_url: '{{ addslashes($mediaSourceUrl) }}',
                                page_url: '{{ route('projects.docs', ['id' => $project->id, 'doc_id' => $activeDoc->id]) }}',
                                is_photo: true
                             })"
                             class="inline-block max-w-full sm:max-w-[540px] cursor-zoom-in">
                            <img src="{{ $mediaSourceUrl }}?v=3"
                                 alt="{{ $activeDoc->title }}"
                                 class="w-full h-auto bg-white rounded-[2px] object-contain shadow-xs">
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3 text-[13px]">
                            <a href="{{ $mediaSourceUrl }}?v=3"
                               download="{{ $activeDoc->title }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                </svg>
                                <span>Download</span>
                            </a>
                            <a href="{{ $mediaSourceUrl }}?v=3"
                               target="_blank"
                               class="text-[#1d2d35] underline hover:text-[#1b6ac9]">
                                View full-size
                            </a>
                            <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Replace with a new version</span>
                                <input type="file" class="hidden">
                            </label>
                        </div>
                    </div>

                @else
                    <!-- Standard Document / DOCX / PDF Preview -->
                    @if($isWordDoc)
                        <div class="mt-7 flex items-center gap-4">
                            <!-- Basecamp Light-Blue DOCX Icon with DOCX Pill Badge -->
                            <div class="flex-shrink-0">
                                <svg class="w-[54px] h-[68px]" viewBox="0 0 54 68" fill="none">
                                    <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#bce3fa"/>
                                    <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#89c9f4"/>
                                    <rect x="9" y="21" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                    <rect x="9" y="29" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                    <rect x="9" y="37" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                    <rect x="7" y="48" width="40" height="14" rx="3" fill="#1d74d6"/>
                                    <text x="27" y="58.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="10.5" font-weight="800" letter-spacing="0.4">DOCX</text>
                                </svg>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-[13px]">
                                <a href="{{ $mediaDownloadUrl }}"
                                   download="{{ $activeDoc->title }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                    </svg>
                                    <span>Download</span>
                                </a>
                                <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Replace with a new version</span>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                        </div>
                    @endif

                    <div @click="openPdfViewer({
                             name: '{{ addslashes($activeDoc->title) }}',
                             size: '{{ addslashes($docSizeFormatted) }}',
                             preview_url: '{{ addslashes($docLightboxPreviewUrl) }}',
                             pdf_url: '{{ addslashes($docLightboxPdfUrl) }}',
                             page_url: '{{ request()->fullUrl() }}',
                             is_photo: false,
                             is_video: false
                         })"
                         class="mt-6 bg-[#f6f4ef] border border-[#e5e2dc] rounded-[10px] p-6 flex flex-col items-center cursor-pointer group">
                        <img src="{{ $docPreviewImageUrl }}?v=2"
                             alt="{{ $activeDoc->title }}"
                             class="max-w-full sm:max-w-[680px] w-full h-auto bg-white border border-[#d8d4cc] shadow-sm rounded object-contain group-hover:shadow-md transition-shadow">
                    </div>

                    @if(!$isWordDoc)
                        <div class="mt-4 flex flex-wrap items-center gap-3 text-[13px]">
                            <a href="{{ $mediaDownloadUrl }}"
                               download="{{ $activeDoc->title }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[5px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[#1d2d35] font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 19h16"/>
                                </svg>
                                <span>Download</span>
                            </a>
                            <label class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Replace with a new version</span>
                                <input type="file" class="hidden">
                            </label>
                        </div>
                    @endif
                @endif

                <!-- Basecamp Boost Rocket Button -->
                <div class="mt-7 flex items-center gap-2">
                    <button type="button"
                            @click="boosted = !boosted"
                            class="w-[28px] h-[28px] rounded-full border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] inline-flex items-center justify-center text-[13px] transition-colors cursor-pointer"
                            title="Boost this">
                        <span>🚀</span>
                    </button>
                    <span x-show="boosted" x-cloak class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#f7f6f3] border border-[#e2dfd9] text-[12px] text-[#1d2d35]">
                        <span>👏</span>
                        <span class="font-semibold">You boosted this!</span>
                    </span>
                </div>

                <!-- Basecamp Add Comment Row (Matches media_1793336713098_29c7581a.jpg) -->
                <div class="border-t border-[#ece9e2] mt-10 pt-6">
                    <div class="flex items-center gap-3.5">
                        <div class="relative flex-shrink-0">
                            <img src="/uploads/avatars/22369668.png"
                                 alt="{{ auth()->user()->name }}"
                                 class="w-[42px] h-[42px] rounded-full object-cover">
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[8.5px] font-black uppercase px-1.5 py-[1px] rounded-[3px] leading-none tracking-tight shadow-2xs">OUT</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div x-show="!docCommenting"
                                 @click="docCommenting = true"
                                 class="w-full py-2 text-[15px] text-[#8c8881] cursor-text hover:text-[#6d6a65] transition-colors">
                                Add your comment...
                            </div>

                            <div x-show="docCommenting" x-cloak class="space-y-3">
                                <div class="rounded-[8px] border border-[#1b6ac9] bg-white overflow-hidden">
                                    <textarea rows="4" placeholder="Add your comment..." class="w-full px-4 py-3 text-[15px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none resize-y"></textarea>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="docCommenting = false" class="px-4 py-1.5 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white font-bold text-[13.5px] cursor-pointer">
                                        Add this comment
                                    </button>
                                    <button type="button" @click="docCommenting = false" class="px-3.5 py-1.5 rounded-full border border-[#d8d4cc] bg-white text-[13.5px] text-[#1d2d35] cursor-pointer">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- ==================================================================== -->
            <!-- VAULT / FOLDER LIST VIEW (/projects/29/docs)                         -->
            <!-- ==================================================================== -->
            <div class="px-8 sm:px-14 pt-8 pb-16">

                <!-- Vault / Folder Title (32px Extrabold) -->
                <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                    {{ $vaultTitle }}
                </h1>

                <!-- Basecamp Toolbar Row: [+ New...] [All files | Images | File type v] [Sort manually v] [Filter...] [Grid|List] -->
                <div class="mt-3 flex flex-wrap items-center gap-2 relative">
                    <!-- [+ New...] Button + Basecamp Dropdown Menu (Matches media_1793346451808_b4b0ff81.jpg 1:1) -->
                    <div class="relative inline-flex items-center" @click.outside="newMenuOpen = false">
                        <button type="button" 
                                @click="newMenuOpen = !newMenuOpen; fileTypeMenu = false; sortMenu = false" 
                                class="inline-flex items-center gap-1 px-3 py-1 bg-[#1b6ac9] hover:bg-[#1557a6] text-white font-bold text-[13px] rounded-[5px] transition-colors cursor-pointer">
                            <span>+ New...</span>
                        </button>

                        <div x-show="newMenuOpen"
                             x-cloak
                             class="absolute left-0 top-[calc(100%+5px)] w-[236px] max-h-[330px] overflow-y-auto rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.14)] py-2 z-40 text-left">
                            <!-- Start a new doc -->
                            <button type="button"
                                    @click="newDocMode = 'doc'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span>Start a new doc</span>
                            </button>

                            <!-- Make a new folder -->
                            <button type="button"
                                    @click="newDocMode = 'folder'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                                <span>Make a new folder</span>
                            </button>

                            <!-- Upload files -->
                            <button type="button"
                                    @click="newDocMode = 'upload'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-5 5m5-5l5 5M4 20h16"/></svg>
                                <span>Upload files</span>
                            </button>

                            <!-- Or link to files from... Divider -->
                            <div class="px-4 pt-3 pb-1.5 flex items-center gap-2 text-[12px] text-[#6d6a65]">
                                <span class="whitespace-nowrap">Or link to files from...</span>
                                <span class="flex-1 h-[1px] bg-[#ece9e2]"></span>
                            </div>

                            <!-- Adobe Creative Cloud -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Adobe Creative Cloud'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"/></svg>
                                <span>Adobe Creative Cloud</span>
                            </button>

                            <!-- Airtable -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Airtable'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 11.5l-9-4.5v8l9 4.5v-8zm2 8l9-4.5V9l-9 4.5v8z"/></svg>
                                <span>Airtable</span>
                            </button>

                            <!-- Basecamp -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Basecamp'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3C7 3 3 8.5 3 14.5 3 18.5 6.5 20 12 20s9-1.5 9-5.5C21 8.5 17 3 12 3z"/><path d="M5 14l4-4 3 3 4-5 3 4"/></svg>
                                <span>Basecamp</span>
                            </button>

                            <!-- Box -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Box'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 10h8M8 14h5"/></svg>
                                <span>Box</span>
                            </button>

                            <!-- Dropbox -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Dropbox'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2L0 6l6 4 6-4-6-4zm12 0l-6 4 6 4 6-4-6-4zM0 14l6 4 6-4-6-4-6 4zm18-4l-6 4 6 4 6-4-6-4zM6 19.5l6 4 6-4-6-4-6 4z"/></svg>
                                <span>Dropbox</span>
                            </button>

                            <!-- Figma -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Figma'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="15" cy="12" r="3"/><path d="M9 3h6a3 3 0 010 6H9a3 3 0 010-6zM9 9h3v6H9a3 3 0 010-6zM9 15h3v3a3 3 0 01-6 0 3 3 0 013-3z"/></svg>
                                <span>Figma</span>
                            </button>

                            <!-- Google Drive -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Google Drive'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polygon points="12 2 19 14 5 14 12 2"/><path d="M5 14l-3 6h16l3-6"/></svg>
                                <span>Google Drive</span>
                            </button>

                            <!-- iCloud Drive -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'iCloud Drive'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z"/></svg>
                                <span>iCloud Drive</span>
                            </button>

                            <!-- Notion -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'Notion'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 16V8l6 8V8"/></svg>
                                <span>Notion</span>
                            </button>

                            <!-- OneDrive -->
                            <button type="button"
                                    @click="newDocMode = 'external'; externalService = 'OneDrive'; newDocModal = true; newMenuOpen = false"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-[13.5px] text-[#1d2d35] hover:bg-[#f2f0ec] cursor-pointer">
                                <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17.5 19H9a7 7 0 116.71-9h1.79a4.5 4.5 0 110 9z"/></svg>
                                <span>OneDrive</span>
                            </button>
                        </div>
                    </div>

                    <!-- [All files | Images | File type v] with Popover Dropdown (Matches media_1793345979436_1a59376e.png & media_1793423075427_1ef59bfa.jpg) -->
                    <div class="relative inline-flex items-center" @click.outside="fileTypeMenu = false">
                        <div class="inline-flex items-center border border-[#d8d4cc] rounded-[5px] bg-white text-[13px] text-[#1d2d35] overflow-hidden">
                            <button type="button"
                                    @click="fileTypeFilter = 'all'; fileTypeMenu = false"
                                    :class="fileTypeFilter === 'all' ? 'font-bold bg-[#f2f0ec]' : 'font-normal bg-white hover:bg-slate-50'"
                                    class="px-3 py-1 text-[#1d2d35] cursor-pointer transition-colors">
                                All files
                            </button>
                            @if($hasImagesInView)
                            <button type="button"
                                    @click="fileTypeFilter = 'images'; fileTypeMenu = false"
                                    :class="fileTypeFilter === 'images' ? 'font-bold bg-[#f2f0ec]' : 'font-normal bg-white hover:bg-slate-50'"
                                    class="px-3 py-1 border-l border-[#d8d4cc] text-[#1d2d35] cursor-pointer transition-colors">
                                Images
                            </button>
                            @endif
                            <button type="button"
                                    @click="fileTypeMenu = !fileTypeMenu; sortMenu = false"
                                    :class="(fileTypeFilter !== 'all' && fileTypeFilter !== 'images') ? 'font-bold bg-[#f2f0ec]' : 'bg-white hover:bg-slate-50'"
                                    class="px-3 py-1 border-l border-[#d8d4cc] inline-flex items-center gap-1 text-[#1d2d35] cursor-pointer transition-colors">
                                <span x-text="(fileTypeFilter === 'all' || fileTypeFilter === 'images') ? 'File type' : fileTypeFilter">File type</span>
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>

                        <!-- File type Dropdown Popover (Lists available file extensions like jpg, mov, pdf) -->
                        <div x-show="fileTypeMenu"
                             x-cloak
                             class="absolute {{ $hasImagesInView ? 'left-[118px]' : 'left-[64px]' }} top-[calc(100%+6px)] w-[184px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-3 px-4 z-30 text-left">
                            <div class="text-[13.5px] font-bold text-[#1d2d35] mb-2">File type</div>
                            <template x-for="ext in availableFileTypes" :key="ext">
                                <button type="button"
                                        @click="fileTypeFilter = (fileTypeFilter === ext ? 'all' : ext); fileTypeMenu = false"
                                        class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span x-text="ext"></span>
                                    <svg x-show="fileTypeFilter === ext" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- [Sort manually v] with Popover Dropdown -->
                    <div class="relative inline-flex items-center" @click.outside="sortMenu = false">
                        <button type="button"
                                @click="sortMenu = !sortMenu; fileTypeMenu = false"
                                class="inline-flex items-center gap-1 px-3 py-1 border border-[#d8d4cc] rounded-[5px] bg-white hover:bg-slate-50 text-[13px] text-[#1d2d35] cursor-pointer">
                            <span x-text="sortBy === 'manual' ? 'Sort manually' : (sortBy === 'name' ? 'Sorted by name' : (sortBy === 'created' ? 'Sorted by created' : 'Sorted by modified'))">Sort manually</span>
                            <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Sort by... Dropdown Popover -->
                        <div x-show="sortMenu"
                             x-cloak
                             class="absolute left-0 top-[calc(100%+6px)] w-[184px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-3 px-4 z-30 text-left">
                            <div class="text-[13.5px] font-bold text-[#1d2d35] mb-2">Sort by...</div>
                            <div class="space-y-1">
                                <button type="button"
                                        @click="sortBy = 'name'; sortMenu = false"
                                        class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Name</span>
                                    <svg x-show="sortBy === 'name'" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button"
                                        @click="sortBy = 'created'; sortMenu = false"
                                        class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Created</span>
                                    <svg x-show="sortBy === 'created'" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button"
                                        @click="sortBy = 'modified'; sortMenu = false"
                                        class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Modified</span>
                                    <svg x-show="sortBy === 'modified'" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button"
                                        @click="sortBy = 'manual'; sortMenu = false"
                                        class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Manually</span>
                                    <svg x-show="sortBy === 'manual'" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="text" 
                           x-model="docFilter" 
                           placeholder="Filter..." 
                           class="px-3 py-1 bg-white border border-[#d8d4cc] rounded-[5px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] w-[145px] focus:outline-none focus:border-[#1b6ac9]">

                    <div class="inline-flex items-center border border-[#d8d4cc] rounded-[5px] overflow-hidden bg-white">
                        <button type="button" 
                                @click="viewMode = 'grid'" 
                                class="px-2 py-1.5 transition-colors cursor-pointer"
                                :class="viewMode === 'grid' ? 'bg-[#e6e3dd] text-[#1d2d35]' : 'bg-white text-[#6d6a65] hover:text-[#1d2d35]'"
                                title="Grid view">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16">
                                <rect x="1" y="1" width="6" height="6" rx="1"/>
                                <rect x="9" y="1" width="6" height="6" rx="1"/>
                                <rect x="1" y="9" width="6" height="6" rx="1"/>
                                <rect x="9" y="9" width="6" height="6" rx="1"/>
                            </svg>
                        </button>
                        <button type="button" 
                                @click="viewMode = 'list'" 
                                class="px-2 py-1.5 border-l border-[#d8d4cc] transition-colors cursor-pointer"
                                :class="viewMode === 'list' ? 'bg-[#e6e3dd] text-[#1d2d35]' : 'bg-white text-[#6d6a65] hover:text-[#1d2d35]'"
                                title="List view">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16">
                                <circle cx="2" cy="3" r="1.3"/>
                                <rect x="5" y="2" width="10" height="2" rx="0.5"/>
                                <circle cx="2" cy="8" r="1.3"/>
                                <rect x="5" y="7" width="10" height="2" rx="0.5"/>
                                <circle cx="2" cy="13" r="1.3"/>
                                <rect x="5" y="12" width="10" height="2" rx="0.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Inline New Document / Folder / Upload / External Link Form -->
                <form x-show="newDocModal" x-cloak action="{{ route('projects.docs.store', $project->id) }}" method="POST" class="mt-4 p-5 bg-[#fbfaf8] border border-[#e5e2dc] rounded-lg space-y-3">
                    @csrf
                    <div class="text-[15px] font-bold text-[#1d2d35]"
                         x-text="newDocMode === 'folder' ? 'Make a new folder' : (newDocMode === 'upload' ? 'Upload files' : (newDocMode === 'external' ? ('Link a file from ' + externalService) : 'Start a new doc'))"></div>
                    <div x-show="newDocMode === 'upload'" x-cloak>
                        <input type="file" multiple class="block w-full text-[13px] text-[#1d2d35] file:mr-3 file:py-1.5 file:px-3 file:rounded-[5px] file:border file:border-[#d8d4cc] file:text-[13px] file:font-semibold file:bg-white file:text-[#1d2d35] hover:file:bg-[#f2f0ec]">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-[#1d2d35] mb-1"
                               x-text="newDocMode === 'folder' ? 'Folder name' : 'Title / Filename'"></label>
                        <input type="text" name="title" required
                               :placeholder="newDocMode === 'folder' ? 'Name this folder...' : (newDocMode === 'external' ? ('Title of this ' + externalService + ' file...') : 'Type a title...')"
                               class="w-full px-3 py-1.5 bg-white border border-[#d8d4cc] rounded-md text-[14px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                    </div>
                    <div x-show="newDocMode !== 'folder'">
                        <label class="block text-[13px] font-bold text-[#1d2d35] mb-1"
                               x-text="newDocMode === 'external' ? 'Paste URL & Notes' : 'Notes / Content'"></label>
                        <textarea name="content" rows="3"
                                  :placeholder="newDocMode === 'external' ? ('https://... Paste the link to your ' + externalService + ' file') : 'Write away...'"
                                  class="w-full px-3 py-1.5 bg-white border border-[#d8d4cc] rounded-md text-[14px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-1.5 bg-[#199849] hover:bg-[#127337] text-white font-bold text-[13px] rounded-full cursor-pointer">Save</button>
                        <button type="button" @click="newDocModal = false" class="px-3 py-1.5 text-[13px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">Cancel</button>
                    </div>
                </form>

                @if($vaultFiles !== null)
                    <!-- ========================================================================= -->
                    <!-- VIEW MODE 1: 4-COLUMN GALLERY GRID VIEW                                   -->
                    <!-- ========================================================================= -->
                    <div x-show="viewMode === 'grid'" x-cloak class="mt-6">
                        <div class="grid grid-cols-2 sm:grid-cols-4 border border-[#d8d4cc] bg-white divide-x divide-[#d8d4cc] overflow-hidden">
                            <template x-for="f in sortedVaultItems" :key="'grid-' + f.idx + '-' + f.name">
                                <a :href="f.page_url"
                                   @click.prevent="openPdfViewer(f)"
                                   class="block h-[275px] bg-white overflow-hidden relative group cursor-pointer"
                                   :title="f.name">
                                    <img :src="f.preview_url + '?v=3'"
                                         :alt="f.name"
                                         class="w-full h-full object-cover object-top group-hover:scale-[1.01] transition-transform duration-150">
                                </a>
                            </template>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- VIEW MODE 2: LIST VIEW (Matches media_1793345979436 & media_1793346008975) -->
                    <!-- ========================================================================= -->
                    <div x-show="viewMode === 'list'" class="mt-6 border-t border-[#ece9e2] divide-y divide-[#ece9e2]">
                        <template x-for="f in sortedVaultItems" :key="'list-' + f.idx + '-' + f.name">
                            <a :href="f.page_url"
                               class="py-4 flex items-center gap-5 hover:bg-[#fbfaf8] -mx-3 px-3 rounded-lg transition-colors cursor-pointer group relative">

                                <!-- Left 3-Dot Vertical Drag Handle on Hover -->
                                <span class="absolute -left-3.5 top-1/2 -translate-y-1/2 text-[#8c8881] opacity-0 group-hover:opacity-100 transition-opacity cursor-grab">
                                    <svg class="w-3.5 h-4 fill-current" viewBox="0 0 16 16">
                                        <circle cx="8" cy="3" r="1.4"/>
                                        <circle cx="8" cy="8" r="1.4"/>
                                        <circle cx="8" cy="13" r="1.4"/>
                                    </svg>
                                </span>

                                <!-- Left Thumbnail (64x44px landscape or 44x64px portrait photo or 54x66px PDF miniature) -->
                                <div @click.prevent.stop="openPdfViewer(f)"
                                     :class="f.is_landscape ? 'w-[64px] h-[44px]' : ((f.is_photo || f.is_video) ? 'w-[64px] h-[64px]' : 'w-[54px] h-[66px]')"
                                     class="flex items-center justify-center flex-shrink-0">
                                    <img :src="f.thumb_url + '?v=6'"
                                         :alt="f.name"
                                         :class="f.is_landscape ? 'w-[64px] h-[44px] object-cover shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : ((f.is_photo || f.is_video) ? 'w-[44px] h-[64px] object-cover shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'w-[54px] h-[66px] object-contain bg-white border border-[#ece9e2] shadow-[0_1px_2px_rgba(0,0,0,0.06)]')">
                                </div>

                                <!-- Right File Details: 17px Bold Filename + 14.5px Author · Date · Size -->
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-[17px] font-bold text-[#1d2d35] group-hover:text-[#1b6ac9] leading-snug truncate" x-text="f.name"></h3>
                                    <p class="text-[14.5px] leading-snug mt-0.5">
                                        <span class="text-[#856d47]" x-text="f.author + ' • ' + f.date + (f.size ? ' •' : '')"></span>
                                        <span x-show="f.size" class="text-[#1d2d35]" x-text="f.size"></span>
                                    </p>
                                </div>
                            </a>
                        </template>
                        <div x-show="sortedVaultItems.length === 0" class="py-12 text-center text-[14.5px] text-[#6d6a65]">
                            This folder is empty.
                        </div>
                    </div>
                @else
                    <!-- Top Divider with Right-Aligned [Off] Keep folders open Toggle (Matches media_1793340411314_14693fd2.png & media_1793423075427_1ef59bfa.jpg) -->
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex-1 border-t border-[#ece9e2]"></div>
                        @if($hasRootFolders)
                        <button type="button"
                                @click="keepFoldersOpen = !keepFoldersOpen"
                                class="inline-flex items-center gap-1.5 text-[12px] text-[#1d2d35] cursor-pointer select-none">
                            <span class="inline-flex items-center w-[36px] h-[18px] rounded-[4px] p-[2px] transition-colors"
                                  :class="keepFoldersOpen ? 'bg-[#199849] justify-end' : 'bg-[#9a968e] justify-start'">
                                <span x-show="keepFoldersOpen" class="text-[9.5px] font-bold text-white pl-1 leading-none">On</span>
                                <span class="w-[10px] h-[14px] rounded-[2px] bg-white shadow-2xs flex-shrink-0"></span>
                                <span x-show="!keepFoldersOpen" class="text-[9.5px] font-bold text-white pr-0.5 pl-0.5 leading-none">Off</span>
                            </span>
                            <span>Keep folders open</span>
                        </button>
                        @endif
                    </div>

                    <!-- Root Docs & Files Grid View -->
                    <div x-show="viewMode === 'grid'" x-cloak class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($docs as $doc)
                            @php
                                $trimmedTitle = trim($doc->title);
                                $rawExt = strtolower(pathinfo($trimmedTitle, PATHINFO_EXTENSION));
                                $itemExt = preg_replace('/^(pdf|mov|jpg|jpeg|png|gif|docx|xlsx|pptx|amr|wav)\d+$/i', '$1', $rawExt);
                                $isFolder = array_key_exists($trimmedTitle, $folderItemCounts)
                                    || str_contains($trimmedTitle, 'GEM/')
                                    || str_contains($trimmedTitle, 'BID NO:')
                                    || ($project->id == 4 && $itemExt === '');
                                $folderCode = str_contains($trimmedTitle, '8176205') ? '8176205' : (str_contains($trimmedTitle, '8159428') ? '8159428' : $trimmedTitle);
                                $folderCountStr = $folderItemCounts[$trimmedTitle] ?? '3 items';
                                $authorName = $doc->author->name ?? 'Pawandeep Kaur';
                                $isImageItem = !$isFolder && in_array($itemExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true);
                                $isVideoItem = !$isFolder && in_array($itemExt, ['mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v'], true);
                                $isWordItem = !$isFolder && in_array($itemExt, ['docx', 'doc', 'rtf', 'odt'], true);
                                $isSheetItem = !$isFolder && in_array($itemExt, ['xlsx', 'xls', 'csv'], true);
                                $isMediaOrGeneric = !$isFolder && in_array($itemExt, ['amr', 'mp3', 'wav', 'm4a', 'ogg', 'aac', 'flac', 'opus', 'mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v'], true);
                                $targetHref = $isFolder
                                    ? route('projects.docs', ['id' => $project->id, 'folder' => $folderCode])
                                    : route('projects.docs', ['id' => $project->id, 'doc_id' => $doc->id]);
                            @endphp
                            <a href="{{ $targetHref }}"
                               @if($isImageItem)
                                   @click.prevent="openPdfViewer({
                                       name: '{{ addslashes($doc->title) }}',
                                       size: '3.82 MB',
                                       preview_url: '/uploads/docs/diwali_3445_full.jpg',
                                       pdf_url: '/uploads/docs/diwali_3445_full.jpg',
                                       page_url: '{{ $targetHref }}',
                                       is_photo: true,
                                       is_video: false
                                   })"
                               @elseif($isVideoItem)
                                   @click.prevent="openPdfViewer({
                                       name: '{{ addslashes($doc->title) }}',
                                       size: '14.2 MB',
                                       preview_url: '/uploads/docs/inaug_mov_0141.jpg',
                                       pdf_url: '/uploads/docs/sample_video_preview.mp4',
                                       video_url: '/uploads/docs/sample_video_preview.mp4',
                                       page_url: '{{ $targetHref }}',
                                       is_photo: false,
                                       is_video: true
                                   })"
                               @endif
                               x-show="(!docFilter || '{{ strtolower(addslashes($doc->title . ' ' . $authorName)) }}'.includes(docFilter.toLowerCase())) && (fileTypeFilter === 'all' || (fileTypeFilter === 'images' && {{ $isImageItem ? 'true' : 'false' }}) || fileTypeFilter === '{{ $itemExt }}')"
                               class="p-4 rounded-[8px] border border-[#e5e2dc] bg-white hover:shadow-md transition-all flex flex-col items-center text-center group">
                                <div class="w-16 h-20 flex items-center justify-center mb-2">
                                    @if($isFolder)
                                        <!-- Basecamp Gray Folder Icon with + Sign -->
                                        <svg class="w-[52px] h-[44px]" viewBox="0 0 52 44" fill="none">
                                            <path d="M0 5C0 2.79086 1.79086 1 4 1H18.5C19.8 1 21 1.6 21.8 2.6L24.5 6H48C50.2091 6 52 7.79086 52 10V39C52 41.2091 50.2091 43 48 43H4C1.79086 43 0 41.2091 0 39V5Z" fill="#c7c3bc"/>
                                            <rect x="0" y="9" width="52" height="34" rx="4" fill="#d8d5cf"/>
                                            <line x1="26" y1="20" x2="26" y2="32" stroke="#9e9a93" stroke-width="2" stroke-linecap="round"/>
                                            <line x1="20" y1="26" x2="32" y2="26" stroke="#9e9a93" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    @elseif($isImageItem)
                                        <img src="/uploads/docs/diwali_3445.jpg?v=3" alt="{{ $doc->title }}" class="w-[50px] h-[64px] object-cover shadow-[0_1px_2px_rgba(0,0,0,0.08)]">
                                    @elseif($isVideoItem)
                                        <img src="/uploads/docs/inaug_mov_0141.jpg?v=3" alt="{{ $doc->title }}" class="w-[64px] h-[44px] object-cover shadow-[0_1px_2px_rgba(0,0,0,0.08)]">
                                    @elseif($isWordItem)
                                        <!-- Basecamp Light-Blue DOCX Icon with DOCX Pill Badge -->
                                        <svg class="w-[46px] h-[58px]" viewBox="0 0 54 68" fill="none">
                                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#bce3fa"/>
                                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#89c9f4"/>
                                            <rect x="9" y="21" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                            <rect x="9" y="29" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                            <rect x="9" y="37" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                            <rect x="7" y="48" width="40" height="14" rx="3" fill="#1d74d6"/>
                                            <text x="27" y="58.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="10.5" font-weight="800" letter-spacing="0.4">{{ strtoupper($itemExt ?: 'DOCX') }}</text>
                                        </svg>
                                    @elseif($isSheetItem)
                                        <!-- Basecamp Light-Green XLSX Icon with XLSX Pill Badge -->
                                        <svg class="w-[46px] h-[58px]" viewBox="0 0 54 68" fill="none">
                                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#bbf7d0"/>
                                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#86efac"/>
                                            <rect x="9" y="21" width="36" height="2.6" rx="1.3" fill="#22c55e"/>
                                            <rect x="9" y="29" width="36" height="2.6" rx="1.3" fill="#22c55e"/>
                                            <rect x="9" y="37" width="36" height="2.6" rx="1.3" fill="#22c55e"/>
                                            <rect x="7" y="48" width="40" height="14" rx="3" fill="#16a34a"/>
                                            <text x="27" y="58.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="10.5" font-weight="800" letter-spacing="0.4">{{ strtoupper($itemExt ?: 'XLSX') }}</text>
                                        </svg>
                                    @elseif($isMediaOrGeneric)
                                        <svg class="w-[46px] h-[58px]" viewBox="0 0 54 68" fill="none">
                                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#dedbd6"/>
                                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#c7c3bc"/>
                                            <rect x="9" y="25" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                            <rect x="9" y="34" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                            <rect x="9" y="43" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                            <rect x="9" y="52" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                        </svg>
                                    @else
                                        <img src="/uploads/docs/thumb_gem_bidding.png" alt="{{ $doc->title }}" class="w-14 h-18 object-contain bg-white border border-[#ece9e2] shadow-2xs">
                                    @endif
                                </div>
                                <h3 class="text-[14px] font-bold text-[#1d2d35] group-hover:text-[#1b6ac9] leading-snug line-clamp-2">{{ $doc->title }}</h3>
                                <p class="text-[12px] text-[#6d6a65] mt-1">{{ $isFolder ? $folderCountStr : $authorName }}</p>
                            </a>
                        @endforeach
                    </div>

                    <!-- Root Docs & Files List (Matches media_1793340411314_14693fd2.png & media_1793423075427_1ef59bfa.jpg) -->
                    <div x-show="viewMode === 'list'" class="mt-2 divide-y divide-[#ece9e2]">
                        @forelse($docs as $doc)
                            @php
                                $trimmedTitle = trim($doc->title);
                                $rawExt = strtolower(pathinfo($trimmedTitle, PATHINFO_EXTENSION));
                                $itemExt = preg_replace('/^(pdf|mov|jpg|jpeg|png|gif|docx|xlsx|pptx|amr|wav)\d+$/i', '$1', $rawExt);
                                $isFolder = array_key_exists($trimmedTitle, $folderItemCounts)
                                    || str_contains($trimmedTitle, 'GEM/')
                                    || str_contains($trimmedTitle, 'BID NO:')
                                    || ($project->id == 4 && $itemExt === '');
                                $folderCode = str_contains($trimmedTitle, '8176205') ? '8176205' : (str_contains($trimmedTitle, '8159428') ? '8159428' : $trimmedTitle);
                                $folderCountStr = $folderItemCounts[$trimmedTitle] ?? '3 items';
                                $authorName = $doc->author->name ?? 'Pawandeep Kaur';
                                $isImageItem = !$isFolder && in_array($itemExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true);
                                $isVideoItem = !$isFolder && in_array($itemExt, ['mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v'], true);
                                $isWordItem = !$isFolder && in_array($itemExt, ['docx', 'doc', 'rtf', 'odt'], true);
                                $isSheetItem = !$isFolder && in_array($itemExt, ['xlsx', 'xls', 'csv'], true);
                                $isMediaOrGeneric = !$isFolder && in_array($itemExt, ['amr', 'mp3', 'wav', 'm4a', 'ogg', 'aac', 'flac', 'opus', 'mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v'], true);
                                $dateStr = ($doc->id == 7)
                                    ? 'Oct 20, 2023'
                                    : (($doc->id == 8 || stripos($doc->title, 'Flutter App Business Proposal') !== false)
                                        ? 'Jan 30, 2023'
                                        : ($doc->created_at ? ($doc->created_at->year < 2026 ? $doc->created_at->format('M j, Y') : $doc->created_at->format('M j')) : 'Oct 29'));
                                $itemSizeStr = ($doc->id == 7 || $itemExt === 'amr')
                                    ? '801 KB'
                                    : (($doc->id == 8 || $isWordItem || stripos($doc->title, 'Flutter App Business Proposal') !== false)
                                        ? '60.9 KB'
                                        : ($isSheetItem ? '142 KB' : ($isImageItem ? '3.82 MB' : ($isVideoItem ? '14.2 MB' : ($isMediaOrGeneric ? '801 KB' : '245 KB')))));
                                $targetHref = $isFolder
                                    ? route('projects.docs', ['id' => $project->id, 'folder' => $folderCode])
                                    : route('projects.docs', ['id' => $project->id, 'doc_id' => $doc->id]);
                            @endphp
                            <a href="{{ $targetHref }}"
                               @if($isImageItem)
                                   @click.prevent="openPdfViewer({
                                       name: '{{ addslashes($doc->title) }}',
                                       size: '3.82 MB',
                                       preview_url: '/uploads/docs/diwali_3445_full.jpg',
                                       pdf_url: '/uploads/docs/diwali_3445_full.jpg',
                                       page_url: '{{ $targetHref }}',
                                       is_photo: true,
                                       is_video: false
                                   })"
                               @elseif($isVideoItem)
                                   @click.prevent="openPdfViewer({
                                       name: '{{ addslashes($doc->title) }}',
                                       size: '14.2 MB',
                                       preview_url: '/uploads/docs/inaug_mov_0141.jpg',
                                       pdf_url: '/uploads/docs/sample_video_preview.mp4',
                                       video_url: '/uploads/docs/sample_video_preview.mp4',
                                       page_url: '{{ $targetHref }}',
                                       is_photo: false,
                                       is_video: true
                                   })"
                               @endif
                               x-show="(!docFilter || '{{ strtolower(addslashes($doc->title . ' ' . $authorName)) }}'.includes(docFilter.toLowerCase())) && (fileTypeFilter === 'all' || (fileTypeFilter === 'images' && {{ $isImageItem ? 'true' : 'false' }}) || fileTypeFilter === '{{ $itemExt }}')"
                               class="py-5 flex items-center gap-5 hover:bg-[#fbfaf8] -mx-3 px-3 rounded-lg transition-colors group relative">

                                <!-- Left 3-Dot Vertical Drag Handle on Hover -->
                                <span class="absolute -left-3.5 top-1/2 -translate-y-1/2 text-[#8c8881] opacity-0 group-hover:opacity-100 transition-opacity cursor-grab">
                                    <svg class="w-3.5 h-4 fill-current" viewBox="0 0 16 16">
                                        <circle cx="8" cy="3" r="1.4"/>
                                        <circle cx="8" cy="8" r="1.4"/>
                                        <circle cx="8" cy="13" r="1.4"/>
                                    </svg>
                                </span>

                                <div class="w-[64px] h-[58px] flex items-center justify-center flex-shrink-0">
                                    @if($isFolder)
                                        <!-- Basecamp Gray Folder Icon with + Sign (Matches media_1793340411314_14693fd2.png) -->
                                        <svg class="w-[52px] h-[44px]" viewBox="0 0 52 44" fill="none">
                                            <path d="M0 5C0 2.79086 1.79086 1 4 1H18.5C19.8 1 21 1.6 21.8 2.6L24.5 6H48C50.2091 6 52 7.79086 52 10V39C52 41.2091 50.2091 43 48 43H4C1.79086 43 0 41.2091 0 39V5Z" fill="#c7c3bc"/>
                                            <rect x="0" y="9" width="52" height="34" rx="4" fill="#d8d5cf"/>
                                            <line x1="26" y1="20" x2="26" y2="32" stroke="#9e9a93" stroke-width="2" stroke-linecap="round"/>
                                            <line x1="20" y1="26" x2="32" y2="26" stroke="#9e9a93" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    @elseif($isImageItem)
                                        <img src="/uploads/docs/diwali_3445.jpg?v=3" alt="{{ $doc->title }}" class="w-[50px] h-[64px] object-cover shadow-[0_1px_2px_rgba(0,0,0,0.08)]">
                                    @elseif($isVideoItem)
                                        <img src="/uploads/docs/inaug_mov_0141.jpg?v=3" alt="{{ $doc->title }}" class="w-[64px] h-[44px] object-cover shadow-[0_1px_2px_rgba(0,0,0,0.08)]">
                                    @elseif($isWordItem)
                                        <!-- Basecamp Light-Blue DOCX Icon with DOCX Pill Badge (Matches media_1793423075427_1ef59bfa.jpg) -->
                                        <svg class="w-[46px] h-[58px]" viewBox="0 0 54 68" fill="none">
                                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#bce3fa"/>
                                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#89c9f4"/>
                                            <rect x="9" y="21" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                            <rect x="9" y="29" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                            <rect x="9" y="37" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                                            <rect x="7" y="48" width="40" height="14" rx="3" fill="#1d74d6"/>
                                            <text x="27" y="58.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="10.5" font-weight="800" letter-spacing="0.4">{{ strtoupper($itemExt ?: 'DOCX') }}</text>
                                        </svg>
                                    @elseif($isSheetItem)
                                        <!-- Basecamp Light-Green XLSX Icon with XLSX Pill Badge -->
                                        <svg class="w-[46px] h-[58px]" viewBox="0 0 54 68" fill="none">
                                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#bbf7d0"/>
                                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#86efac"/>
                                            <rect x="9" y="21" width="36" height="2.6" rx="1.3" fill="#22c55e"/>
                                            <rect x="9" y="29" width="36" height="2.6" rx="1.3" fill="#22c55e"/>
                                            <rect x="9" y="37" width="36" height="2.6" rx="1.3" fill="#22c55e"/>
                                            <rect x="7" y="48" width="40" height="14" rx="3" fill="#16a34a"/>
                                            <text x="27" y="58.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="10.5" font-weight="800" letter-spacing="0.4">{{ strtoupper($itemExt ?: 'XLSX') }}</text>
                                        </svg>
                                    @elseif($isMediaOrGeneric)
                                        <svg class="w-[46px] h-[58px]" viewBox="0 0 54 68" fill="none">
                                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#dedbd6"/>
                                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#c7c3bc"/>
                                            <rect x="9" y="25" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                            <rect x="9" y="34" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                            <rect x="9" y="43" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                            <rect x="9" y="52" width="36" height="2.6" rx="1.3" fill="#9a968e"/>
                                        </svg>
                                    @else
                                        <img src="/uploads/docs/thumb_gem_bidding.png" alt="{{ $doc->title }}" class="w-[48px] h-[62px] object-contain bg-white border border-[#ece9e2] shadow-2xs">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-[17px] font-bold text-[#1d2d35] group-hover:text-[#1b6ac9] leading-snug truncate">
                                        {{ $doc->title }}
                                    </h3>
                                    @if($isFolder)
                                        <p class="text-[15px] text-[#1d2d35] leading-snug mt-0.5">
                                            {{ $folderCountStr }}
                                        </p>
                                    @else
                                        <p class="text-[14.5px] leading-snug mt-0.5">
                                            <span class="text-[#856d47]">{{ $authorName }} • {{ $dateStr }} •</span>
                                            <span class="text-[#1d2d35]">{{ ($doc->content && !str_starts_with($doc->content, 'Document:')) ? $doc->content : $itemSizeStr }}</span>
                                        </p>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="py-12 text-center text-[14.5px] text-[#6d6a65]">
                                No files or documents uploaded yet.
                            </div>
                        @endforelse
                    </div>
                @endif

            </div>
        @endif

    </div>

    <!-- ======================================================================== -->
    <!-- SHARED FULL-SCREEN BASECAMP IMAGE, VIDEO & PDF LIGHTBOX MODAL            -->
    <!-- Image Lightbox matches media_1793345882738_4ea6e000.jpg                  -->
    <!-- Video Lightbox plays .mov / .mp4 files interactively                     -->
    <!-- PDF Lightbox matches media_1793269729585_cd16fa83.jpg                    -->
    <!-- ======================================================================== -->
    <style>
        @keyframes bcDocLightboxFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes bcDocLightboxFadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes bcDocLightboxZoomIn {
            from { opacity: 0; transform: scale(0.84); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes bcDocLightboxZoomOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.84); }
        }
        .bc-doc-lightbox-backdrop-enter {
            animation: bcDocLightboxFadeIn 220ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .bc-doc-lightbox-backdrop-leave {
            animation: bcDocLightboxFadeOut 190ms cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        .bc-doc-lightbox-content-enter {
            animation: bcDocLightboxZoomIn 220ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .bc-doc-lightbox-content-leave {
            animation: bcDocLightboxZoomOut 190ms cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
    </style>
    <div x-show="pdfLightbox"
         x-cloak
         @click="closePdfViewer()"
         @keydown.escape.window="if (pdfLightbox) closePdfViewer()"
         :class="pdfLightboxClosing ? 'bc-doc-lightbox-backdrop-leave' : 'bc-doc-lightbox-backdrop-enter'"
         class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-between px-4 sm:px-8 pt-11 pb-4 bc-pdf-lightbox cursor-zoom-out">

        <!-- Top-Right Close X Button -->
        <button type="button"
                @click.stop="closePdfViewer()"
                class="absolute top-3.5 right-5 p-1.5 text-[#1d2d35] hover:text-black transition-colors cursor-pointer bc-pdf-close z-20"
                title="Close viewer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- ==================================================================== -->
        <!-- MODE A: BASECAMP IMAGE LIGHTBOX (Matches media_1793345882738_4ea6e000.jpg) -->
        <!-- ==================================================================== -->
        <template x-if="lightboxFile.is_photo">
            <div class="w-full flex-1 min-h-0 flex items-center justify-center relative select-none cursor-zoom-out">

                <!-- Left Previous Photo Chevron (<) -->
                <button type="button"
                        x-show="currentLightboxIdx > 0"
                        @click.stop="prevLightboxItem()"
                        class="absolute left-1 sm:left-3 top-1/2 -translate-y-1/2 p-2.5 text-[#1d2d35] hover:text-black transition-colors cursor-pointer z-10"
                        title="Previous image">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Centered High-Resolution Image (Tapping anywhere resigns & animates back) -->
                <div :class="pdfLightboxClosing ? 'bc-doc-lightbox-content-leave' : 'bc-doc-lightbox-content-enter'"
                     class="w-full h-full flex items-center justify-center overflow-hidden px-10 cursor-zoom-out">
                    <img :src="lightboxFile.preview_url + '?v=5'"
                         :alt="lightboxFile.name"
                         class="max-h-full max-w-full w-auto h-auto object-contain cursor-zoom-out">
                </div>

                <!-- Right Next Photo Chevron (>) -->
                <button type="button"
                        x-show="currentLightboxIdx >= 0 && currentLightboxIdx < sortedVaultItems.length - 1"
                        @click.stop="nextLightboxItem()"
                        class="absolute right-1 sm:right-3 top-1/2 -translate-y-1/2 p-2.5 text-[#1d2d35] hover:text-black transition-colors cursor-pointer z-10"
                        title="Next image">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </template>

        <!-- ==================================================================== -->
        <!-- MODE B: BASECAMP VIDEO PLAYER LIGHTBOX (For DSC_0141.MOV, .mp4, etc.) -->
        <!-- ==================================================================== -->
        <template x-if="lightboxFile.is_video && !lightboxFile.is_photo">
            <div class="w-full flex-1 min-h-0 flex items-center justify-center relative select-none">

                <!-- Left Previous Item Chevron (<) -->
                <button type="button"
                        x-show="currentLightboxIdx > 0"
                        @click.stop="prevLightboxItem()"
                        class="absolute left-1 sm:left-3 top-1/2 -translate-y-1/2 p-2.5 text-[#1d2d35] hover:text-black transition-colors cursor-pointer z-10"
                        title="Previous item">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Centered HTML5 Video Player -->
                <div :class="pdfLightboxClosing ? 'bc-doc-lightbox-content-leave' : 'bc-doc-lightbox-content-enter'"
                     class="w-full h-full flex items-center justify-center overflow-hidden px-10">
                    <video controls autoplay playsinline
                           @click.stop
                           :poster="lightboxFile.preview_url + '?v=3'"
                           :src="lightboxFile.video_url || '/uploads/docs/sample_video_preview.mp4'"
                           class="max-h-full max-w-[920px] w-full h-auto rounded-[6px] bg-black shadow-lg cursor-default">
                        Your browser does not support video playback.
                    </video>
                </div>

                <!-- Right Next Item Chevron (>) -->
                <button type="button"
                        x-show="currentLightboxIdx >= 0 && currentLightboxIdx < sortedVaultItems.length - 1"
                        @click.stop="nextLightboxItem()"
                        class="absolute right-1 sm:right-3 top-1/2 -translate-y-1/2 p-2.5 text-[#1d2d35] hover:text-black transition-colors cursor-pointer z-10"
                        title="Next item">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </template>

        <!-- ==================================================================== -->
        <!-- MODE C: DARK CHROME PDF VIEWER (Matches media_1793269729585_cd16fa83.jpg) -->
        <!-- ==================================================================== -->
        <template x-if="!lightboxFile.is_photo && !lightboxFile.is_video">
            <div @click.stop
                 :class="pdfLightboxClosing ? 'bc-doc-lightbox-content-leave' : 'bc-doc-lightbox-content-enter'"
                 class="w-full max-w-[1280px] flex-1 bg-[#202124] rounded-[2px] shadow-2xl overflow-hidden flex flex-col bc-pdf-chrome cursor-default">

                <!-- Top Dark Toolbar (#323639) -->
                <div class="h-[46px] bg-[#323639] px-4 flex items-center justify-between text-[#f1f3f4] text-[13px] border-b border-black/40 select-none flex-shrink-0 bc-pdf-toolbar">
                    <!-- Left: Sidebar Toggle & Document Title -->
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="pdfSidebar = !pdfSidebar" class="p-1.5 rounded hover:bg-white/10 cursor-pointer" title="Toggle page thumbnails">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <span class="font-medium truncate max-w-[260px]" x-text="lightboxFile.name.replace('.pdf', '.docx')"></span>
                    </div>

                    <!-- Center: Page Counter (1 / 5) | Zoom (- 100% +) | Fit & Rotate -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 pr-2 border-r border-white/20">
                            <span class="bg-[#202124] px-2 py-0.5 rounded text-[12px] font-medium" x-text="pdfPage">1</span>
                            <span class="text-white/70">/</span>
                            <span>5</span>
                        </div>

                        <div class="flex items-center gap-1 px-1 border-r border-white/20">
                            <button type="button" @click="pdfZoom = Math.max(50, pdfZoom - 10)" class="p-1 rounded hover:bg-white/10 cursor-pointer" title="Zoom out">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14"/></svg>
                            </button>
                            <span class="bg-[#202124] px-2 py-0.5 rounded text-[12px] font-medium min-w-[48px] text-center" x-text="pdfZoom + '%'">100%</span>
                            <button type="button" @click="pdfZoom = Math.min(200, pdfZoom + 10)" class="p-1 rounded hover:bg-white/10 cursor-pointer" title="Zoom in">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                            </button>
                        </div>

                        <div class="hidden sm:flex items-center gap-1.5 pl-1">
                            <button type="button" @click="pdfZoom = 100" class="p-1.5 rounded hover:bg-white/10 cursor-pointer" title="Fit to page">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="4" y="3" width="16" height="18" rx="1"/>
                                    <path stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/>
                                </svg>
                            </button>
                            <button type="button" @click="pdfRotate = (pdfRotate + 90) % 360" class="p-1.5 rounded hover:bg-white/10 cursor-pointer" title="Rotate counter-clockwise">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M4.5 9A8 8 0 1112 20"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Download, Print & More -->
                    <div class="flex items-center gap-2">
                        <a :href="lightboxFile.pdf_url + '?v=2'"
                           :download="lightboxFile.name"
                           class="p-1.5 rounded hover:bg-white/10 transition-colors"
                           title="Download PDF">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                            </svg>
                        </a>
                        <button type="button" @click="printPdfDocument(lightboxFile.pdf_url)" class="p-1.5 rounded hover:bg-white/10 transition-colors cursor-pointer" title="Print PDF">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Main Viewer Body: Left Thumbnail Rail + Right Scrollable Multi-Page Document Canvas -->
                <div class="flex-1 flex min-h-0 overflow-hidden bc-pdf-body">

                    <!-- Left Thumbnail Sidebar (1..5) -->
                    <div x-show="pdfSidebar" class="w-[215px] bg-[#282b2e] border-r border-[#3c4043] overflow-y-auto py-5 flex flex-col items-center gap-5 flex-shrink-0 bc-pdf-sidebar">
                        <template x-for="p in [1, 2, 3, 4, 5]" :key="p">
                            <div @click="scrollToPdfPage(p)" class="flex flex-col items-center gap-1.5 cursor-pointer group">
                                <div class="p-0.5 rounded-[2px] transition-all"
                                     :class="pdfPage === p ? 'ring-[3px] ring-[#8ab4f8]' : 'opacity-80 group-hover:opacity-100'">
                                    <img :src="(lightboxFile.preview_url && lightboxFile.preview_url.includes('flutter_proposal')) ? ('/uploads/docs/flutter_page_' + p + '.png?v=5') : (p === 1 ? (lightboxFile.preview_url + '?v=5') : ('/uploads/docs/comm_page_' + p + '.png?v=5'))"
                                         :alt="'Page ' + p"
                                         class="w-[96px] h-[128px] bg-white object-contain shadow">
                                </div>
                                <span class="text-[11.5px] text-[#e8eaed]" x-text="p"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Right Scrollable 5-Page Document Canvas (#525659) -->
                    <div class="flex-1 bg-[#525659] overflow-y-auto py-5 px-4 flex flex-col items-center gap-6 bc-pdf-canvas">
                        <template x-for="p in [1, 2, 3, 4, 5]" :key="'page-' + p">
                            <div :id="'pdf-viewer-page-' + p"
                                 @mouseenter="pdfPage = p"
                                 class="bg-white shadow-[0_4px_16px_rgba(0,0,0,0.45)] transition-transform duration-150 origin-top bc-pdf-page"
                                 :style="'width: ' + Math.round(640 * (pdfZoom / 100)) + 'px; transform: rotate(' + pdfRotate + 'deg)'">
                                <img :src="(lightboxFile.preview_url && lightboxFile.preview_url.includes('flutter_proposal')) ? ('/uploads/docs/flutter_page_' + p + '.png?v=5') : (p === 1 ? (lightboxFile.preview_url + '?v=5') : ('/uploads/docs/comm_page_' + p + '.png?v=5'))"
                                     :alt="'Page ' + p"
                                     class="w-full h-auto block select-none">
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Bottom Caption Bar -->
        <!-- Image: IMG_3445.JPG · 3.82 MB · Go to page · View full-size · Download -->
        <!-- PDF:   Copy of Commercial bid.pdf · 237 KB · Go to page · Download -->
        <div @click.stop class="mt-2.5 flex flex-wrap items-center justify-center gap-1.5 text-[13px] text-[#6d6a65] bc-pdf-caption cursor-default">
            <span x-text="lightboxFile.name"></span>
            <template x-if="lightboxFile.size">
                <span class="inline-flex items-center gap-1.5">
                    <span>·</span>
                    <span x-text="lightboxFile.size"></span>
                </span>
            </template>
            <span>·</span>
            <a :href="lightboxFile.page_url"
               @click="if (window.location.href.includes('file=')) { $event.preventDefault(); closePdfViewer(); }"
               class="underline hover:text-[#1d2d35] cursor-pointer">
                Go to page
            </a>
            <template x-if="lightboxFile.is_photo">
                <span class="inline-flex items-center gap-1.5">
                    <span>·</span>
                    <a :href="lightboxFile.pdf_url + '?v=3'"
                       target="_blank"
                       class="underline hover:text-[#1d2d35]">
                        View full-size
                    </a>
                </span>
            </template>
            <span>·</span>
            <a :href="lightboxFile.pdf_url + '?v=3'"
               :download="lightboxFile.name"
               class="underline hover:text-[#1d2d35]">
                Download
            </a>
        </div>
    </div>

    <!-- Floating Bottom-Left "↑ Back to top" Pill Button (Matches media_1793346008975_cf0b4e19.png) -->
    <button type="button"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-11 left-12 z-30 inline-flex items-center gap-1 px-2.5 py-1 rounded-[5px] bg-[#fbfaf8] hover:bg-white border border-[#d8d4cc] shadow-2xs text-[12px] text-[#1d2d35] cursor-pointer transition-colors">
        <span>↑</span>
        <span>Back to top</span>
    </button>

</div>
@endsection
