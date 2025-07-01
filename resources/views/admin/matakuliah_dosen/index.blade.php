@extends('layouts.layout')

@section('title', 'Matakuliah & Dosen')
@section('header_title', 'Manajemen Mata Kuliah & Dosen')

@section('content')
<div class="content-container">
    <h1>Daftar Mata Kuliah &amp; Dosen</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('admin.matakuliah_dosen.index') }}" class="form-inline mb-3">
        
        {{-- Filter Semester --}}
        <div class="form-group mr-2">
            <select name="semester_type" class="form-control">
                <option value="">Semua Semester</option>
                <option value="gasal" {{ request('semester_type') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                <option value="genap" {{ request('semester_type') == 'genap' ? 'selected' : '' }}>Genap</option>
            </select>
        </div>

        {{-- Input Pencarian --}}
        <div class="form-group mr-2">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Cari nama mata kuliah..."
                value="{{ request('search') }}"
                style="max-width: 300px;"
            >
        </div>

        <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Kode</th>
                <th>Matakuliah</th>
                <th>Kelas</th>
                <th>NIDN</th>
                <th>Nama Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
    @forelse($kelas as $k)
    <tr>
        <td>{{ optional($k->mataKuliah)->kode_matkul  ?? '–' }}</td>
        <td>{{ optional($k->mataKuliah)->nama_matkul  ?? '–' }}</td>
        <td>{{ $k->kelas }}</td>
        <td>{{ optional($k->dosen)->unique_number    ?? '–' }}</td>
        <td>{{ optional($k->dosen)->name             ?? '–' }}</td>
        <td class="d-flex align-items-center">
            @if($k->dosen)
                {{-- Form untuk Update Dosen --}}
                <form action="{{ route('admin.matakuliah_dosen.update', $k->id) }}"
                        method="POST"
                        class="mr-2"
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
                    <button type="submit" class="btn btn-warning btn-sm">Ubah</button>
                </form>
                {{-- Form untuk Delete Dosen --}}
                <form action="{{ route('admin.matakuliah_dosen.delete', $k->id) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus dosen dari kelas ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>
            @else
                {{-- Form untuk Assign Dosen --}}
                <form action="{{ route('admin.matakuliah_dosen.assign', $k->id) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin assign dosen untuk {{ optional($k->mataKuliah)->kode_matkul }} {{ $k->kelas }}?')">
                    @csrf
                    <select name="unique_number" required>
                        <option value="">Pilih Dosen…</option>
                        @foreach($dosenList as $d)
                            <option value="{{ $d->unique_number }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                </form>
            @endif
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center py-4">
            <strong>Tidak ada data yang cocok dengan filter yang diterapkan.</strong>
        </td>
    </tr>
    @endforelse
</tbody>
    </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah_dosen/index.css') }}">
@endpush
