@extends('layouts.layout')

@section('title', 'Edit Available Time')

@section('header_title', 'Edit Available Time for Dosen')

@section('content')
    <div class="available-container">
        <h1>Edit Available Time for {{ $dosen->name }}</h1>

        <!-- Form to fill available time -->
        <form action="{{ route('admin.dosen.storeAvailable', $dosen->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="hari">Hari</label>
                <select name="hari" id="hari" required>
                    <option value="Senin" {{ old('hari') == 'Senin' ? 'selected' : '' }}>Senin</option>
                    <option value="Selasa" {{ old('hari') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                    <option value="Rabu" {{ old('hari') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                    <option value="Kamis" {{ old('hari') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                    <option value="Jumat" {{ old('hari') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                    <option value="Sabtu" {{ old('hari') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                    <option value="Minggu" {{ old('hari') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                </select>
            </div>

            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required>
            </div>

            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Save Availability</button>
        </form>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/available/dashboard.css') }}">
@endpush
