@extends('layouts.layout')

@section('title', 'Matakuliah')

@section('header_title', 'Manajemen Matakuliah')

@section('content')
    <div class="content-container">
        <h1>Daftar Mata Kuliah</h1>

        <a href="{{ route('admin.mata_kuliah.create') }}" class="add-matkul-btn">Tambah Mata Kuliah</a>

        <form action="{{ route('admin.mata_kuliah.destroyMultiple') }}" method="POST" class="delete-matkul-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="delete-selected-btn">Hapus Terpilih</button>

            <table class="matkul-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" onclick="toggleSelectAll()"></th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>SKS</th>
                        <th>Kelas</th>
                        <th>NIDN</th>
                        <th>Nama Dosen</th>
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
                                            {{ $kelas->dosen->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($kelas->dosen)
                                            <a href="{{ route('admin.mata_kuliah.editDosen', ['kelas' => $kelas->id]) }}" class="edit-dosen-btn">
                                                Edit Dosen
                                            </a>
                                        @else
                                            <a href="{{ route('admin.mata_kuliah.addDosen', ['kelas' => $kelas->id]) }}" class="add-dosen-btn">
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
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/matakuliah/index.css') }}">
@endpush

<script>
    function toggleSelectAll() {
        var checkboxes = document.querySelectorAll('input[name="kelas_ids[]"]');
        var selectAllCheckbox = document.getElementById('select_all');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
</script>
