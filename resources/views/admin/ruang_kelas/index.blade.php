@extends('layouts.layout')

@section('title', 'Ruang Kelas')

@section('header_title', 'Manajemen Ruang Kelas')

@section('content')
    <div class="ruang-kelas-container">
        <h1>Daftar Ruang Kelas</h1>

        <a href="{{ route('admin.ruang_kelas.create') }}" class="add-ruang-btn">Tambah Ruang Kelas</a>

        <table class="ruang-kelas-table">
            <thead>
                <tr>
                    <th>Nama Ruangan</th>
                    <th>Gedung</th>
                    <th>Kapasitas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ruangKelas as $ruang)
                    <tr>
                        <td>{{ $ruang->nama_ruangan }}</td>
                        <td>{{ $ruang->nama_gedung }}</td>
                        <td>{{ $ruang->kapasitas }}</td>
                        <td>
                            <a href="{{ route('admin.ruang_kelas.edit', $ruang->id) }}" class="edit-ruang-btn">Edit</a>
                            <form action="{{ route('admin.ruang_kelas.destroy', $ruang->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/index.css') }}">
@endpush
