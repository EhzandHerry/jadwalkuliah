@extends('layouts.layout')

@section('title', 'Manajemen Jadwal')

@section('header_title', 'Manajemen Jadwal')

@section('content')
<div class="jadwal-index-container">
    <h1>Daftar Jadwal</h1>

    {{-- Notifikasi error/success --}}
    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <table class="jadwal-table">
    <thead>
        <tr>
            <th>Kode Mata Kuliah</th>
            <th>Nama Mata Kuliah</th>
            <th>Kelas</th>
            <th>NIDN</th>
            <th>Nama Dosen</th>
            <th>Ruang Kelas</th>
            <th>Hari</th>
            <th>Jam</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($kelas as $k)
        <tr>
            <td>{{ $k->mataKuliah->kode_matkul ?? '-' }}</td>
            <td>{{ $k->mataKuliah->nama_matkul ?? '-' }}</td>
            <td>{{ $k->kelas }}</td>
            <td>{{ $k->dosen->unique_number ?? '-' }}</td>
            <td>{{ $k->dosen->name ?? '-' }}</td>
            <td>
    @if($k->nama_ruangan)
        {{ $k->nama_ruangan }}
    @else
        <form action="{{ route('admin.jadwal.assignRuang', $k->id) }}" method="POST">
    @csrf
    <select name="nama_ruangan" required>
        <option value="">Pilih Ruang Kelas</option>
        @foreach ($ruangKelasList as $ruang)
            <option value="{{ $ruang->nama_ruangan }}">{{ $ruang->nama_ruangan }} - {{ $ruang->nama_gedung }}</option>
        @endforeach
    </select>

    <select name="hari" required>
        <option value="">Pilih Hari</option>
        <option value="Senin">Senin</option>
        <option value="Selasa">Selasa</option>
        <option value="Rabu">Rabu</option>
        <option value="Kamis">Kamis</option>
        <option value="Jumat">Jumat</option>
        <option value="Sabtu">Sabtu</option>
    </select>

    <select name="jam" required>
    <option value="">Pilih Jam</option>

    <optgroup label="Sesi 1">
        <option value="07:00 - 07:50">07:00 - 07:50</option>
        <option value="07:50 - 08:40">07:50 - 08:40</option>
    </optgroup>

    <optgroup label="Sesi 2">
        <option value="08:50 - 09:40">08:50 - 09:40</option>
        <option value="09:40 - 10:30">09:40 - 10:30</option>
    </optgroup>

    <optgroup label="Sesi 3">
        <option value="10:40 - 11:30">10:40 - 11:30</option>
    </optgroup>

    <optgroup label="Sesi 4">
        <option value="12:10 - 13:10">12:10 - 13:10</option>
    </optgroup>

    <optgroup label="Sesi 5">
        <option value="13:20 - 14:10">13:20 - 14:10</option>
        <option value="14:10 - 15:00">14:10 - 15:00</option>
    </optgroup>

    <optgroup label="Sesi 6">
        <option value="15:30 - 16:20">15:30 - 16:20</option>
        <option value="16:20 - 17:10">16:20 - 17:10</option>
        <option value="17:10 - 18:00">17:10 - 18:00</option>
    </optgroup>

    <optgroup label="Sesi 7">
        <option value="18:30 - 19:20">18:30 - 19:20</option>
        <option value="19:20 - 20:10">19:20 - 20:10</option>
        <option value="20:10 - 21:00">20:10 - 21:00</option>
    </optgroup>
</select>


    <button type="submit" class="btn btn-primary">Simpan</button>
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

