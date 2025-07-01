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

            {{-- Menambahkan struktur .form-group dan label --}}
            <div class="form-group">
                <label for="nama_ruangan">Nama Ruangan</label>
                <input type="text" name="nama_ruangan" id="nama_ruangan" value="{{ old('nama_ruangan', $ruang->nama_ruangan) }}" required class="input-field">
            </div>
            
            <div class="form-group">
                <label for="nama_gedung">Nama Gedung</label>
                <input type="text" name="nama_gedung" id="nama_gedung" value="{{ old('nama_gedung', $ruang->nama_gedung) }}" required class="input-field">
            </div>
            
            <div class="form-group">
                <label for="kapasitas">Kapasitas (jumlah duduk)</label>
                <input type="number" name="kapasitas" id="kapasitas" value="{{ old('kapasitas', $ruang->kapasitas) }}" required class="input-field">
            </div>

            <div class="form-group">
                <label for="kapasitas_kelas">Kapasitas Kelas (jumlah kelas simultan)</label>
                <input type="number" name="kapasitas_kelas" id="kapasitas_kelas" value="{{ old('kapasitas_kelas', $ruang->kapasitas_kelas) }}" required class="input-field">
            </div>
            
            {{-- Menambahkan pembungkus .form-actions dan tombol Batal --}}
            <div class="form-actions">
                <button type="submit" class="btn update-btn">Simpan</button>
                <a href="{{ route('admin.ruang_kelas.index') }}" class="btn cancel-btn">Batal</a>
            </div>

        </form>
    </div>
@endsection

@push('css')
    {{-- Pastikan ini me-link ke file CSS yang benar --}}
    <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/edit.css') }}">
@endpush
