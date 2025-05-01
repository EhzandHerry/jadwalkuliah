@extends('layouts.layout')

@section('title', 'Kelas')

@section('header_title', 'Manajemen Kelas')

@section('content')
<h1>Daftar Kelas</h1>
    <!-- <a href="{{ route('admin.kelas.create') }}">Tambah Kelas</a> -->

    <table border="1" style="margin-top: 20px;">
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
                        <!-- Menampilkan NIDN dosen yang terkait dengan kelas -->
                        {{ $item->dosen->unique_number }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if (!$item->ruang_kelas_id) <!-- Jika belum ada ruang kelas -->
                        <form action="{{ route('admin.kelas.assignRuang', $item->id) }}" method="POST">
                            @csrf
                            <select name="ruang_kelas_id" required>
                                <option value="">Pilih Ruang Kelas</option>
                                @foreach ($ruangKelas as $ruang)
                                    <option value="{{ $ruang->id }}">{{ $ruang->nama_ruangan }} - {{ $ruang->nama_gedung }}</option>
                                @endforeach
                            </select>
                            <button type="submit">submit</button>
                        </form>
                    @else
                        <span>{{ $item->ruangKelas->nama_ruangan }} - {{ $item->ruangKelas->nama_gedung }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection

