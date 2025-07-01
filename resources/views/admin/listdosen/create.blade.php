@extends('layouts.layout')

@section('title', 'Tambah Dosen')
@section('header_title', 'Tambah Dosen')

@section('content')
  <div class="add-dosen-container">
    <div class="add-dosen-form">
      <h1>Tambah Dosen</h1>

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

      <form action="{{ route('admin.dosen.store') }}" method="POST">
        @csrf

        <div class="form-group">
          <label for="name">Nama Dosen</label>
          {{-- 'old('name')' akan mengisi kembali input jika validasi gagal --}}
          <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-control @error('name') is-invalid @enderror">
          {{-- Menampilkan pesan error spesifik untuk field 'name' --}}
          @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-control @error('email') is-invalid @enderror">
          @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required class="form-control @error('password') is-invalid @enderror">
          @error('password')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="unique_number">NIDN</label>
          <input type="number" id="unique_number" name="unique_number" value="{{ old('unique_number') }}" required class="form-control @error('unique_number') is-invalid @enderror" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
          @error('unique_number')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group d-flex">
          <button type="submit" class="btn btn-primary mr-2">Simpan</button>
          <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('css')
  <link rel="stylesheet" href="{{ asset('css/admin/listdosen/create.css') }}">
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
