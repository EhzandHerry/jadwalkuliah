@extends('layouts.layout')

@section('title', 'Edit Ruang Kelas')
@section('header_title', 'Edit Ruang Kelas')

@section('content')
    <div class="edit-ruang-container">
        
        {{-- Menggunakan class baru untuk form --}}
        <form action="{{ route('admin.ruang_kelas.update', $ruang->id) }}" method="POST" class="edit-ruang-form">
            @csrf
            @method('PUT')
            
            <h1>Edit Ruang Kelas</h1>

            {{-- Menampilkan pesan error umum jika ada --}}
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Menambahkan struktur .form-group dan label --}}
            <div class="form-group">
                <label for="nama_ruangan">Nama Ruangan</label>
                <input 
                    type="text" 
                    name="nama_ruangan" 
                    id="nama_ruangan" 
                    value="{{ old('nama_ruangan', $ruang->nama_ruangan) }}" 
                    required 
                    class="input-field {{ $errors->has('nama_ruangan') ? 'error' : '' }}"
                >
                {{-- Pesan error spesifik untuk nama_ruangan --}}
                @error('nama_ruangan')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="nama_gedung">Nama Gedung</label>
                <input 
                    type="text" 
                    name="nama_gedung" 
                    id="nama_gedung" 
                    value="{{ old('nama_gedung', $ruang->nama_gedung) }}" 
                    required 
                    class="input-field {{ $errors->has('nama_gedung') ? 'error' : '' }}"
                >
                {{-- Pesan error spesifik untuk nama_gedung --}}
                @error('nama_gedung')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="kapasitas_kelas">Kapasitas Kelas (jumlah kelas simultan)</label>
                <input 
                    type="number" 
                    name="kapasitas_kelas" 
                    id="kapasitas_kelas" 
                    value="{{ old('kapasitas_kelas', $ruang->kapasitas_kelas) }}" 
                    min="1"
                    required 
                    class="input-field {{ $errors->has('kapasitas_kelas') ? 'error' : '' }}"
                    oninput="updateKapasitas()"
                >
                {{-- Pesan error spesifik untuk kapasitas_kelas --}}
                @error('kapasitas_kelas')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="kapasitas_display">Kapasitas Total (otomatis)</label>
                <input 
                    type="number" 
                    id="kapasitas_display" 
                    value="{{ old('kapasitas_kelas', $ruang->kapasitas_kelas) * 50 }}" 
                    readonly
                    class="input-field readonly"
                >
                <small class="form-hint">Kapasitas dihitung otomatis: Kapasitas Kelas × 50</small>
            </div>
            
            {{-- Menambahkan pembungkus .form-actions dan tombol Batal --}}
            <div class="form-actions">
                <button type="submit" class="btn update-btn">Simpan</button>
                <a href="{{ route('admin.ruang_kelas.index') }}" class="btn cancel-btn">Batal</a>
            </div>

        </form>
    </div>

    <script>
        function updateKapasitas() {
            const kapasitasKelas = document.getElementById('kapasitas_kelas').value;
            const kapasitasDisplay = document.getElementById('kapasitas_display');
            kapasitasDisplay.value = kapasitasKelas ? kapasitasKelas * 50 : 0;
        }
    </script>
@endsection

@push('css')
    {{-- Pastikan ini me-link ke file CSS yang benar --}}
    <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/edit.css') }}">
    <style>
        .readonly {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }
        .form-hint {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
    </style>
@endpush