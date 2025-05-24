@extends('layouts.layout')

@section('title', 'Edit Matakuliah')
@section('header_title', 'Edit Matakuliah')

@section('content')
<div class="content-container">
  <h1>Edit Matakuliah</h1>

  <form action="{{ route('admin.mata_kuliah.update', $matkul->id) }}"
        method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
      <label for="kode_matkul">Kode Mata Kuliah</label>
      <input type="text"
             id="kode_matkul"
             name="kode_matkul"
             class="form-control"
             value="{{ old('kode_matkul', $matkul->kode_matkul) }}"
             required>
    </div>

    <div class="form-group">
      <label for="nama_matkul">Nama Mata Kuliah</label>
      <input type="text"
             id="nama_matkul"
             name="nama_matkul"
             class="form-control"
             value="{{ old('nama_matkul', $matkul->nama_matkul) }}"
             required>
    </div>

    <div class="form-group">
      <label for="sks">SKS</label>
      <input type="number"
             id="sks"
             name="sks"
             class="form-control"
             value="{{ old('sks', $matkul->sks) }}"
             required>
    </div>

    <div class="form-group">
      <label for="semester">Semester</label>
      <select name="semester" id="semester" class="form-control" required>
        <option value="Genap" {{ $matkul->semester=='Genap' ? 'selected':'' }}>Genap</option>
        <option value="Gasal" {{ $matkul->semester=='Gasal' ? 'selected':'' }}>Gasal</option>
      </select>
    </div>

    <div class="form-group">
      <label for="jumlah_kelas">Jumlah Kelas</label>
      <input type="number"
             id="jumlah_kelas"
             name="jumlah_kelas"
             class="form-control"
             value="{{ old('jumlah_kelas', $matkul->kelas->count()) }}"
             min="1"
             required>
      <small class="form-text text-muted">
        Ubah jumlah kelas (A, B, C…) sesuai kebutuhan.
      </small>
    </div>

    <button type="submit" class="btn btn-primary">
      Update
    </button>
    <a href="{{ route('admin.mata_kuliah.index') }}"
       class="btn btn-secondary">Batal</a>
  </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/edit.css') }}">
@endpush


@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/create.css') }}">
@endpush
