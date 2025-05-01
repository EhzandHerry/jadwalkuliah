@extends('layouts.layout')

@section('title', 'Dashboard Admin')

@section('header_title', 'Dashboard')

@section('content')
<h1>Tambah Ruang Kelas</h1>
    <form action="{{ route('admin.ruang_kelas.store') }}" method="POST">
        @csrf
        <input type="text" name="kode_ruangan" placeholder="Kode Ruangan" required><br>
        <input type="text" name="nama_ruangan" placeholder="Nama Ruangan" required><br>
        <input type="number" name="lantai" placeholder="Lantai" required><br>
        <input type="text" name="nama_gedung" placeholder="Nama Gedung" required><br>
        <button type="submit">Simpan</button>
    </form>
@endsection


