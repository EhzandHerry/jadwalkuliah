@extends('layouts.layout')

@section('title', 'Ruang Kelas')

@section('header_title', 'Manajemen Ruang Kelas')

@section('content')
<h1>Daftar Ruang Kelas</h1>

<a href="{{ route('admin.ruang_kelas.create') }}" style="margin-top: 20px; display: inline-block; padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">Tambah Ruang Kelas</a>

<table border="1" style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Gedung</th>
            <th>Lantai</th>
            <th>Nama Ruangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ruangKelas as $ruang)
        <tr>
            <td>{{ $ruang->kode_ruangan }}</td>
            <td>{{ $ruang->nama_gedung }}</td>
            <td>{{ $ruang->lantai }}</td>
            <td>{{ $ruang->nama_ruangan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection


