@extends('layouts.layout')

@section('title', 'Manajemen Jadwal')
@section('header_title', 'Manajemen Jadwal')

@section('content')
<div class="jadwal-index-container">
  <h1>Daftar Jadwal</h1>

  {{-- Tombol export --}}
  <a href="{{ route('admin.jadwal.export') }}" class="btn btn-success mb-3">
    Export Jadwal ke Excel
  </a>
  <a href="{{ route('admin.jadwal.exportMatrix') }}" class="btn btn-success mb-3">
    Export Matrix Jadwal ke Excel
  </a>

  {{-- Tampilkan error validasi jam atau bentrok --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Tampilkan session error / success --}}
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Tabel jadwal --}}
  <table class="jadwal-table table table-striped">
    <thead class="thead-dark">
      <tr>
        <th>Kode Mata Kuliah</th>
        <th>Nama Mata Kuliah</th>
        <th>Kelas</th>
        <th>SKS</th>
        <th>Nama Dosen</th>
        <th>Ruang Kelas</th>
        <th>Hari</th>
        <th>Jam</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      {{-- Loop setiap baris kelas --}}
      @foreach($kelas as $k)
      <tr>
        <td>{{ optional($k->mataKuliah)->kode_matkul ?? '-' }}</td>
        <td>{{ optional($k->mataKuliah)->nama_matkul ?? '-' }}</td>
        <td>{{ $k->kelas }}</td>
        <td>{{ optional($k->mataKuliah)->sks ?? '-' }}</td>
        <td>{{ optional($k->dosen)->name ?? '-' }}</td>
        <td>
          {{-- 1) Jika dosen belum dipilih --}}
          @if(! $k->unique_number)
            <span class="text-danger">
              Silakan pilih dosen terlebih dahulu di halaman Matakuliah & Dosen!
            </span>

          {{-- 2) Jika sudah ada jadwal --}}
          @elseif($k->nama_ruangan)
            {{ $k->nama_ruangan }}

          {{-- 3) Jika belum dijadwalkan, tampilkan form assign --}}
          @else
            @php
              $uniq   = $k->dosen->unique_number ?? null;
              $hasAv  = isset($availableTimes[$uniq]) && count($availableTimes[$uniq]) > 0;
              $hariAv = $hasAv ? array_keys($availableTimes[$uniq]) : [];
            @endphp

            <form action="{{ route('admin.jadwal.assignRuang', $k->id) }}"
                  method="POST"
                  class="d-flex flex-wrap align-items-center">
              @csrf

              {{-- a) Dropdown pilih ruang dengan kapasitas --}}
              <select name="nama_ruangan"
                      data-id="{{ $k->id }}"
                      class="form-control form-control-sm mr-2"
                      required>
                <option value="">Pilih Ruang Kelas</option>
                @foreach($ruangKelasList as $r)
                  <option value="{{ $r->nama_ruangan }}"
                          data-capacity="{{ $r->kapasitas }}">
                    {{ $r->nama_ruangan }} – {{ $r->nama_gedung }} (kapasitas kelas {{ $r->kapasitas_kelas }})
                  </option>
                @endforeach
              </select>

              {{-- b) Dropdown pilih hari --}}
              <select name="hari"
                      id="hari-{{ $k->id }}"
                      class="form-control form-control-sm mr-2"
                      {{ $hasAv ? '' : 'disabled' }}
                      required>
                <option value="">Pilih Hari</option>
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                  <option value="{{ $hari }}"
                    @if(! in_array($hari, $hariAv)) disabled @endif>
                    {{ $hari }}
                  </option>
                @endforeach
              </select>

              {{-- c) Dropdown pilih jam --}}
              <select name="jam"
                      id="jam-{{ $k->id }}"
                      class="form-control form-control-sm mr-2"
                      {{ $hasAv ? '' : 'disabled' }}
                      required>
                <option value="">Pilih Jam</option>
              </select>

              {{-- d) Tombol simpan --}}
              <button type="submit"
                      class="btn btn-primary btn-sm">
                Simpan
              </button>
            </form>
          @endif
        </td>

        {{-- Hari & Jam setelah disimpan --}}
        <td>{{ $k->hari ?? '-' }}</td>
        <td>{{ $k->jam ?? '-' }}</td>

        {{-- Aksi Edit & Hapus --}}
        <td>
          @if($k->jadwal_id)
            <a href="{{ route('admin.jadwal.edit', $k->jadwal_id) }}"
               class="btn btn-warning btn-sm mr-1">Edit</a>
            <form action="{{ route('admin.jadwal.destroy', $k->jadwal_id) }}"
                  method="POST"
                  style="display:inline-block"
                  onsubmit="return confirm('Yakin ingin hapus jadwal ini?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
          @else
            <span class="text-muted">Belum dijadwalkan</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/jadwal/index.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1) semua jadwal yang sudah tersimpan, untuk cek bentrok
  const existingJadwals = @json($existingJadwals);

  // Daftar sesi tetap—harus identik dengan dropdown
  const SESSIONS = [
    "07:00 - 07:50","07:50 - 08:40",
    "08:50 - 09:40","09:40 - 10:30",
    "10:40 - 11:30","12:10 - 13:10",
    "13:20 - 14:10","14:10 - 15:00",
    "15:30 - 16:20","16:20 - 17:10",
    "17:10 - 18:00","18:30 - 19:20",
    "19:20 - 20:10","20:10 - 21:00"
  ];

  @foreach($kelas as $k)
  (function(){
    const matkul  = '{{ optional($k->mataKuliah)->kode_matkul }}';
    const uniq    = '{{ $k->dosen->unique_number ?? "" }}';
    const hariEl  = document.getElementById('hari-{{ $k->id }}');
    const jamEl   = document.getElementById('jam-{{ $k->id }}');
    const ruangEl = document.querySelector('select[name="nama_ruangan"][data-id="{{ $k->id }}"]');
    if (!hariEl || !jamEl || !ruangEl) return;

    function sessionOverlap(s1,e1,s2,e2){
      return !(e1 <= s2 || e2 <= s1);
    }

    function populateSessions(hari) {
      jamEl.innerHTML = '<option value="">Pilih Jam</option>';

      // kapasitas ruang
      const capacity = parseInt(ruangEl.selectedOptions[0]?.dataset.capacity || '1',10);
      // jumlah existing same matkul
      const existingSame = existingJadwals.filter(j=>
        j.ruang===ruangEl.value && j.matkul===matkul
      ).length;

      SESSIONS.forEach(sesi=>{
        const [sMulai,sAkhir] = sesi.split(' - ');

        // cek bentrok existing jadwal
        const hasConflict = existingJadwals.some(j => {
  if (j.hari !== hari) return false;

  // 1) bentrok dosen dengan matakuliah berbeda → blokir
  if (j.dosen === uniq
      && sessionOverlap(sMulai, sAkhir, j.start, j.end)
      && j.matkul !== matkul   // hanya blokir kalau beda matakuliah
  ) {
    return true;
  }

  // 2) bentrok ruang dengan matakuliah atau dosen berbeda → blokir
  if (j.ruang === ruangEl.value
      && sessionOverlap(sMulai, sAkhir, j.start, j.end)
      && (
           j.matkul !== matkul   // beda matakuliah
        || j.dosen !== uniq     // atau beda dosen
      )
  ) {
    return true;
  }

  // sisanya tidak dianggap bentrok
  return false;
});

        if (hasConflict) return;

        // tampilkan sesi
        const o = document.createElement('option');
        o.value = sesi;
        o.textContent = sesi;
        jamEl.appendChild(o);
      });
    }

    // event
    hariEl.addEventListener('change', ()=> populateSessions(hariEl.value));
    ruangEl.addEventListener('change', ()=> populateSessions(hariEl.value));
  })();
  @endforeach
});
</script>
@endpush
