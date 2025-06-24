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

  {{-- Validasi error --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Session message --}}
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
      @foreach($kelas as $k)
      <tr>
        <td>{{ optional($k->mataKuliah)->kode_matkul ?? '-' }}</td>
        <td>{{ optional($k->mataKuliah)->nama_matkul ?? '-' }}</td>
        <td>{{ $k->kelas }}</td>
        <td>{{ optional($k->mataKuliah)->sks ?? '-' }}</td>
        <td>{{ optional($k->dosen)->name ?? '-' }}</td>
        <td>
          {{-- Jika dosen belum dipilih --}}
          @if(! $k->unique_number)
            <span class="text-danger">
              Silakan pilih dosen di halaman Matakuliah & Dosen!
            </span>

          {{-- Jika sudah ada jadwal --}}
          @elseif($k->nama_ruangan)
            {{ $k->nama_ruangan }}

          {{-- Form assign --}}
          @else
            @php
              $uniq   = $k->dosen->unique_number;
              $hasAv  = isset($availableTimes[$uniq]) && count($availableTimes[$uniq]) > 0;
              $hariAv = $hasAv ? array_keys($availableTimes[$uniq]) : [];
            @endphp

            <form action="{{ route('admin.jadwal.assignRuang', $k->id) }}"
                  method="POST"
                  class="d-flex flex-wrap align-items-center">
              @csrf

              {{-- a) Ruang --}}
              <select name="nama_ruangan"
                      data-id="{{ $k->id }}"
                      class="form-control form-control-sm mr-2"
                      required>
                <option value="">Pilih Ruang Kelas</option>
                @foreach($ruangKelasList as $r)
                  <option value="{{ $r->nama_ruangan }}"
                          data-capacity="{{ $r->kapasitas }}">
                    {{ $r->nama_ruangan }} – {{ $r->nama_gedung }}
                    (kapasitas {{ $r->kapasitas_kelas }})
                  </option>
                @endforeach
              </select>

              {{-- b) Hari --}}
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

              {{-- c) Jam --}}
              <select name="jam"
                      id="jam-{{ $k->id }}"
                      class="form-control form-control-sm mr-2"
                      {{ $hasAv ? '' : 'disabled' }}
                      required>
                <option value="">Pilih Jam</option>
              </select>

              {{-- d) Simpan --}}
              <button type="submit" class="btn btn-primary btn-sm">
                Simpan
              </button>
            </form>
          @endif
        </td>

        {{-- Ditampilkan setelah disimpan --}}
        <td>{{ $k->hari ?? '-' }}</td>
        <td>{{ $k->jam ?? '-' }}</td>

        {{-- Edit / Hapus --}}
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
  const existingJadwals = @json($existingJadwals);
  const availableTimes  = @json($availableTimes);

  // ** slot per sesi dengan spasi di antara " - " **
  const SESSION_SLOTS = {
    1: ['07:00 - 07:50','07:50 - 08:40'],
    2: ['08:50 - 09:40','09:40 - 10:30'],
    3: ['10:40 - 11:30'],
    4: ['12:10 - 13:10'],
    5: ['13:20 - 14:10','14:10 - 15:00'],
    6: ['15:30 - 16:20','16:20 - 17:10','17:10 - 18:00'],
    7: ['18:30 - 19:20','19:20 - 20:10','20:10 - 21:00'],
  };

  @foreach($kelas as $k)
  (function(){
    const matkul = '{{ optional($k->mataKuliah)->kode_matkul }}';
    const uniq   = '{{ $k->dosen->unique_number ?? "" }}';
    const hariEl = document.getElementById('hari-{{ $k->id }}');
    const jamEl  = document.getElementById('jam-{{ $k->id }}');
    const ruangEl= document.querySelector(
      'select[name="nama_ruangan"][data-id="{{ $k->id }}"]'
    );
    if (!hariEl || !jamEl || !ruangEl) return;

    // Cek overlap dua interval
    function sessionOverlap(s1,e1,s2,e2){
      return !(e1 <= s2 || e2 <= s1);
    }

    function populateSessions(){
      const hari = hariEl.value;
      jamEl.innerHTML = '<option value="">Pilih Jam</option>';
      if (!hari || !availableTimes[uniq] || !availableTimes[uniq][hari]) return;

      // parse jendela available dosen
      const windows = availableTimes[uniq][hari].map(w=>{
        const [s,e] = w.split(' - ');
        return { start: s.trim(), end: e.trim() };
      });

      // cek tiap sesi
      Object.entries(SESSION_SLOTS).forEach(([no, slots])=>{
        // cari slot yang masuk window & tidak bentrok
        const ok = slots.filter(timestr=>{
          const [s,e] = timestr.split(' - ');
          const start = s.trim(), end = e.trim();

          // 1) harus sepenuhnya dalam salah satu window
          const inWin = windows.some(w=>
            start >= w.start && end <= w.end
          );
          if (!inWin) return false;

          // 2) cek bentrok existing jadwal
          const conflict = existingJadwals.some(j=>{
            if (j.hari !== hari) return false;
            // bentrok dosen (j.dosen===uniq & matkul beda)
            if (j.dosen===uniq
                && sessionOverlap(start,end,j.start,j.end)
                && j.matkul!==matkul) return true;
            // bentrok ruang (j.ruang===ruangEl.value & (matkul/dosen beda))
            if (j.ruang===ruangEl.value
                && sessionOverlap(start,end,j.start,j.end)
                && (j.matkul!==matkul || j.dosen!==uniq)) return true;
            return false;
          });
          return !conflict;
        });

        if (ok.length) {
          const g = document.createElement('optgroup');
          g.label = 'Sesi ' + no;
          ok.forEach(timestr=>{
            const o = document.createElement('option');
            o.value = timestr;       // "07:00 - 07:50"
            o.textContent = timestr; // "07:00 - 07:50"
            g.appendChild(o);
          });
          jamEl.appendChild(g);
        }
      });
    }

    hariEl.addEventListener('change', populateSessions);
    ruangEl.addEventListener('change', populateSessions);
  })();
  @endforeach

});
</script>
@endpush
