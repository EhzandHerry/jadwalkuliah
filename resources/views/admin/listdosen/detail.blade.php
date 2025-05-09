@extends('layouts.layout')

@section('title', 'Detail Dosen')

@section('header_title', 'Detail Dosen')

@section('content')
    <div class="dosen-detail-container">
        <h1>Detail Dosen</h1>
        
        <div class="dosen-details">
            <p><strong>Nama Dosen:</strong> {{ $dosen->name }}</p>
            <p><strong>NIDN:</strong> {{ $dosen->unique_number }}</p>
            <p><strong>Email:</strong> {{ $dosen->email }}</p>
            <p><strong>Phone:</strong> {{ $dosen->phone }}</p>
        </div>

        <a href="{{ route('admin.dosen.index') }}" class="back-btn">Back to Dosen List</a>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/listdosen/detail.css') }}">
@endpush
