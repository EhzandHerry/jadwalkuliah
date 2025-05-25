{{-- resources/views/admin/jadwal/index.blade.php --}}
@extends('layouts.layout')

@section('title', 'Manajemen Jadwal')
@section('header_title', 'Manajemen Jadwal')

@section('content')
<div class="jadwal-index-container">
  <h1>Daftar Jadwal</h1>

  {{-- Notifikasi error/success --}}
  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="jadwal-table">
    <thead>
      <tr>
        <th>Kode Mata Kuliah</th>
        <th>Nama Mata Kuliah</th>
        <th>Kelas</th>
        <th>NIDN</th>
        <th>Nama Dosen</th>
        <th>Ruang Kelas / Aksi</th>
        <th>Hari</th>
        <th>Jam</th>
      </tr>
    </thead>
    <tbody>
      @foreach($kelas as $k)
      <tr>
        <td>{{ $k->mataKuliah->kode_matkul ?? '-' }}</td>
        <td>{{ $k->mataKuliah->nama_matkul ?? '-' }}</td>
        <td>{{ $k->kelas }}</td>
        <td>{{ $k->dosen->unique_number ?? '-' }}</td>
        <td>{{ $k->dosen->name ?? '-' }}</td>
        <td>
          @if(! $k->unique_number)
            {{-- no dosen assigned yet --}}
            <span class="text-danger">
              Silakan pilih dosen terlebih dahulu di halaman Matakuliah & Dosen!
            </span>
          @elseif($k->nama_ruangan)
            {{ $k->nama_ruangan }}
          @else
            <form action="{{ route('admin.jadwal.assignRuang', $k->id) }}" method="POST" class="d-flex flex-wrap align-items-center">
              @csrf
              <select name="nama_ruangan" class="form-control form-control-sm mr-2" required>
                <option value="">Pilih Ruang Kelas</option>
                @foreach($ruangKelasList as $r)
                  <option value="{{ $r->nama_ruangan }}">
                    {{ $r->nama_ruangan }} – {{ $r->nama_gedung }}
                  </option>
                @endforeach
              </select>
              <select name="hari" class="form-control form-control-sm mr-2" required>
                <option value="">Pilih Hari</option>
                <option>Senin</option><option>Selasa</option><option>Rabu</option>
                <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
              </select>
              <select name="jam" class="form-control form-control-sm mr-2" required>
                <option value="">Pilih Jam</option>
                <optgroup label="Sesi 1">
                  <option>07:00 – 07:50</option><option>07:50 – 08:40</option>
                </optgroup>
                <optgroup label="Sesi 2">
                  <option>08:50 – 09:40</option><option>09:40 – 10:30</option>
                </optgroup>
                <!-- etc… -->
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </form>
          @endif
        </td>
        <td>{{ $k->hari ?? '-' }}</td>
        <td>{{ $k->jam ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/jadwal/index.css') }}">
@endpush
