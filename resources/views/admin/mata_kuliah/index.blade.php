{{-- resources/views/admin/mata_kuliah/index.blade.php --}}
@extends('layouts.layout')

@section('title', 'Matakuliah')
@section('header_title', 'Manajemen Matakuliah')

@section('content')
<div class="content-container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Mata Kuliah</h1>
    <a href="{{ route('admin.mata_kuliah.create') }}" class="add-matkul-btn">
      Tambah Matakuliah
    </a>
  </div>

  <!-- Form Search + Filter -->
<form method="GET" action="{{ route('admin.mata_kuliah.index') }}" class="form-inline mb-3">
  <input
    type="text"
    name="search"
    class="form-control mr-2"
    placeholder="Cari nama mata kuliah..."
    value="{{ request('search') }}"
    style="max-width: 300px;"
  >

  <select name="semester_filter" class="form-control mr-2" style="max-width: 150px;">
    <option value="">Semua</option>
    <option value="Genap" {{ request('semester_filter') == 'Genap' ? 'selected' : '' }}>Genap</option>
    <option value="Gasal" {{ request('semester_filter') == 'Gasal' ? 'selected' : '' }}>Gasal</option>
  </select>

  <button type="submit" class="btn btn-secondary">Cari</button>
</form>


  <table class="table table-striped matkul-table">
    <thead class="thead-dark">
      <tr>
        <th>Kode</th>
        <th>Nama</th>
        <th>SKS</th>
        <th>Semester</th>
        <th>Jumlah Kelas</th>
        <th style="width:160px">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($mataKuliahs as $matkul)
        <tr>
          <td>{{ $matkul->kode_matkul }}</td>
          <td>{{ $matkul->nama_matkul }}</td>
          <td>{{ $matkul->sks }}</td>
          <td>{{ $matkul->semester }}</td>
          <td>{{ $matkul->jumlah_kelas }}</td>

          <td>
            <a href="{{ route('admin.mata_kuliah.edit', $matkul->id) }}" class="btn btn-warning btn-sm mr-2">
              Edit
            </a>

            <form action="{{ route('admin.mata_kuliah.destroy', $matkul->id) }}"
                  method="POST"
                  style="display:inline-block;"
                  onsubmit="return confirm('Yakin ingin menghapus {{ $matkul->nama_matkul }}?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">
                Delete
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">Data tidak ditemukan.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/index.css') }}">
@endpush
