@extends('layouts.layout')

@section('title', 'Tambah Mata Kuliah')
@section('header_title', 'Tambah Mata Kuliah')

@section('content')
<div class="content-container">
  <h1>Tambah Mata Kuliah</h1>

  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Back link --}}
  <a href="{{ route('admin.mata_kuliah.index') }}" class="back-btn">
    &larr; Kembali ke Daftar
  </a>

  {{-- Use your add-matkul-form class on the form itself --}}
  <form action="{{ route('admin.mata_kuliah.store') }}"
        method="POST"
        class="add-matkul-form">
    @csrf

    <div class="form-group">
      <label for="kode_matkul">Kode Mata Kuliah</label>
      <input type="text"
             id="kode_matkul"
             name="kode_matkul"
             value="{{ old('kode_matkul') }}"
             required>
    </div>

    <div class="form-group">
      <label for="nama_matkul">Nama Mata Kuliah</label>
      <input type="text"
             id="nama_matkul"
             name="nama_matkul"
             value="{{ old('nama_matkul') }}"
             required>
    </div>

    <div class="form-group">
      <label for="sks">SKS</label>
      <input type="number"
             id="sks"
             name="sks"
             value="{{ old('sks') }}"
             min="0"
             required>
    </div>

    <div class="form-group">
      <label for="jumlah_kelas">Jumlah Kelas</label>
      <input type="number"
             id="jumlah_kelas"
             name="jumlah_kelas"
             value="{{ old('jumlah_kelas') }}"
             min="1"
             required>
      <small>
        Masukkan berapa banyak kelas (A, B, C…) yang akan dibuat.
      </small>
    </div>

    <div class="form-group">
  <label for="semester">Semester</label>
  <select id="semester"
          name="semester"
          class="form-control"
          required>
    <option value="" disabled {{ old('semester') ? '' : 'selected' }}>
      Pilih Semester
    </option>
    @for($i = 1; $i <= 8; $i++)
      <option value="{{ $i }}"
        {{ old('semester') == (string)$i ? 'selected' : '' }}>
        Semester {{ $i }}
      </option>
    @endfor
  </select>
</div>


    <button type="submit" class="submit-btn">
      Simpan
    </button>
  </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/create.css') }}">
@endpush
