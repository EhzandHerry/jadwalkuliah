@extends('layouts.layout')

@section('title', 'Matakuliah & Dosen')
@section('header_title', 'Manajemen Matakuliah & Dosen')

@section('content')
<div class="content-container">
  <h1>Daftar Kelas &amp; Dosen</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-striped">
    <thead class="thead-dark">
      <tr>
        <th>Kode</th>
        <th>Matakuliah</th>
        <th>Kelas</th>
        <th>Dosen (NIDN)</th>
        <th>Nama Dosen</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
  @foreach($kelas as $k)
    <tr>
      <td>{{ optional($k->mataKuliah)->kode_matkul  ?? '–' }}</td>
      <td>{{ optional($k->mataKuliah)->nama_matkul  ?? '–' }}</td>
      <td>{{ $k->kelas }}</td>
      <td>{{ optional($k->dosen)->unique_number    ?? '–' }}</td>
      <td>{{ optional($k->dosen)->name             ?? '–' }}</td>
      <td>
      @if($k->dosen)
        <form action="{{ route('admin.matakuliah_dosen.update', $k->id) }}"
              method="POST"
              style="display:inline"
              onsubmit="return confirm('Yakin ingin memperbarui dosen untuk {{ optional($k->mataKuliah)->kode_matkul }} {{ $k->kelas }}?')">
          @csrf
          @method('PUT')
          <select name="unique_number" required>
            @foreach($dosenList as $d)
              <option value="{{ $d->unique_number }}"
                {{ $d->unique_number == $k->unique_number ? 'selected':'' }}>
                {{ $d->name }}
              </option>
            @endforeach
          </select>
          <button class="btn btn-warning btn-sm">Update</button>
        </form>
      @else
        <form action="{{ route('admin.matakuliah_dosen.assign', $k->id) }}"
              method="POST"
              style="display:inline"
              onsubmit="return confirm('Yakin ingin assign dosen untuk {{ optional($k->mataKuliah)->kode_matkul }} {{ $k->kelas }}?')">
          @csrf
          <select name="unique_number" required>
            <option value="">Pilih Dosen…</option>
            @foreach($dosenList as $d)
              <option value="{{ $d->unique_number }}">{{ $d->name }}</option>
            @endforeach
          </select>
          <button class="btn btn-success btn-sm">Assign</button>
        </form>
      @endif
    </td>
    </tr>
  @endforeach
</tbody>
  </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah_dosen/index.css') }}">
@endpush

