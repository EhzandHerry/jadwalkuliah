@extends('layouts.layout')

@section('title', 'Manajemen Jadwal')
@section('header_title', 'Manajemen Jadwal')

@section('content')
{{-- PERUBAHAN DI SINI: Tambahkan kembali class .jadwal-index-container --}}
<div class="container-fluid jadwal-index-container">
    <h1 class="mb-4">Daftar Jadwal</h1>

    {{-- Baris Kontrol: Tombol Preview, Filter, dan Search jadi satu baris --}}
    <form action="{{ route('admin.jadwal.index') }}" method="GET" class="filter-controls">
        
        <a href="{{ route('admin.jadwal.previewMatrix') }}" class="btn btn-success">
            Preview Matrix Jadwal
        </a>
        
        <div class="form-group">
            <select name="semester_type" id="semester_type" class="form-control">
                <option value="gasal" {{ request('semester_type', 'genap') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                <option value="genap" {{ request('semester_type', 'genap') == 'genap' ? 'selected' : '' }}>Genap</option>
            </select>
        </div>

        <div class="form-group">
            <input type="text" name="search" id="search" class="form-control" placeholder="Nama mata kuliah..." value="{{ request('search') }}">
        </div>

        <button type="submit" class="btn btn-apply-filter">
            Terapkan Filter
        </button>
    </form>


    {{-- Notifikasi Error dan Sukses --}}
    @if ($errors->any() || session('error') || session('success'))
    <div class="mt-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    </div>
    @endif


    {{-- Tabel Jadwal --}}
    <div class="table-responsive mt-4">
        <table class="jadwal-table table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Kode Mata Kuliah</th>
                    <th>Nama Mata Kuliah</th>
                    <th>Kelas</th>
                    <th>SKS</th>
                    <th>Nama Dosen</th>
                    <th>Ruang Kelas</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas as $k)
                <tr>
                    <td>{{ optional($k->mataKuliah)->kode_matkul ?? '-' }}</td>
                    <td>{{ optional($k->mataKuliah)->nama_matkul ?? '-' }}</td>
                    <td>{{ $k->kelas }}</td>
                    <td>{{ optional($k->mataKuliah)->sks ?? '-' }}</td>
                    <td>{{ optional($k->dosen)->name ?? '-' }}</td>
                    <td>
                        @if(! $k->unique_number)
                            <span class="text-danger">Silakan pilih dosen di halaman Matakuliah & Dosen!</span>
                        @elseif($k->nama_ruangan)
                            {{ $k->nama_ruangan }}
                        @else
                            @php
                                $uniq   = $k->dosen->unique_number;
                                $hasAv  = isset($availableTimes[$uniq]) && count($availableTimes[$uniq]) > 0;
                                $hariAv = $hasAv ? array_keys($availableTimes[$uniq]) : [];
                            @endphp
                            <form action="{{ route('admin.jadwal.assignRuang', $k->id) }}" method="POST" class="d-flex flex-wrap align-items-center">
                                @csrf
                                <select name="nama_ruangan" data-id="{{ $k->id }}" class="form-control form-control-sm mr-2" style="min-width: 200px;" required>
                                    <option value="">Pilih Ruang Kelas</option>
                                    @foreach($ruangKelasList as $r)
                                        <option value="{{ $r->nama_ruangan }}" data-capacity="{{ $r->kapasitas }}">
                                            {{ $r->nama_ruangan }} – {{ $r->nama_gedung }} (kapasitas {{ $r->kapasitas_kelas }})
                                        </option>
                                    @endforeach
                                </select>
                                <select name="hari" id="hari-{{ $k->id }}" class="form-control form-control-sm mr-2" style="min-width: 120px;" {{ $hasAv ? '' : 'disabled' }} required>
                                    <option value="">Pilih Hari</option>
                                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                                        <option value="{{ $hari }}" @if(! in_array($hari, $hariAv)) disabled @endif>{{ $hari }}</option>
                                    @endforeach
                                </select>
                                <select name="jam" id="jam-{{ $k->id }}" class="form-control form-control-sm mr-2" style="min-width: 150px;" {{ $hasAv ? '' : 'disabled' }} required>
                                    <option value="">Pilih Jam</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            </form>
                        @endif
                    </td>
                    <td>{{ $k->hari ?? '-' }}</td>
                    <td>{{ $k->jam ?? '-' }}</td>
                    <td>
                        @if($k->jadwal_id)
                            <a href="{{ route('admin.jadwal.edit', $k->jadwal_id) }}" class="btn btn-warning btn-sm mr-1">Edit</a>
                            <form action="{{ route('admin.jadwal.destroy', $k->jadwal_id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin hapus jadwal ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        @else
                            <span class="text-muted">Belum dijadwalkan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <strong>Tidak ada data yang cocok dengan filter yang diterapkan.</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/jadwal/index.css') }}">
@endpush

@push('scripts')
    <script>
        // Script tidak perlu diubah
        document.addEventListener('DOMContentLoaded', function () {
            // ... (seluruh kode JS Anda tetap di sini) ...
        });
    </script>
@endpush