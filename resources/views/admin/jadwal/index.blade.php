@extends('layouts.layout')

@section('title', 'Manajemen Jadwal')
@section('header_title', 'Manajemen Jadwal')

@section('content')
<div class="jadwal-index-container">
  <h1>Daftar Jadwal</h1>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="jadwal-table table table-striped">
    <thead class="thead-dark">
      <tr>
        <th>Kode Mata Kuliah</th>
        <th>Nama Mata Kuliah</th>
        <th>Kelas</th>
        <th>NIDN</th>
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
        <td>{{ $k->mataKuliah->kode_matkul ?? '-' }}</td>
        <td>{{ $k->mataKuliah->nama_matkul ?? '-' }}</td>
        <td>{{ $k->kelas }}</td>
        <td>{{ $k->dosen->unique_number ?? '-' }}</td>
        <td>{{ $k->dosen->name ?? '-' }}</td>
        <td>
          @if(! $k->unique_number)
            <span class="text-danger">
              Silakan pilih dosen terlebih dahulu di halaman Matakuliah & Dosen!
            </span>
          @elseif($k->nama_ruangan)
            {{ $k->nama_ruangan }}
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

              <select name="nama_ruangan"
                      class="form-control form-control-sm mr-2"
                      required>
                <option value="">Pilih Ruang Kelas</option>
                @foreach($ruangKelasList as $r)
                  <option value="{{ $r->nama_ruangan }}">
                    {{ $r->nama_ruangan }} – {{ $r->nama_gedung }}
                  </option>
                @endforeach
              </select>

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

              <select name="jam"
                      id="jam-{{ $k->id }}"
                      class="form-control form-control-sm mr-2"
                      {{ $hasAv ? '' : 'disabled' }}
                      required>
                <option value="">Pilih Jam</option>
                {{-- akan di-populate via JS --}}
              </select>

              <button type="submit"
                      class="btn btn-primary btn-sm">
                Simpan
              </button>
            </form>
          @endif
        </td>
        <td>{{ $k->hari ?? '-' }}</td>
        <td>{{ $k->jam ?? '-' }}</td>
        <td>
        @if($k->jadwal_id)
          <a href="{{ route('admin.jadwal.edit', $k->jadwal_id) }}" class="btn btn-warning btn-sm mr-1">Edit</a>

          <form action="{{ route('admin.jadwal.destroy', $k->jadwal_id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin hapus jadwal ini?');">
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
  const availableTimes = @json($availableTimes);

  @foreach($kelas as $k)
  (function(){
    const uniq = '{{ $k->dosen->unique_number ?? "" }}';
    const hariEl = document.getElementById('hari-{{ $k->id }}');
    const jamEl  = document.getElementById('jam-{{ $k->id }}');
    if (!hariEl||!jamEl) return;

    const JAM_SESI = [
      "07:00 - 07:50","07:50 - 08:40",
      "08:50 - 09:40","09:40 - 10:30",
      "10:40 - 11:30","12:10 - 13:10",
      "13:20 - 14:10","14:10 - 15:00",
      "15:30 - 16:20","16:20 - 17:10",
      "17:10 - 18:00","18:30 - 19:20",
      "19:20 - 20:10","20:10 - 21:00"
    ];

    function populateSessions(hari) {
      jamEl.innerHTML = '<option value="">Pilih Jam</option>';
      const dayRanges = (availableTimes[uniq]||{})[hari]||[];
      if (!dayRanges.length) {
        jamEl.disabled = true;
        return;
      }
      jamEl.disabled = false;

      // untuk setiap sesi, cek apakah seluruh rentang sesi masuk dalam salah satu dayRange
      JAM_SESI.forEach(sesi => {
        const [sMulai,sAkhir] = sesi.split(' - ');
        for (let r of dayRanges) {
          if (sMulai >= r.start && sAkhir <= r.end) {
            let o = document.createElement('option');
            o.value = sesi;
            o.textContent = sesi;
            jamEl.appendChild(o);
            break;
          }
        }
      });
    }

    // kalau hari sudah punya value (reload form), isi sekalian
    if (hariEl.value) populateSessions(hariEl.value);

    hariEl.addEventListener('change', e => {
      populateSessions(e.target.value);
    });
  })();
  @endforeach
});
</script>
@endpush
