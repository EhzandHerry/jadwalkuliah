@extends('layouts.layout')

@section('title', 'Available Time Dashboard')

@section('header_title', 'Available Time Dashboard')

@section('content')
    <div class="available-container">
        <h1>Available Time Dashboard</h1>

        <!-- Tombol Kembali -->
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary mb-3">Kembali</a>

        <!-- Table displaying dosen and their NIDN -->
        <table class="available-table">
            <thead>
                <tr>
                    <th>Nama Dosen</th>
                    <th>NIDN</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dosen as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->unique_number }}</td>
                        <td>
                            <!-- Button to manage availability for this dosen -->
                            <a href="{{ route('admin.available.manage', $item->id) }}" class="manage-btn">Manage Availability</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/available/dashboard.css') }}">
@endpush
