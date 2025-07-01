@extends('layouts.layout')

@section('title', 'Tambah Ruang Kelas')
@section('header_title', 'Tambah Ruang Kelas')

@section('content')
  <div class="ruang-kelas-create-container">
    
    <form action="{{ route('admin.ruang_kelas.store') }}" method="POST" class="ruang-kelas-form">
      @csrf
      <h1>Tambah Ruang Kelas</h1>

      <div class="form-group">
        <label for="nama_ruangan">Nama Ruangan</label>
        <input
          type="text"
          id="nama_ruangan"
          name="nama_ruangan"
          value="{{ old('nama_ruangan') }}"
          required
          class="input-field"
        >
      </div>

      <div class="form-group">
        <label for="nama_gedung">Nama Gedung</label>
        <input
          type="text"
          id="nama_gedung"
          name="nama_gedung"
          value="{{ old('nama_gedung') }}"
          required
          class="input-field"
        >
      </div>

      <div class="form-group">
        <label for="kapasitas">Kapasitas (jumlah duduk)</label>
        <input
          type="number"
          id="kapasitas"
          name="kapasitas"
          value="{{ old('kapasitas') }}"
          min="1"
          required
          class="input-field"
        >
      </div>

      <div class="form-group">
        <label for="kapasitas_kelas">Kapasitas Kelas (jumlah kelas simultan)</label>
        <input
          type="number"
          id="kapasitas_kelas"
          name="kapasitas_kelas"
          value="{{ old('kapasitas_kelas', 1) }}"
          min="1"
          required
          class="input-field"
        >
      </div>
      
      {{-- Mengganti tombol Simpan dan menambahkan tombol Batal di sini --}}
      <div class="form-actions">
        <button type="submit" class="btn submit-btn">Simpan</button>
        <a href="{{ route('admin.ruang_kelas.index') }}" class="btn cancel-btn">Batal</a>
      </div>
    </form>
    {{-- Menghapus link "Kembali" yang lama dari sini --}}
  </div>
@endsection

@push('css')
  <link rel="stylesheet" href="{{ asset('css/admin/ruang_kelas/create.css') }}">
@endpush
