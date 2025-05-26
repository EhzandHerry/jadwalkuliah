@extends('layouts.layout')

@section('title', 'Add Available Time')
@section('header_title', 'Add Available Time for ' . $dosen->name)

@section('content')
<div class="available-container">
  <div class="available-form">
    <h1>Add Available Time for {{ $dosen->name }}</h1>

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.dosen.storeAvailable', $dosen->id) }}" method="POST">
      @csrf

      <div class="form-group">
        <label for="hari">Hari</label>
        <select name="hari" id="hari" required>
          <option value="">-- Pilih Hari --</option>
          @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari)
            <option value="{{ $hari }}"
              {{ old('hari') == $hari ? 'selected':'' }}
              @if(in_array($hari, $existingDays)) disabled @endif
            >
              {{ $hari }}
              @if(in_array($hari, $existingDays))
                (sudah diinput)
              @endif
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="start_time">Start Time</label>
        <select name="start_time" id="start_time" required>
          <option value="">-- Pilih Jam Mulai --</option>
          <optgroup label="Sesi 1">
            <option value="07:00">07:00</option>
            <option value="07:50">07:50</option>
          </optgroup>
          <optgroup label="Sesi 2">
            <option value="08:50">08:50</option>
            <option value="09:40">09:40</option>
          </optgroup>
          <optgroup label="Sesi 3">
            <option value="10:40">10:40</option>
          </optgroup>
          <optgroup label="Sesi 4">
            <option value="12:10">12:10</option>
          </optgroup>
          <optgroup label="Sesi 5">
            <option value="13:20">13:20</option>
            <option value="14:10">14:10</option>
          </optgroup>
          <optgroup label="Sesi 6">
            <option value="15:30">15:30</option>
            <option value="16:20">16:20</option>
            <option value="17:10">17:10</option>
          </optgroup>
          <optgroup label="Sesi 7">
            <option value="18:30">18:30</option>
            <option value="19:20">19:20</option>
            <option value="20:10">20:10</option>
          </optgroup>
        </select>
      </div>

      <div class="form-group">
        <label for="end_time">End Time</label>
        <select name="end_time" id="end_time" required>
          <option value="">-- Pilih Jam Selesai --</option>
          <optgroup label="Sesi 1">
            <option value="07:50">07:50</option>
            <option value="08:40">08:40</option>
          </optgroup>
          <optgroup label="Sesi 2">
            <option value="09:40">09:40</option>
            <option value="10:30">10:30</option>
          </optgroup>
          <optgroup label="Sesi 3">
            <option value="11:30">11:30</option>
          </optgroup>
          <optgroup label="Sesi 4">
            <option value="13:10">13:10</option>
          </optgroup>
          <optgroup label="Sesi 5">
            <option value="14:10">14:10</option>
            <option value="15:00">15:00</option>
          </optgroup>
          <optgroup label="Sesi 6">
            <option value="16:20">16:20</option>
            <option value="17:10">17:10</option>
            <option value="18:00">18:00</option>
          </optgroup>
          <optgroup label="Sesi 7">
            <option value="19:20">19:20</option>
            <option value="20:10">20:10</option>
            <option value="21:00">21:00</option>
          </optgroup>
        </select>
      </div>

      <button type="submit">Save Availability</button>
    </form>
  </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/available/add.css') }}">
@endpush
