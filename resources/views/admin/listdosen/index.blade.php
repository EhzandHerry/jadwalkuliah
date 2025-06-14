{{-- resources/views/admin/listdosen/index.blade.php --}}
@extends('layouts.layout')

@section('title', 'Daftar Dosen')
@section('header_title', 'Daftar Dosen')

@section('content')
  <div class="dosen-list-container">
    <h1>Daftar Dosen</h1>

    <a href="{{ route('admin.dosen.create') }}" class="btn btn-add mb-3">Tambah Dosen</a>

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
          <th>Phone</th>
          <th style="width:240px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($dosen as $item)
          <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->unique_number }}</td>
            <td>{{ $item->email }}</td>
            <td>{{ $item->phone }}</td>
            <td>
              <!-- Edit -->
              <a href="{{ route('admin.dosen.edit', $item->id) }}"
   class="btn-update btn-sm mr-1">
    Edit
</a>


              <!-- Delete -->
              <form action="{{ route('admin.dosen.delete', $item->id) }}"
                    method="POST"
                    style="display:inline-block;"
                    onsubmit="return confirm('Yakin ingin menghapus dosen ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm mr-1">
                  Delete
                </button>
              </form>

              <!-- Manage Available -->
              <a href="{{ route('admin.available.manage', $item->id) }}"
   class="btn btn-info btn-sm">
    Manage Available
</a>
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
