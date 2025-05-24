{{-- resources/views/admin/mata_kuliah/index.blade.php --}}
@extends('layouts.layout')

@section('title', 'Matakuliah')
@section('header_title', 'Manajemen Matakuliah')

@section('content')
<div class="content-container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Mata Kuliah</h1>
    <a href="{{ route('admin.mata_kuliah.create') }}" class="add-matkul-btn">
      Tambah Mata Kuliah
    </a>
  </div>

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
      @foreach($mataKuliahs as $matkul)
        <tr>
          <td>{{ $matkul->kode_matkul }}</td>
          <td>{{ $matkul->nama_matkul }}</td>
          <td>{{ $matkul->sks }}</td>
          <td>{{ $matkul->semester }}</td>
          <td>{{ $matkul->kelas->count() }}</td>
          <td>
            {{-- Update --}}
            <a href="{{ route('admin.mata_kuliah.edit', $matkul->id) }}"
               class="btn btn-warning btn-sm mr-2">
              Update
            </a>

            {{-- Delete --}}
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
      @endforeach
    </tbody>
  </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/index.css') }}">
@endpush
