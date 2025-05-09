@extends('layouts.layout')

@section('title', 'Edit Kelas')

@section('header_title', 'Edit Kelas')

@section('content')
    <h1>Edit Kelas</h1>

    <form action="{{ route('admin.kelas.update', $kelas->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Keep the existing values intact -->
        <!-- <input type="text" name="kode_matkul" value="{{ $kelas->kode_matkul }}" placeholder="Kode Mata Kuliah" required><br>
        <input type="text" name="kelas" value="{{ $kelas->kelas }}" placeholder="Nama Kelas" required><br>
        <input type="number" name="lantai" value="{{ $kelas->lantai }}" placeholder="Lantai" required><br>
        <input type="text" name="nama_gedung" value="{{ $kelas->nama_gedung }}" placeholder="Nama Gedung" required><br> -->

        <!-- Dropdown for Ruang Kelas -->
        <select name="ruang_kelas_id" required>
            <option value="">Pilih Ruang Kelas</option>
            @foreach ($ruangKelas as $ruang)
                <option value="{{ $ruang->id }}" {{ $kelas->ruang_kelas_id == $ruang->id ? 'selected' : '' }}>
                    {{ $ruang->nama_ruangan }} - {{ $ruang->nama_gedung }}
                </option>
            @endforeach
        </select>

        <button type="submit">Update</button>
    </form>
@endsection
