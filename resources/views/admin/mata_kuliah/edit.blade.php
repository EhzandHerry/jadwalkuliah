@extends('layouts.layout')

@section('title', 'Edit Mata Kuliah')
@section('header_title', 'Edit Mata Kuliah')

@section('content')
<div class="edit-matkul-container">
    
    <form action="{{ route('admin.mata_kuliah.update', $matkul->id) }}" method="POST" class="edit-matkul-form">
        @csrf
        @method('PUT')
        
        <h1>Edit Mata Kuliah</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group">
            <label for="kode_matkul">Kode Mata Kuliah</label>
            <input type="text" id="kode_matkul" name="kode_matkul" value="{{ old('kode_matkul', $matkul->kode_matkul) }}" required class="form-control">
        </div>

        <div class="form-group">
            <label for="nama_matkul">Nama Mata Kuliah</label>
            <input type="text" id="nama_matkul" name="nama_matkul" value="{{ old('nama_matkul', $matkul->nama_matkul) }}" required class="form-control">
        </div>

        <div class="form-group">
            <label for="sks">SKS</label>
            <input type="number" id="sks" name="sks" value="{{ old('sks', $matkul->sks) }}" min="0" required class="form-control">
        </div>

        <div class="form-group">
            <label for="semester">Semester</label>
            <select id="semester" name="semester" class="form-control" required>
                <option value="" disabled>Pilih Semester</option>
                @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ old('semester', $matkul->semester) == $i ? 'selected' : '' }}>
                        Semester {{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        {{-- PENAMBAHAN FORM PEMINATAN --}}
        <div class="form-group">
            <label for="peminatan">Peminatan (Opsional)</label>
            <select id="peminatan" name="peminatan" class="form-control">
                {{-- Opsi ini akan menyimpan NULL jika dipilih --}}
                <option value="" {{ old('peminatan', $matkul->peminatan) == '' ? 'selected' : '' }}>-- Mata Kuliah Wajib --</option>
                <option value="Programming" {{ old('peminatan', $matkul->peminatan) == 'Programming' ? 'selected' : '' }}>Programming</option>
                <option value="Data" {{ old('peminatan', $matkul->peminatan) == 'Data' ? 'selected' : '' }}>Data</option>
                <option value="UX" {{ old('peminatan', $matkul->peminatan) == 'UX' ? 'selected' : '' }}>UX</option>
                <option value="Network" {{ old('peminatan', $matkul->peminatan) == 'Network' ? 'selected' : '' }}>Network</option>
            </select>
            <small>Biarkan pada pilihan "-- Mata Kuliah Wajib --" jika bukan mata kuliah peminatan.</small>
        </div>

        <div class="form-group">
            <label for="jumlah_kelas">Jumlah Kelas</label>
            <input type="number" id="jumlah_kelas" name="jumlah_kelas" value="{{ old('jumlah_kelas', $matkul->jumlah_kelas) }}" min="1" required class="form-control">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-update">Simpan</button>
            <a href="{{ route('admin.mata_kuliah.index') }}" class="btn btn-cancel">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/matakuliah/edit.css') }}">
@endpush
