@extends('layouts.layout')

@section('title', 'Edit Dosen')

@section('header_title', 'Edit Dosen Kelas')

@section('content')
<h1>Edit Dosen untuk Kelas {{ $kelas->kelas }}</h1>

<form action="{{ route('admin.mata_kuliah.updateDosen', $kelas->id) }}" method="POST">
    @csrf
    @method('PUT')
    <label for="unique_number">Pilih Dosen:</label>
    <select name="unique_number" id="unique_number" required>
        @foreach($dosen as $dosenItem)
            <option value="{{ $dosenItem->unique_number }}" 
                {{ $kelas->unique_number == $dosenItem->unique_number ? 'selected' : '' }}>
                {{ $dosenItem->name }}
            </option>
        @endforeach
    </select>
    <button type="submit" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;">Update Dosen</button>
</form>
@endsection
