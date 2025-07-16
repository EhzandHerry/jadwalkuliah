@extends('layouts.layout')

@section('title', 'Manajemen Mata Kuliah')
@section('header_title', 'Tambah Mata Kuliah')

@section('content')
<div class="content-container">
    <h1>Tambah Mata Kuliah</h1>

    {{-- Validation Errors --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any() && !session('error'))
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.mata_kuliah.store') }}"
          method="POST"
          class="add-matkul-form">
        @csrf

        <div class="form-group">
            <label for="kode_matkul">Kode Mata Kuliah</label>
            <input type="text"
                   id="kode_matkul"
                   name="kode_matkul"
                   value="{{ old('kode_matkul') }}"
                   required>
        </div>

        <div class="form-group">
            <label for="nama_matkul">Nama Mata Kuliah</label>
            <input type="text"
                   id="nama_matkul"
                   name="nama_matkul"
                   value="{{ old('nama_matkul') }}"
                   required>
        </div>

        <div class="form-group">
            <label for="sks">SKS</label>
            <input type="number"
                   id="sks"
                   name="sks"
                   value="{{ old('sks') }}"
                   min="0"
                   required>
        </div>

        <div class="form-group">
            <label for="jumlah_kelas">Jumlah Kelas</label>
            <input type="number"
                   id="jumlah_kelas"
                   name="jumlah_kelas"
                   value="{{ old('jumlah_kelas') }}"
                   min="1"
                   required>
            <small>
                Masukkan berapa banyak kelas yang akan dibuat (Contoh: 1(A), 2(A,B), 3(A,B,C)).
            </small>
        </div>

        <div class="form-group">
            <label for="semester">Semester</label>
            <select id="semester"
                    name="semester"
                    class="form-control"
                    required>
                <option value="" disabled {{ old('semester') ? '' : 'selected' }}>
                    Pilih Semester
                </option>
                @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}"
                        {{ old('semester') == (string)$i ? 'selected' : '' }}>
                        Semester {{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        {{-- PENAMBAHAN FORM PEMINATAN --}}
        <div class="form-group">
            <label for="peminatan">Peminatan (Opsional)</label>
            <select id="peminatan" name="peminatan" class="form-control">
                {{-- Opsi ini akan mengirimkan nilai NULL ke database --}}
                <option value="">Mata Kuliah Wajib</option>
                <option value="Programming" {{ old('peminatan') == 'Programming' ? 'selected' : '' }}>Programming</option>
                <option value="Data" {{ old('peminatan') == 'Data' ? 'selected' : '' }}>Data</option>
                <option value="UX" {{ old('peminatan') == 'UX' ? 'selected' : '' }}>UX</option>
                <option value="Network" {{ old('peminatan') == 'Network' ? 'selected' : '' }}>Network</option>
            </select>
            <small>Biarkan pada pilihan "Mata Kuliah Wajib" jika bukan mata kuliah peminatan.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn submit-btn">Simpan</button>
            <a href="{{ route('admin.mata_kuliah.index') }}" class="btn cancel-btn">Batal</a>
        </div>
        
    </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/create.css') }}">
@endpush
