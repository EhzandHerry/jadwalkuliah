@extends('layouts.layout')

@section('title', 'Manage Available Time')
@section('header_title', 'Manage Available Time')

@section('content')
<div class="available-container">
    <h1>Manage Available Time for {{ $dosen->name }}</h1>

    {{-- Tombol Kembali dan Tambah --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="{{ route('admin.available.dashboard') }}" class="back-btn" style="margin-bottom: 0;">Kembali</a>
        <a href="{{ route('admin.available.add', $dosen->id) }}" class="add-btn" style="margin-top: 0;">Add Available Time</a>
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
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($availables as $item)
                <tr>
                    <td>{{ $item->hari }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}</td>
                    <td class="action-cell">
                        {{-- Menerapkan class .btn-link dan .edit-btn --}}
                        <a href="{{ route('admin.available.edit', $item->id) }}" class="btn-link edit-btn">Edit</a>

                        <form action="{{ route('admin.available.delete', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus jadwal ini?');">
                            @csrf
                            @method('DELETE')
                            {{-- Menerapkan class .delete-btn --}}
                            <button type="submit" class="delete-btn">Delete</button>
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
