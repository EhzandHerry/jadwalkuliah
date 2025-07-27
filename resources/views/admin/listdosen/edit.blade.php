@extends('layouts.layout')

@section('title', 'Edit Dosen')
@section('header_title', 'Edit Dosen')

@section('content')
    <div class="update-dosen-container">
        <h1>Edit Dosen</h1>
        
        {{-- Menampilkan pesan error khusus untuk NIDN yang sudah terdaftar di kelas --}}
        @if (session('error_nidn_kelas'))
            <div class="alert alert-warning">
                <strong>Perhatian!</strong> {{ session('error_nidn_kelas') }}
                <br><br>
                <strong>Alternatif:</strong>
                <ul>
                    <li>Hapus dosen dari semua kelas yang terdaftar terlebih dahulu</li>
                    <li>Atau gunakan fitur edit pada halaman manajemen kelas</li>
                    <li>Atau buat dosen baru dengan NIDN yang berbeda</li>
                </ul>
            </div>
        @endif
        
        {{-- Menampilkan pesan error validasi umum --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> Terjadi beberapa masalah dengan input Anda.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Info Box jika dosen terdaftar di kelas -->
        @php
            $dosenTerdaftarDiKelas = DB::table('kelas')->where('nidn', $dosen->nidn)->exists();
        @endphp
        
        
        
        <!-- Dosen Update Form -->
        <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Name Field -->
            <div class="form-group">
                <label for="nama">Nama Dosen</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $dosen->nama) }}" required class="form-control @error('nama') is-invalid @enderror">
                @error('nama')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- Email Field -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $dosen->email) }}" required class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- NIDN Field -->
<div class="form-group">
    <label for="nidn">NIDN (Harus 10 digit)</label>
    <input type="number" name="nidn" id="nidn" value="{{ old('nidn', $dosen->nidn) }}" required 
           class="form-control @error('nidn') is-invalid @enderror @if(session('error_nidn_kelas')) is-invalid @endif" 
           oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
           minlength="10" 
           maxlength="10"
           @if($dosenTerdaftarDiKelas) data-original-nidn="{{ $dosen->nidn }}" @endif>
    @error('nidn')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
    @if(session('error_nidn_kelas'))
        <span class="invalid-feedback">{{ session('error_nidn_kelas') }}</span>
    @endif
    <small class="form-text text-muted">
        NIDN harus terdiri dari tepat 10 digit angka
    </small>
</div>
            
            <!-- Submit & Cancel Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn btn-update">Simpan</button>
                <a href="{{ route('admin.dosen.index') }}" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/listdosen/edit.css') }}">
<style>
    .is-invalid {
        border-color: #e3342f !important;
    }
    .invalid-feedback {
        color: #e3342f;
        font-size: 0.875em;
        margin-top: 0.25rem;
        display: block;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .25rem;
    }
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .25rem;
    }
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .25rem;
    }
    .alert-danger ul, .alert-warning ul, .alert-info ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    .form-text {
        color: #6c757d;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
    .text-warning {
        color: #ffc107 !important;
    }
    /* Sembunyikan panah stepper pada input number */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }
    input[type=number] {
      -moz-appearance: textfield;
    }
</style>
@endpush