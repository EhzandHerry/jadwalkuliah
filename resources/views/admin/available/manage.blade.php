@extends('layouts.layout')

@section('title', 'Manage Available Time')
@section('header_title', 'Manajemen Waktu Ketersediaan')

@section('content')
<div class="available-container">
    <h1>Manajemen Waktu Ketersediaan untuk {{ $dosen->nama }}</h1>

    {{-- Tombol Kembali dan Tambah --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        {{-- PERUBAHAN DI SINI: Mengubah route ke halaman list dosen --}}
        <a href="{{ route('admin.dosen.index') }}" class="back-btn" style="margin-bottom: 0;">Kembali</a>
        <a href="{{ route('admin.available.add', $dosen->id) }}" class="add-btn" style="margin-top: 0;">Tambah Waktu Ketersediaan</a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    {{-- Tabel --}}
    <table class="available-table">
        <thead>
            <tr>
                <th>Hari</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($availables as $item)
                <tr>
                    <td>{{ $item->hari }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->waktu_mulai)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->waktu_selesai)->format('H:i') }}</td>
                    <td class="action-cell">
                        <a href="{{ route('admin.available.edit', $item->id) }}" class="btn-link edit-btn">Edit</a>

                        <form action="{{ route('admin.available.delete', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus jadwal ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">Belum ada available time yang ditambahkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('css')
    {{-- Pastikan ini me-link ke file CSS yang benar --}}
    <link rel="stylesheet" href="{{ asset('css/admin/available/manage.css') }}">
@endpush
