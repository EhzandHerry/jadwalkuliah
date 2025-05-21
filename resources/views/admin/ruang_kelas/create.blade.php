@extends('layouts.layout')

@section('title', 'Tambah Ruang Kelas')

@section('header_title', 'Tambah Ruang Kelas')

@section('content')
    <div class="ruang-kelas-create-container">
        <h1>Tambah Ruang Kelas</h1>

        <form action="{{ route('admin.ruang_kelas.store') }}" method="POST" class="ruang-kelas-form">
            @csrf
            <div class="form-group">
                <label for="nama_ruangan">Nama Ruangan</label>
                <input
                    type="text"
                    id="nama_ruangan"
                    name="nama_ruangan"
                    placeholder="Nama Ruangan"
                    required
                    class="input-field"
                >
            </div>

            <div class="form-group">
                <label for="nama_gedung">Nama Gedung</label>
                <input
                    type="text"
                    id="nama_gedung"
                    name="nama_gedung"
                    placeholder="Nama Gedung"
                    required
                    class="input-field"
                >
            </div>

            <div class="form-group">
                <label for="kapasitas">Kapasitas</label>
                <input
                    type="number"
                    id="kapasitas"
                    name="kapasitas"
                    placeholder="Kapasitas (jumlah maksimal)"
                    min="1"
                    required
                    class="input-field"
                >
            </div>

            <button type="submit" class="submit-btn">Simpan</button>
        </form>

        <!-- Back Button -->
        <a href="{{ route('admin.ruang_kelas.index') }}" class="back-btn">← Kembali ke Daftar Ruang Kelas</a>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/create.css') }}">
@endpush
