{{-- resources/views/admin/mata_kuliah/index.blade.php --}}
@extends('layouts.layout')

@section('title', 'Matakuliah')
@section('header_title', 'Manajemen Matakuliah')

@section('content')
<div class="content-container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Matakuliah</h1>
    <a href="{{ route('admin.mata_kuliah.create') }}" class="add-matkul-btn">
      Tambah Mata Kuliah
    </a>
  </div>

  {{-- Form untuk hapus multiple --}}
  <form id="delete-multiple-form"
        action="{{ route('admin.mata_kuliah.destroyMultiple') }}"
        method="POST"
        class="mb-3">
    @csrf
    @method('DELETE')
    <button type="submit"
            class="btn btn-danger"
            onclick="return confirm('Yakin ingin menghapus yang terpilih?')">
      Hapus Terpilih
    </button>
  </form>

  <table class="table table-striped matkul-table">
    <thead class="thead-dark">
      <tr>
        <th style="width:40px">
          <input type="checkbox" id="select_all" onclick="toggleSelectAll()">
        </th>
        <th>Kode</th>
        <th>Nama</th>
        <th>SKS</th>
        <th>Semester</th>
        <th>Kelas</th>
        <th>NIDN</th>
        <th>Nama Dosen</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($mataKuliahs as $matkul)
        @foreach($matkul->kelas as $kelas)
          <tr>
            {{-- Checkbox untuk hapus multiple --}}
            <td>
              <input type="checkbox"
                     name="kelas_ids[]"
                     form="delete-multiple-form"
                     value="{{ $kelas->id }}">
            </td>
            <td>{{ $matkul->kode_matkul }}</td>
            <td>{{ $matkul->nama_matkul }}</td>
            <td>{{ $matkul->sks }}</td>
            <td>{{ $matkul->semester }}</td>
            <td>{{ $kelas->kelas }}</td>

            @if($kelas->dosen)
              <td>{{ $kelas->dosen->unique_number }}</td>
              <td>{{ $kelas->dosen->name }}</td>
              <td>
                <a href="{{ route('admin.mata_kuliah.editDosen', ['kelas' => $kelas->id]) }}"
                   class="btn btn-warning btn-sm">
                  Edit Dosen
                </a>
              </td>
            @else
              <td>–</td>
              <td>
                <form action="{{ route('admin.mata_kuliah.assignDosen', ['kelasId' => $kelas->id]) }}"
                      method="POST"
                      class="form-inline">
                  @csrf
                  <select name="unique_number"
                          class="form-control form-control-sm mr-2"
                          required>
                    <option value="">Pilih Dosen</option>
                    @foreach($dosenList as $dos)
                      <option value="{{ $dos->unique_number }}">{{ $dos->name }}</option>
                    @endforeach
                  </select>
                  <button type="submit"
                          class="btn btn-success btn-sm">
                    Assign
                  </button>
                </form>
              </td>
            @endif
          </tr>
        @endforeach
      @endforeach
    </tbody>
  </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/matakuliah/index.css') }}">
@endpush

@push('scripts')
<script>
function toggleSelectAll() {
  const checked = document.getElementById('select_all').checked;
  document.querySelectorAll('input[name="kelas_ids[]"]')
          .forEach(cb => cb.checked = checked);
}
</script>
@endpush
