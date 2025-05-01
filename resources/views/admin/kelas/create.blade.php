@extends('layouts.layout')

@section('title', 'Dashboard Admin')

@section('header_title', 'Dashboard')

@section('content')
<h1>Tambah Kelas</h1>
    <form action="{{ route('admin.kelas.store') }}" method="POST">
        @csrf
        <label for="kode_matkul">Mata Kuliah</label><br>
        <select name="kode_matkul" required>
            @foreach ($mataKuliahs as $matkul)
                <option value="{{ $matkul->kode_matkul }}">{{ $matkul->kode_matkul }} - {{ $matkul->nama_matkul }}</option>
            @endforeach
        </select><br>
        <label for="kelas">Nama Kelas</label><br>
        <input type="text" name="kelas" placeholder="Contoh: A" required><br>
        <button type="submit">Simpan</button>
    </form>
@endsection

