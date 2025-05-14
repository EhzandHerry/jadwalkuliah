@extends('layouts.layout')

@section('title', 'Add Available Time')

@section('header_title', 'Add Available Time for ' . $dosen->name)

@section('content')
    <div class="available-container">
        <div class="available-form">
            <h1>Add Available Time for {{ $dosen->name }}</h1>

            <!-- Form to add new available time -->
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
                    <input type="time" name="start_time" id="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" name="end_time" id="end_time" required>
                </div>

                <button type="submit">Save Availability</button>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/available/add.css') }}">
@endpush
