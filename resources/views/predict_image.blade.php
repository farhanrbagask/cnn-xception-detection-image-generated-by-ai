<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Gambar — CNN Xception AI Detector</title>
    <meta name="description" content="Uji apakah gambar Anda dibuat oleh AI atau foto asli menggunakan model Xception CNN yang telah terlatih.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-start: #667eea;
            --primary-end: #764ba2;
            --real-color: #10b981;
            --fake-color: #ef4444;
            --card-shadow: 0 4px 24px rgba(0,0,0,0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f8; min-height: 100vh; }

        /* ─── SIDEBAR ─── */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(160deg, var(--primary-start) 0%, var(--primary-end) 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(102,126,234,0.3);
        }
        .sidebar-brand {
            padding: 28px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .sidebar-brand h4 {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }
        .sidebar-brand p {
            color: rgba(255,255,255,0.65);
            font-size: 0.75rem;
            margin: 4px 0 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 10px;
            margin: 3px 10px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateX(4px);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .sidebar .nav-link i { width: 18px; text-align: center; }

        /* ─── MAIN CONTENT ─── */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 32px;
            min-height: 100vh;
        }

        /* ─── PAGE HEADER ─── */
        .page-header-card {
            background: linear-gradient(135deg, var(--primary-start), var(--primary-end));
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 28px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(102,126,234,0.35);
        }
        .page-header-card::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 180px; height: 180px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .page-header-card::after {
            content: '';
            position: absolute;
            bottom: -60px; right: 80px;
            width: 240px; height: 240px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .page-header-card h2 { font-weight: 800; font-size: 1.75rem; margin-bottom: 6px; }
        .page-header-card p { opacity: 0.85; margin: 0; font-size: 0.95rem; }

        /* ─── CARDS ─── */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }
        .card:hover { box-shadow: 0 8px 32px rgba(0,0,0,0.12); }
        .card-title-section {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .card-title-section .icon-badge {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-start), var(--primary-end));
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 0.85rem;
        }

        /* ─── UPLOAD ZONE ─── */
        .upload-zone {
            border: 2.5px dashed #c7d2fe;
            border-radius: 16px;
            background: #f8faff;
            padding: 48px 24px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--primary-start);
            background: #eef0ff;
        }
        .upload-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .upload-icon {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #eef0ff, #e0e3ff);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 1.8rem;
            color: var(--primary-start);
            transition: var(--transition);
        }
        .upload-zone:hover .upload-icon { transform: scale(1.08); }
        .upload-zone h6 { font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .upload-zone p { color: #64748b; font-size: 0.85rem; margin: 0; }

        /* ─── PREVIEW BOX ─── */
        #preview-box {
            display: none;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            background: #000;
        }
        #preview-box img {
            width: 100%;
            max-height: 320px;
            object-fit: contain;
            display: block;
        }
        .preview-overlay {
            position: absolute; top: 12px; right: 12px;
        }
        .preview-badge {
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
        #remove-preview {
            position: absolute; top: 12px; left: 12px;
            background: rgba(239,68,68,0.85);
            color: white; border: none;
            width: 32px; height: 32px; border-radius: 50%;
            cursor: pointer; font-size: 0.8rem;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition);
        }
        #remove-preview:hover { background: #ef4444; transform: scale(1.1); }

        /* ─── MODEL SELECTOR ─── */
        .model-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .model-card:hover { border-color: var(--primary-start); background: #f8faff; }
        .model-card.selected { border-color: var(--primary-start); background: #eef0ff; }
        .model-card input[type="radio"] { accent-color: var(--primary-start); width: 18px; height: 18px; }
        .model-card-label { flex: 1; }
        .model-card-label strong { display: block; font-size: 0.9rem; color: #1e293b; }
        .model-card-label small { color: #64748b; font-size: 0.78rem; }
        .model-badge { background: #eef0ff; color: var(--primary-start); font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; }

        /* ─── SUBMIT BUTTON ─── */
        .btn-predict {
            background: linear-gradient(135deg, var(--primary-start), var(--primary-end));
            border: none;
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .btn-predict:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102,126,234,0.4);
            color: white;
        }
        .btn-predict:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        /* ─── LOADING STATE ─── */
        .loading-overlay {
            display: none;
            text-align: center;
            padding: 48px 24px;
        }
        .spinner-ring {
            width: 64px; height: 64px;
            border: 5px solid #e2e8f0;
            border-top-color: var(--primary-start);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-overlay p { color: #64748b; font-size: 0.9rem; }
        .loading-overlay strong { color: #1e293b; display: block; font-size: 1.05rem; margin-bottom: 4px; }

        /* ─── RESULT CARD ─── */
        .result-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
            animation: slideUp 0.5s ease both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .result-header-real {
            background: linear-gradient(135deg, #059669, #10b981);
            padding: 28px;
            color: white;
            text-align: center;
        }
        .result-header-fake {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            padding: 28px;
            color: white;
            text-align: center;
        }
        .result-icon {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 2.2rem;
            animation: pulse 2s ease infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255,255,255,0); }
        }
        .result-label {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .result-sub { opacity: 0.85; font-size: 0.9rem; margin-top: 4px; }
        .result-body { padding: 28px; background: white; }

        /* ─── CONFIDENCE BARS ─── */
        .conf-bar-wrap { margin-bottom: 14px; }
        .conf-bar-label {
            display: flex; justify-content: space-between;
            font-size: 0.82rem; font-weight: 600; margin-bottom: 6px;
        }
        .conf-bar-label .pct { font-weight: 700; }
        .progress { height: 10px; border-radius: 20px; background: #f1f5f9; }
        .progress-bar-real { background: linear-gradient(90deg, #059669, #34d399); border-radius: 20px; transition: width 1s ease; }
        .progress-bar-fake { background: linear-gradient(90deg, #dc2626, #f87171); border-radius: 20px; transition: width 1s ease; }

        /* ─── INFO GRID ─── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }
        .info-item {
            background: #f8faff;
            border-radius: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
        }
        .info-item-label { font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-item-value { font-size: 0.9rem; color: #1e293b; font-weight: 700; }

        /* ─── DISCLAIMER ─── */
        .disclaimer-box {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 1px solid #fcd34d;
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 20px;
        }
        .disclaimer-box p { margin: 0; font-size: 0.82rem; color: #92400e; }
        .disclaimer-box strong { color: #78350f; }

        /* ─── NO MODEL WARNING ─── */
        .no-model-box {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1.5px solid #fca5a5;
            border-radius: 16px;
            padding: 40px 32px;
            text-align: center;
        }
        .no-model-box .icon { font-size: 3rem; color: #ef4444; margin-bottom: 16px; }
        .no-model-box h5 { font-weight: 700; color: #7f1d1d; }
        .no-model-box p { color: #991b1b; font-size: 0.875rem; }

        /* ─── ALERT ─── */
        .alert-custom {
            border-radius: 12px;
            border: none;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
        }
        .alert-danger-custom { background: #fef2f2; color: #7f1d1d; border: 1px solid #fecaca; }
    </style>
</head>

<body>
<div class="d-flex">

    {{-- ────────────────── SIDEBAR ────────────────── --}}
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-brain me-2"></i>CNN AI Detector</h4>
            <p>Xception Model v1.0</p>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('upload.*') ? 'active' : '' }}" href="{{ route('upload.page') }}">
                    <i class="fas fa-upload"></i> Upload Dataset
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('results') ? 'active' : '' }}" href="{{ route('results') }}">
                    <i class="fas fa-chart-bar"></i> Hasil Training
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('predict.image.*') ? 'active' : '' }}" href="{{ route('predict.image.page') }}">
                    <i class="fas fa-search"></i> Uji Gambar
                </a>
            </li>
        </ul>
    </div>

    {{-- ────────────────── MAIN CONTENT ────────────────── --}}
    <div class="main-content">

        {{-- Page Header --}}
        <div class="page-header-card">
            <div style="position:relative;z-index:1;">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <div>
                        <h2 class="mb-0">Uji Gambar Tunggal</h2>
                        <p>Deteksi apakah gambar Anda Real atau AI-Generated menggunakan model Xception</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error Alert --}}
        @if (session('error'))
            <div class="alert-custom alert-danger-custom mb-4">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-custom alert-danger-custom mb-4">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="row g-4">

            {{-- ── LEFT: FORM ── --}}
            <div class="col-lg-5">

                @if(empty($availableModels))
                    {{-- Tidak ada model tersedia --}}
                    <div class="card p-4">
                        <div class="no-model-box">
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <h5>Belum Ada Model Tersedia</h5>
                            <p>
                                Anda perlu melatih model terlebih dahulu sebelum dapat melakukan prediksi.
                                Upload dataset di halaman <strong>Upload Dataset</strong> untuk memulai training.
                            </p>
                            <a href="{{ route('upload.page') }}" class="btn mt-3" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-radius:10px;font-weight:600;padding:12px 28px;">
                                <i class="fas fa-upload me-2"></i>Upload Dataset
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card p-4">
                        <form id="predict-form" action="{{ route('predict.image.single') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Upload Zone --}}
                            <div class="card-title-section">
                                <div class="icon-badge"><i class="fas fa-image"></i></div>
                                Upload Gambar
                            </div>

                            <div class="upload-zone mb-3" id="upload-zone">
                                <input type="file" name="image" id="image-input" accept="image/jpeg,image/jpg,image/png,image/bmp,image/webp">
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <h6>Drag & drop atau klik untuk memilih gambar</h6>
                                <p>Mendukung JPG, PNG, BMP, WebP &bull; Maks 10MB</p>
                            </div>

                            {{-- Preview --}}
                            <div id="preview-box" class="mb-3">
                                <button type="button" id="remove-preview" title="Hapus gambar">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="preview-overlay">
                                    <span class="preview-badge" id="preview-filename"></span>
                                </div>
                                <img id="preview-img" src="" alt="Preview gambar">
                            </div>

                            {{-- Pilih Model --}}
                            <div class="card-title-section mt-4">
                                <div class="icon-badge"><i class="fas fa-robot"></i></div>
                                Pilih Model
                            </div>

                            <div class="d-flex flex-column gap-2 mb-4">
                                @foreach($availableModels as $i => $model)
                                    <label class="model-card {{ $i === 0 ? 'selected' : '' }}" id="model-card-{{ $model['split'] }}">
                                        <input type="radio" name="split_ratio" value="{{ $model['split'] }}"
                                            {{ $i === 0 ? 'checked' : '' }}
                                            onchange="selectModel({{ $model['split'] }})">
                                        <div class="model-card-label">
                                            <strong>Model Split {{ $model['split'] }}/{{ 100 - $model['split'] }}</strong>
                                            <small>{{ $model['split'] }}% Train &bull; {{ 100 - $model['split'] }}% Test &bull; {{ $model['size'] }}</small>
                                        </div>
                                        <span class="model-badge">{{ $model['split'] }}%</span>
                                    </label>
                                @endforeach
                            </div>

                            <button type="submit" class="btn-predict" id="submit-btn" disabled>
                                <i class="fas fa-search me-2"></i>Analisis Gambar
                            </button>
                        </form>

                        {{-- Disclaimer --}}
                        <div class="disclaimer-box">
                            <p>
                                <strong><i class="fas fa-exclamation-triangle me-1"></i>Catatan Penting:</strong>
                                Model ini dilatih pada dataset spesifik. Akurasi pada gambar AI modern
                                (Midjourney, DALL-E, Stable Diffusion) dapat bervariasi. Hasil ini bersifat eksperimental.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── RIGHT: RESULT ── --}}
            <div class="col-lg-7">

                @if(isset($resultData))
                    {{-- ── HASIL PREDIKSI ── --}}
                    <div class="result-card">
                        <div class="{{ $resultData['prediction'] === 'real' ? 'result-header-real' : 'result-header-fake' }}">
                            <div class="result-icon">
                                @if($resultData['prediction'] === 'real')
                                    <i class="fas fa-check-circle"></i>
                                @else
                                    <i class="fas fa-robot"></i>
                                @endif
                            </div>
                            <div class="result-label">
                                {{ $resultData['prediction'] === 'real' ? '✓ REAL' : '⚠ AI GENERATED' }}
                            </div>
                            <div class="result-sub">
                                Confidence: {{ $resultData['confidence'] }}%
                            </div>
                        </div>

                        <div class="result-body">
                            {{-- Preview gambar --}}
                            <div class="mb-4 text-center">
                                <img src="{{ $resultData['preview_url'] }}"
                                    alt="{{ $resultData['original_filename'] }}"
                                    style="max-height:240px;max-width:100%;border-radius:12px;object-fit:contain;box-shadow:0 4px 20px rgba(0,0,0,0.12);">
                                <div class="mt-2" style="font-size:0.8rem;color:#94a3b8;">
                                    <i class="fas fa-file-image me-1"></i>{{ $resultData['original_filename'] }}
                                </div>
                            </div>

                            {{-- Confidence Bars --}}
                            <h6 style="font-weight:700;color:#1e293b;margin-bottom:14px;">
                                <i class="fas fa-chart-bar me-2" style="color:#667eea;"></i>Distribusi Confidence
                            </h6>

                            <div class="conf-bar-wrap">
                                <div class="conf-bar-label">
                                    <span><i class="fas fa-check-circle me-1" style="color:#10b981;"></i>Real</span>
                                    <span class="pct" style="color:#10b981;">{{ $resultData['confidence_real'] }}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar-real" style="width: {{ $resultData['confidence_real'] }}%;"></div>
                                </div>
                            </div>

                            <div class="conf-bar-wrap">
                                <div class="conf-bar-label">
                                    <span><i class="fas fa-robot me-1" style="color:#ef4444;"></i>AI Generated</span>
                                    <span class="pct" style="color:#ef4444;">{{ $resultData['confidence_fake'] }}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar-fake" style="width: {{ $resultData['confidence_fake'] }}%;"></div>
                                </div>
                            </div>

                            {{-- Info Grid --}}
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-item-label">Model Digunakan</div>
                                    <div class="info-item-value">Split {{ $resultData['split_ratio'] }}/{{ 100 - $resultData['split_ratio'] }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-item-label">Ukuran Asli</div>
                                    <div class="info-item-value">
                                        {{ $resultData['original_size']['width'] }}×{{ $resultData['original_size']['height'] }} px
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-item-label">Input ke Model</div>
                                    <div class="info-item-value">299×299 px</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-item-label">Metode Resize</div>
                                    <div class="info-item-value">Bilinear Interpolasi</div>
                                </div>
                            </div>

                            {{-- Coba Gambar Lain --}}
                            <div class="mt-4 d-flex gap-3">
                                <a href="{{ route('predict.image.page') }}"
                                    class="btn flex-fill"
                                    style="border:2px solid #667eea;color:#667eea;border-radius:10px;font-weight:600;padding:12px;">
                                    <i class="fas fa-redo me-2"></i>Uji Gambar Lain
                                </a>
                                <a href="{{ route('results') }}"
                                    class="btn flex-fill"
                                    style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-radius:10px;font-weight:600;padding:12px;">
                                    <i class="fas fa-chart-bar me-2"></i>Lihat Hasil Training
                                </a>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- ── PLACEHOLDER (belum ada prediksi) ── --}}
                    <div class="card p-5 text-center h-100 d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 480px; background: linear-gradient(135deg, #f8faff, #f0f4ff);">

                        <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#eef0ff,#e0e3ff);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2.8rem;color:#667eea;">
                            <i class="fas fa-search"></i>
                        </div>

                        <h4 style="font-weight:800;color:#1e293b;margin-bottom:10px;">Belum Ada Hasil</h4>
                        <p style="color:#64748b;max-width:340px;margin:0 auto 28px;font-size:0.9rem;">
                            Upload sebuah gambar dan pilih model yang ingin digunakan, lalu klik
                            <strong>"Analisis Gambar"</strong> untuk melihat apakah gambar tersebut
                            asli atau dibuat oleh AI.
                        </p>

                        <div class="d-flex flex-column gap-3 w-100" style="max-width: 360px;">
                            <div class="d-flex align-items-center gap-3 text-start p-3" style="background:white;border-radius:12px;border:1px solid #e2e8f0;">
                                <div style="width:36px;height:36px;min-width:36px;background:#eef0ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#667eea;font-size:1rem;">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <div>
                                    <strong style="font-size:0.85rem;color:#1e293b;">1. Upload Gambar</strong>
                                    <p style="font-size:0.78rem;color:#94a3b8;margin:0;">Pilih atau drag gambar yang ingin diuji</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-start p-3" style="background:white;border-radius:12px;border:1px solid #e2e8f0;">
                                <div style="width:36px;height:36px;min-width:36px;background:#eef0ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#667eea;font-size:1rem;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div>
                                    <strong style="font-size:0.85rem;color:#1e293b;">2. Pilih Model</strong>
                                    <p style="font-size:0.78rem;color:#94a3b8;margin:0;">Pilih model berdasarkan split ratio yang diinginkan</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-start p-3" style="background:white;border-radius:12px;border:1px solid #e2e8f0;">
                                <div style="width:36px;height:36px;min-width:36px;background:#eef0ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#667eea;font-size:1rem;">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div>
                                    <strong style="font-size:0.85rem;color:#1e293b;">3. Lihat Hasil</strong>
                                    <p style="font-size:0.78rem;color:#94a3b8;margin:0;">Dapatkan prediksi + confidence score secara instan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Loading State (ditampilkan via JS saat submit) --}}
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner-ring"></div>
                    <strong>Sedang Menganalisis Gambar...</strong>
                    <p>Model Xception sedang memproses gambar Anda.<br>Mohon tunggu beberapa saat.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ─── Drag & Drop ───
    const uploadZone = document.getElementById('upload-zone');
    const imageInput = document.getElementById('image-input');
    const previewBox = document.getElementById('preview-box');
    const previewImg = document.getElementById('preview-img');
    const previewFilename = document.getElementById('preview-filename');
    const removeBtn = document.getElementById('remove-preview');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('predict-form');
    const loadingOverlay = document.getElementById('loading-overlay');

    if (uploadZone) {
        ['dragenter', 'dragover'].forEach(e => {
            uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(e => {
            uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.remove('drag-over'); });
        });
        uploadZone.addEventListener('drop', ev => {
            const file = ev.dataTransfer.files[0];
            if (file) handleFile(file);
        });
    }

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            if (this.files[0]) handleFile(this.files[0]);
        });
    }

    function handleFile(file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/bmp', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Format tidak didukung. Gunakan JPG, PNG, BMP, atau WebP.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maks 10MB.');
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewFilename.textContent = file.name;
            uploadZone.style.display = 'none';
            previewBox.style.display = 'block';
            submitBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            previewImg.src = '';
            imageInput.value = '';
            previewBox.style.display = 'none';
            uploadZone.style.display = 'block';
            submitBtn.disabled = true;
        });
    }

    // ─── Model Card Selection ───
    function selectModel(split) {
        document.querySelectorAll('.model-card').forEach(c => c.classList.remove('selected'));
        const card = document.getElementById('model-card-' + split);
        if (card) card.classList.add('selected');
    }

    // ─── Loading on Submit ───
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!imageInput || !imageInput.files[0]) { e.preventDefault(); return; }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menganalisis...';

            // Sembunyikan result lama, tampilkan loading
            const resultCard = document.querySelector('.result-card');
            const placeholder = document.querySelector('.col-lg-7 .card');
            if (resultCard) resultCard.style.display = 'none';
            if (placeholder && !resultCard) placeholder.style.display = 'none';
            if (loadingOverlay) loadingOverlay.style.display = 'block';
        });
    }
</script>
</body>
</html>
