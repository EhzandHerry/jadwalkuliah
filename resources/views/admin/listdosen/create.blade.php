@extends('layouts.layout')

@section('title', 'Tambah Dosen')

@section('header_title', 'Tambah Dosen')

@section('content')
    <div class="dosen-form-container">
        <h1>Tambah Dosen</h1>

        <form action="{{ route('admin.dosen.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama Dosen</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-control">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required class="form-control">
            </div>
            <div class="form-group">
                <label for="unique_number">NIDN (Unique Number)</label>
                <input type="text" id="unique_number" name="unique_number" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Tambah Dosen</button>
        </form>
    </div>
@endsection
