@extends('layouts.layout')

@section('title', 'Matakuliah')

@section('header_title', 'Manajemen Matakuliah')

@section('content')
    <h1>Tambah Mata Kuliah</h1>
    <form action="{{ route('admin.mata_kuliah.store') }}" method="POST" class="add-matkul-form">
        @csrf
        <input type="text" name="kode_matkul" placeholder="Kode Mata Kuliah" required class="input-field"><br>
        <input type="text" name="nama_matkul" placeholder="Nama Mata Kuliah" required class="input-field"><br>
        <input type="number" name="sks" placeholder="Jumlah SKS" required class="input-field"><br>
        <input type="number" name="jumlah_kelas" placeholder="Jumlah Kelas" required class="input-field"><br> <!-- Tambahan -->
        <button type="submit" class="submit-btn">Simpan</button>
    </form>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/matakuliah/create.css') }}">
@endpush
