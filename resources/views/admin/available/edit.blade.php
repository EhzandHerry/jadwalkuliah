@extends('layouts.layout')

@section('title', 'Edit Available Time')
@section('header_title', 'Edit Available Time for ' . $available->user->name)

@section('content')
<div class="available-container">
    <div class="available-form">
        <h1>Edit Available Time for {{ $available->user->name }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.available.update', $available->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="hari">Hari</label>
                <input type="text" id="hari" class="form-control" value="{{ $available->hari }}" disabled>
            </div>

            @php
                // Format waktu dari database untuk perbandingan di dropdown
                $startTime = \Carbon\Carbon::parse($available->start_time)->format('H:i');
                $endTime = \Carbon\Carbon::parse($available->end_time)->format('H:i');
            @endphp

            <div class="form-group">
                <label for="start_time">Start Time</label>
                <select name="start_time" id="start_time" required class="form-control">
                    <option value="">-- Pilih Jam Mulai --</option>
                    {{-- Opsi jam di-generate dengan rapi --}}
                    <optgroup label="Sesi 1">
                        <option value="07:00" {{ old('start_time', $startTime) == '07:00' ? 'selected' : '' }}>07:00</option>
                        <option value="07:50" {{ old('start_time', $startTime) == '07:50' ? 'selected' : '' }}>07:50</option>
                    </optgroup>
                    <optgroup label="Sesi 2">
                        <option value="08:50" {{ old('start_time', $startTime) == '08:50' ? 'selected' : '' }}>08:50</option>
                        <option value="09:40" {{ old('start_time', $startTime) == '09:40' ? 'selected' : '' }}>09:40</option>
                    </optgroup>
                    <optgroup label="Sesi 3">
                        <option value="10:40" {{ old('start_time', $startTime) == '10:40' ? 'selected' : '' }}>10:40</option>
                    </optgroup>
                    <optgroup label="Sesi 4">
                        <option value="12:10" {{ old('start_time', $startTime) == '12:10' ? 'selected' : '' }}>12:10</option>
                    </optgroup>
                    <optgroup label="Sesi 5">
                        <option value="13:20" {{ old('start_time', $startTime) == '13:20' ? 'selected' : '' }}>13:20</option>
                        <option value="14:10" {{ old('start_time', $startTime) == '14:10' ? 'selected' : '' }}>14:10</option>
                    </optgroup>
                    <optgroup label="Sesi 6">
                        <option value="15:30" {{ old('start_time', $startTime) == '15:30' ? 'selected' : '' }}>15:30</option>
                        <option value="16:20" {{ old('start_time', $startTime) == '16:20' ? 'selected' : '' }}>16:20</option>
                        <option value="17:10" {{ old('start_time', $startTime) == '17:10' ? 'selected' : '' }}>17:10</option>
                    </optgroup>
                    <optgroup label="Sesi 7">
                        <option value="18:30" {{ old('start_time', $startTime) == '18:30' ? 'selected' : '' }}>18:30</option>
                        <option value="19:20" {{ old('start_time', $startTime) == '19:20' ? 'selected' : '' }}>19:20</option>
                        <option value="20:10" {{ old('start_time', $startTime) == '20:10' ? 'selected' : '' }}>20:10</option>
                    </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label for="end_time">End Time</label>
                <select name="end_time" id="end_time" required class="form-control">
                    <option value="">-- Pilih Jam Selesai --</option>
                    <optgroup label="Sesi 1">
                        <option value="07:50" {{ old('end_time', $endTime) == '07:50' ? 'selected' : '' }}>07:50</option>
                        <option value="08:40" {{ old('end_time', $endTime) == '08:40' ? 'selected' : '' }}>08:40</option>
                    </optgroup>
                    <optgroup label="Sesi 2">
                        <option value="09:40" {{ old('end_time', $endTime) == '09:40' ? 'selected' : '' }}>09:40</option>
                        <option value="10:30" {{ old('end_time', $endTime) == '10:30' ? 'selected' : '' }}>10:30</option>
                    </optgroup>
                    <optgroup label="Sesi 3">
                        <option value="11:30" {{ old('end_time', $endTime) == '11:30' ? 'selected' : '' }}>11:30</option>
                    </optgroup>
                    <optgroup label="Sesi 4">
                        <option value="13:10" {{ old('end_time', $endTime) == '13:10' ? 'selected' : '' }}>13:10</option>
                    </optgroup>
                    <optgroup label="Sesi 5">
                        <option value="14:10" {{ old('end_time', $endTime) == '14:10' ? 'selected' : '' }}>14:10</option>
                        <option value="15:00" {{ old('end_time', $endTime) == '15:00' ? 'selected' : '' }}>15:00</option>
                    </optgroup>
                    <optgroup label="Sesi 6">
                        <option value="16:20" {{ old('end_time', $endTime) == '16:20' ? 'selected' : '' }}>16:20</option>
                        <option value="17:10" {{ old('end_time', $endTime) == '17:10' ? 'selected' : '' }}>17:10</option>
                        <option value="18:00" {{ old('end_time', $endTime) == '18:00' ? 'selected' : '' }}>18:00</option>
                    </optgroup>
                    <optgroup label="Sesi 7">
                        <option value="19:20" {{ old('end_time', $endTime) == '19:20' ? 'selected' : '' }}>19:20</option>
                        <option value="20:10" {{ old('end_time', $endTime) == '20:10' ? 'selected' : '' }}>20:10</option>
                        <option value="21:00" {{ old('end_time', $endTime) == '21:00' ? 'selected' : '' }}>21:00</option>
                    </optgroup>
                </select>
            </div>

            {{-- ====================================================== --}}
            {{-- PERBAIKAN STRUKTUR TOMBOL AGAR SAMA DENGAN FORM ADD --}}
            {{-- ====================================================== --}}
            <div class="form-group">
                <button type="submit" class="btn-action btn-update">Update Availability</button>
            </div>
             <a href="{{ route('admin.available.manage', $available->user->id) }}" class="btn-secondary">
                Batal
            </a>
            {{-- ====================================================== --}}
            
        </form>
    </div>
</div>
@endsection

@push('css')
    {{-- Pastikan ini mengarah ke file CSS yang sama dengan form Add --}}
    <link rel="stylesheet" href="{{ asset('css/admin/available/edit.css') }}">
@endpush
