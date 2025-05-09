@extends('layouts.layout')

@section('title', 'Kelas')

@section('header_title', 'Manajemen Kelas')

@section('content')
    <div class="kelas-index-container">
        <h1>Daftar Kelas</h1>

        <table class="kelas-table">
            <thead>
                <tr>
                    <th>Kode Mata Kuliah</th>
                    <th>Nama Kelas</th>
                    <th>Dosen (NIDN)</th>
                    <th>Ruang Kelas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kelas as $item)
                    <tr>
                        <td>{{ $item->kode_matkul }}</td>
                        <td>{{ $item->kelas }}</td>
                        <td>
                            @if ($item->dosen) 
                                {{ $item->dosen->unique_number }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if (!$item->ruang_kelas_id) 
                                <form action="{{ route('admin.kelas.assignRuang', $item->id) }}" method="POST" class="assign-ruang-form">
                                    @csrf
                                    <select name="ruang_kelas_id" required class="select-ruang">
                                        <option value="">Pilih Ruang Kelas</option>
                                        @foreach ($ruangKelas as $ruang)
                                            <option value="{{ $ruang->id }}">{{ $ruang->nama_ruangan }} - {{ $ruang->nama_gedung }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="submit-btn">Submit</button>
                                </form>
                            @else
                                <span>{{ $item->ruangKelas->nama_ruangan }} - {{ $item->ruangKelas->nama_gedung }}</span>
                                <!-- Display Edit Button only if ruang_kelas_id exists -->
                                <a href="{{ route('admin.kelas.edit', $item->id) }}" class="edit-btn">Edit</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/kelas/index.css') }}">
@endpush
