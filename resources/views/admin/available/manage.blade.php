@extends('layouts.layout')

@section('title', 'Manage Available Time')

@section('header_title', 'Manage Available Time for ' . $dosen->name)

@section('content')
    <div class="available-container">
        <h1>Manage Available Time for {{ $dosen->name }}</h1>

        <!-- Tombol Kembali -->
        <a href="{{ route('admin.dosen.index') }}" class="back-btn">Kembali</a>

        <!-- Table displaying the available time for the dosen -->
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
                @foreach ($availables as $available)
                    <tr>
                        <td>{{ $available->hari }}</td>
                        <td>{{ $available->start_time }}</td>
                        <td>{{ $available->end_time }}</td>
                        <td>
                            <!-- Button to delete the available time -->
                            <form action="{{ route('admin.available.delete', $available->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this available time?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Button to add new available time for the dosen -->
        <a href="{{ route('admin.available.add', $dosen->id) }}" class="add-btn">Add Available Time</a>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/available/manage.css') }}">
@endpush
