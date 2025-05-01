@extends('layouts.layout')

@section('title', 'Matakuliah')

@section('header_title', 'Manajemen Matakuliah')

@section('content')
    <h1>Tambah Mata Kuliah</h1>
    <form action="{{ route('admin.mata_kuliah.store') }}" method="POST">
        @csrf
        <input type="text" name="kode_matkul" placeholder="Kode Mata Kuliah" required><br>
        <input type="text" name="nama_matkul" placeholder="Nama Mata Kuliah" required><br>
        <input type="number" name="sks" placeholder="Jumlah SKS" required><br>
        <input type="number" name="jumlah_kelas" placeholder="Jumlah Kelas" required><br> <!-- Tambahan -->
        <button type="submit">Simpan</button>
    </form>
@endsection

@push('css')
    <!-- <link rel="stylesheet" href="{{ asset('css/admin/matakuliah/create.css') }}"> -->
@endpush
