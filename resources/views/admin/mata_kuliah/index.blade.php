@extends('layouts.layout')

@section('title', 'Matakuliah')

@section('header_title', 'Manajemen Matakuliah')

@section('content')
<h1>Daftar Mata Kuliah</h1>

<a href="{{ route('admin.mata_kuliah.create') }}" style="margin-top: 20px; display: inline-block; padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">Tambah Mata Kuliah</a>

<form action="{{ route('admin.mata_kuliah.destroyMultiple') }}" method="POST">
    @csrf
    @method('DELETE') <!-- Metode yang benar untuk penghapusan -->
    <button type="submit" style="margin-left: 10px; padding: 10px 15px; background-color: #e74c3c; color: white; border: none; border-radius: 5px;">Hapus Terpilih</button>
    <table border="1" style="margin-top: 20px;">
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all" onclick="toggleSelectAll()"></th>
                <th>Kode</th>
                <th>Nama</th>
                <th>SKS</th>
                <th>Kelas</th>
                <th>NIDN</th>
                <th>Nama Dosen</th> <!-- Kolom nama dosen ditambahkan -->
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @isset($mataKuliahs)
                @foreach ($mataKuliahs as $matkul)
                    @foreach ($matkul->kelas as $kelas)
                        <tr>
                            <td><input type="checkbox" name="kelas_ids[]" value="{{ $kelas->id }}"></td>
                            <td>{{ $matkul->kode_matkul }}</td>
                            <td>{{ $matkul->nama_matkul }}</td>
                            <td>{{ $matkul->sks }}</td>
                            <td>{{ $kelas->kelas }}</td>
                            <td>
                                @if ($kelas->dosen)
                                    {{ $kelas->dosen->unique_number }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($kelas->dosen)
                                    {{ $kelas->dosen->name }} <!-- Menampilkan nama dosen -->
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <!-- Check if the class has a dosen assigned -->
                                @if ($kelas->dosen)
                                    <a href="{{ route('admin.mata_kuliah.editDosen', ['kelas' => $kelas->id]) }}" 
                                       style="padding: 3px; background-color: #f39c12; color: white; text-decoration: none; border-radius: 5px;">
                                       Edit Dosen
                                    </a>
                                @else
                                    <a href="{{ route('admin.mata_kuliah.addDosen', ['kelas' => $kelas->id]) }}" 
                                       style="padding: 3px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
                                       Tambah Dosen
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endisset
        </tbody>
    </table>
</form>

@endsection

<script>
    function toggleSelectAll() {
        var checkboxes = document.querySelectorAll('input[name="kelas_ids[]"]');
        var selectAllCheckbox = document.getElementById('select_all');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
</script>
