@extends('layouts.layout')

@section('title', 'Daftar Dosen')

@section('header_title', 'Daftar Dosen')

@section('content')
    <div class="dosen-list-container">
        <h1>Daftar Dosen</h1>
        
        <!-- Dosen Table -->
        <table class="dosen-table">
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
                            <a href="{{ route('admin.dosen.show', $item->id) }}" class="detail-btn">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/listdosen/dashboard.css') }}">
@endpush
