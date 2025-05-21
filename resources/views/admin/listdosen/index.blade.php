@extends('layouts.layout')

@section('title', 'Daftar Dosen')

@section('header_title', 'Daftar Dosen')

@section('content')
    <div class="dosen-list-container">
        
        <h1>Daftar Dosen</h1>

        <!-- Add Dosen Button -->
        <a href="{{ route('admin.dosen.create') }}" class="btn btn-add">Tambah Dosen</a>
        
        <!-- Dosen Table -->
        <table class="dosen-table">
            <thead>
                <tr>
                    <th>Nama Dosen</th>
                    <th>NIDN</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dosen as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->unique_number }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>
                            <!-- Edit Button -->
                            <a href="{{ route('admin.dosen.edit', $item->id) }}" class="btn btn-update">Update</a>
                            
                            <!-- Delete Button -->
                            <form action="{{ route('admin.dosen.delete', $item->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this dosen?')">Delete</button>
                            </form>
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
