@extends('layouts.layout')

@section('title', 'Manajemen Dosen')
@section('header_title', 'Manajemen Dosen')

@section('content')
  <div class="dosen-list-container">
    <h1>Daftar Dosen</h1>
    <a href="{{ route('admin.dosen.create') }}" class="btn btn-add mb-3">Tambah Dosen</a>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Notifikasi Error --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Form Search -->
    <form method="GET" action="{{ route('admin.dosen.index') }}" class="mb-3">
      <div class="input-group" style="max-width: 400px;">
        <input
          type="text"
          name="search"
          class="form-control"
          placeholder="Cari nama dosen…"
          value="{{ $search ?? '' }}"
          style="border-radius: 4px 0 0 4px;"
        >
        <button type="submit" class="btn btn-secondary" style="border-radius: 0 4px 4px 0;">
          Cari
        </button>
      </div>
    </form>

    <table class="dosen-table table table-striped">
      <thead class="thead-dark">
        <tr>
          <th>Nama Dosen</th>
          <th>NIDN</th>
          <th>Email</th>
          <th>Ketersediaan</th> 
          <th style="width:240px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($dosen as $item)
          <tr>
            <td>{{ $item->nama }}</td>
            <td>{{ $item->nidn }}</td>
            <td>{{ $item->email }}</td>
            <td>
              {{ $availabilitySummaries[$item->id] ?? '-' }}
            </td>
            <td>
              <a href="{{ route('admin.dosen.edit', $item->id) }}"
                 class="btn-update btn-sm mr-1">Edit</a>

              <form action="{{ route('admin.dosen.delete', $item->id) }}"
                    method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Yakin ingin menghapus dosen ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm mr-1">Hapus</button>
              </form>

              <a href="{{ route('admin.available.manage', $item->id) }}"
                 class="btn btn-info btn-sm">Ketersediaan</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection

@push('css')
  <link rel="stylesheet" href="{{ asset('css/admin/listdosen/index.css') }}">
@endpush