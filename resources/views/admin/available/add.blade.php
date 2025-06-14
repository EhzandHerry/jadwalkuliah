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
        <select name="hari" id="hari" required class="form-control">
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
        <select name="start_time" id="start_time" required class="form-control">
          <option value="">-- Pilih Jam Mulai --</option>
          <optgroup label="Sesi 1">
            <option value="07:00">07:00</option>
            <option value="07:50">07:50</option>
            <option value="08:50">08:50</option>
            <option value="09:40">09:40</option>
            <option value="10:40">10:40</option>
            <option value="12:10">12:10</option>
            <option value="13:20">13:20</option>
          </optgroup>
          
        </select>
      </div>

      <div class="form-group">
        <label for="end_time">End Time</label>
        <select name="end_time" id="end_time" required class="form-control">
          <option value="">-- Pilih Jam Selesai --</option>
          <optgroup label="Sesi 1">
            <option value="07:50">07:50</option>
            <option value="08:40">08:40</option>
          </optgroup>
          <!-- dst... -->
        </select>
      </div>

      <div class="form-group d-flex">
        <button type="submit" class="btn btn-primary mr-2">Save Availability</button>
        <a href="{{ route('admin.available.manage', $dosen->id) }}" class="btn btn-secondary">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/available/add.css') }}">
@endpush
