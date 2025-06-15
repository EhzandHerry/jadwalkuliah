@extends('layouts.layout')

@section('title', 'Edit Available Time')
@section('header_title', 'Edit Available Time for ' . $dosen->name)

@section('content')
    <div class="available-container">
        <h1>Edit Available Time for {{ $dosen->name }}</h1>

        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.dosen.updateAvailable', $dosen->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="hari">Hari</label>
                <select name="hari" id="hari" required class="form-control">
                  <option value="">-- Pilih Hari --</option>
                  @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h)
                    <option value="{{ $h }}"
                      {{ old('hari', $available->hari) == $h ? 'selected':'' }}
                    >{{ $h }}</option>
                  @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="start_time">Start Time</label>
                <select name="start_time" id="start_time" required class="form-control">
                  <option value="">-- Pilih Jam Mulai --</option>
                  <optgroup label="Sesi 1">
                    <option value="07:00" {{ old('start_time', $available->start_time)=='07:00'?'selected':'' }}>07:00</option>
                    <option value="07:50" {{ old('start_time', $available->start_time)=='07:50'?'selected':'' }}>07:50</option>
                  </optgroup>
                  <optgroup label="Sesi 2">
                    <option value="08:50" {{ old('start_time', $available->start_time)=='08:50'?'selected':'' }}>08:50</option>
                    <option value="09:40" {{ old('start_time', $available->start_time)=='09:40'?'selected':'' }}>09:40</option>
                  </optgroup>
                  <optgroup label="Sesi 3">
                    <option value="10:40" {{ old('start_time', $available->start_time)=='10:40'?'selected':'' }}>10:40</option>
                  </optgroup>
                  <optgroup label="Sesi 4">
                    <option value="12:10" {{ old('start_time', $available->start_time)=='12:10'?'selected':'' }}>12:10</option>
                  </optgroup>
                  <optgroup label="Sesi 5">
                    <option value="13:20" {{ old('start_time', $available->start_time)=='13:20'?'selected':'' }}>13:20</option>
                    <option value="14:10" {{ old('start_time', $available->start_time)=='14:10'?'selected':'' }}>14:10</option>
                  </optgroup>
                  <optgroup label="Sesi 6">
                    <option value="15:30" {{ old('start_time', $available->start_time)=='15:30'?'selected':'' }}>15:30</option>
                    <option value="16:20" {{ old('start_time', $available->start_time)=='16:20'?'selected':'' }}>16:20</option>
                    <option value="17:10" {{ old('start_time', $available->start_time)=='17:10'?'selected':'' }}>17:10</option>
                  </optgroup>
                  <optgroup label="Sesi 7">
                    <option value="18:30" {{ old('start_time', $available->start_time)=='18:30'?'selected':'' }}>18:30</option>
                    <option value="19:20" {{ old('start_time', $available->start_time)=='19:20'?'selected':'' }}>19:20</option>
                    <option value="20:10" {{ old('start_time', $available->start_time)=='20:10'?'selected':'' }}>20:10</option>
                  </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label for="end_time">End Time</label>
                <select name="end_time" id="end_time" required class="form-control">
                  <option value="">-- Pilih Jam Selesai --</option>
                  <optgroup label="Sesi 1">
                    <option value="07:50" {{ old('end_time', $available->end_time)=='07:50'?'selected':'' }}>07:50</option>
                    <option value="08:40" {{ old('end_time', $available->end_time)=='08:40'?'selected':'' }}>08:40</option>
                  </optgroup>
                  <optgroup label="Sesi 2">
                    <option value="09:40" {{ old('end_time', $available->end_time)=='09:40'?'selected':'' }}>09:40</option>
                    <option value="10:30" {{ old('end_time', $available->end_time)=='10:30'?'selected':'' }}>10:30</option>
                  </optgroup>
                  <optgroup label="Sesi 3">
                    <option value="11:30" {{ old('end_time', $available->end_time)=='11:30'?'selected':'' }}>11:30</option>
                  </optgroup>
                  <optgroup label="Sesi 4">
                    <option value="13:10" {{ old('end_time', $available->end_time)=='13:10'?'selected':'' }}>13:10</option>
                  </optgroup>
                  <optgroup label="Sesi 5">
                    <option value="14:10" {{ old('end_time', $available->end_time)=='14:10'?'selected':'' }}>14:10</option>
                    <option value="15:00" {{ old('end_time', $available->end_time)=='15:00'?'selected':'' }}>15:00</option>
                  </optgroup>
                  <optgroup label="Sesi 6">
                    <option value="16:20" {{ old('end_time', $available->end_time)=='16:20'?'selected':'' }}>16:20</option>
                    <option value="17:10" {{ old('end_time', $available->end_time)=='17:10'?'selected':'' }}>17:10</option>
                    <option value="18:00" {{ old('end_time', $available->end_time)=='18:00'?'selected':'' }}>18:00</option>
                  </optgroup>
                  <optgroup label="Sesi 7">
                    <option value="19:20" {{ old('end_time', $available->end_time)=='19:20'?'selected':'' }}>19:20</option>
                    <option value="20:10" {{ old('end_time', $available->end_time)=='20:10'?'selected':'' }}>20:10</option>
                    <option value="21:00" {{ old('end_time', $available->end_time)=='21:00'?'selected':'' }}>21:00</option>
                  </optgroup>
                </select>
            </div>

            <div class="form-group d-flex">
                <button type="submit" class="btn btn-primary mr-2">Update Availability</button>
                <a href="{{ route('admin.available.manage', $dosen->id) }}" class="btn btn-secondary">
                  Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/available/dashboard.css') }}">
@endpush
