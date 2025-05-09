@extends('layouts.layout')

@section('title', 'Tambah Ruang Kelas')

@section('header_title', 'Tambah Ruang Kelas')

@section('content')
    <div class="ruang-kelas-create-container">
        <h1>Tambah Ruang Kelas</h1>

        <form action="{{ route('admin.ruang_kelas.store') }}" method="POST" class="ruang-kelas-form">
            @csrf
            <input type="text" name="kode_ruangan" placeholder="Kode Ruangan" required class="input-field"><br>
            <input type="text" name="nama_ruangan" placeholder="Nama Ruangan" required class="input-field"><br>
            <input type="number" name="lantai" placeholder="Lantai" required class="input-field"><br>
            <input type="text" name="nama_gedung" placeholder="Nama Gedung" required class="input-field"><br>
            <button type="submit" class="submit-btn">Simpan</button>
        </form>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/create.css') }}">
@endpush
