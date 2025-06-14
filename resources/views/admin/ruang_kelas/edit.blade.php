@extends('layouts.layout')

@section('title', 'Edit Ruang Kelas')

@section('header_title', 'Edit Ruang Kelas')

@section('content')
    <div class="edit-ruang-container">
        <h1>Edit Ruang Kelas</h1>

        <form action="{{ route('admin.ruang_kelas.update', $ruang->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="text" name="nama_ruangan" value="{{ $ruang->nama_ruangan }}" placeholder="Nama Ruangan" required><br>
            <input type="text" name="nama_gedung" value="{{ $ruang->nama_gedung }}" placeholder="Nama Gedung" required><br>
            <input type="number" name="kapasitas" value="{{ $ruang->kapasitas }}" placeholder="Kapasitas" required><br>
            <input type="number" name="kapasitas_kelas" value="{{ $ruang->kapasitas_kelas }}" placeholder="Kapasitas Kelas" required><br>

            <button type="submit">Update</button>
        </form>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/edit.css') }}">
@endpush
