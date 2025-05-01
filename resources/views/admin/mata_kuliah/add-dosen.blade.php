@extends('layouts.layout')

@section('title', 'Tambah Dosen')

@section('header_title', 'Tambah Dosen ke Kelas')

@section('content')
<h1>Tambah Dosen untuk Kelas {{ $kelas->kelas }}</h1>

<form action="{{ route('admin.mata_kuliah.assignDosen', $kelas->id) }}" method="POST">
    @csrf
    <label for="unique_number">Pilih Dosen:</label>
    <select name="unique_number" id="unique_number" required>
        @foreach($dosen as $dosenItem)
            <option value="{{ $dosenItem->unique_number }}" 
                {{ $kelas->unique_number == $dosenItem->unique_number ? 'selected' : '' }}>
                {{ $dosenItem->name }}
            </option>
        @endforeach
    </select>
    <button type="submit" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;">Tambah Dosen</button>
</form>
@endsection
