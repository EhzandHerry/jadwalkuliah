{{-- resources/views/admin/listdosen/edit.blade.php --}}
@extends('layouts.layout')

@section('title', 'Update Dosen')
@section('header_title', 'Update Dosen')

@section('content')
    <div class="update-dosen-container">
        <h1>Update Dosen</h1>
        
        <!-- Dosen Update Form -->
        <form action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Name Field -->
            <div class="form-group">
                <label for="name">Nama Dosen</label>
                <input type="text" name="name" id="name" value="{{ $dosen->name }}" required class="form-control">
            </div>
            
            <!-- Email Field -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ $dosen->email }}" required class="form-control">
            </div>
            
            <!-- Phone Field -->
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ $dosen->phone }}" required class="form-control">
            </div>
            
            <!-- Unique Number Field -->
            <div class="form-group">
                <label for="unique_number">NIDN</label>
                <input type="text" name="unique_number" id="unique_number" value="{{ $dosen->unique_number }}" required class="form-control">
            </div>
            
            <!-- Submit & Cancel Buttons -->
            <div class="form-actions">
  <button type="submit" class="btn btn-update">Update Dosen</button>
  <a href="{{ route('admin.dosen.index') }}" class="btn-cancel">Batal</a>
</div>

        </form>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/listdosen/edit.css') }}">
@endpush
