@extends('layouts.layout')

@section('title', 'Edit Dosen')
@section('header_title', 'Edit Dosen')

@section('content')
    <div class="update-dosen-container">
        <h1>Edit Dosen</h1>
        
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
        
        <!-- Dosen Update Form -->
        <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Name Field -->
            <div class="form-group">
                <label for="name">Nama Dosen</label>
                <input type="text" name="name" id="name" value="{{ old('name', $dosen->name) }}" required class="form-control @error('name') is-invalid @enderror">
                @error('name')
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
            
            <!-- Unique Number Field -->
            <div class="form-group">
                <label for="unique_number">NIDN</label>
                {{-- PERUBAHAN: Mengubah type="text" menjadi type="number" --}}
                <input type="number" name="unique_number" id="unique_number" value="{{ old('unique_number', $dosen->unique_number) }}" required class="form-control @error('unique_number') is-invalid @enderror" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('unique_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
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
{{-- Tambahkan style untuk pesan error --}}
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
    .alert-danger ul {
        margin-bottom: 0;
        padding-left: 20px;
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
