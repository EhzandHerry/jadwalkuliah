@extends('layouts.layout')

@section('title', 'Edit Jadwal')
@section('header_title', 'Edit Jadwal')

@section('content')
<div class="container">
  <h1>Edit Jadwal Mata Kuliah {{ $jadwal->kode_mata_kuliah }} Kelas {{ $jadwal->kelas }}</h1>

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <form action="{{ route('admin.jadwal.update', $jadwal->id) }}" method="POST" id="editJadwalForm">
    @csrf
    @method('PUT')

    <div class="form-group">
      <label for="nama_ruangan">Ruang Kelas</label>
      <select name="nama_ruangan" id="nama_ruangan" class="form-control" required>
        <option value="">Pilih Ruang Kelas</option>
        @foreach($ruangKelasList as $r)
          <option value="{{ $r->nama_ruangan }}" {{ $jadwal->nama_ruangan == $r->nama_ruangan ? 'selected' : '' }}>
            {{ $r->nama_ruangan }} – {{ $r->nama_gedung }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
  <label for="hari">Hari</label>
  <select name="hari" id="hari" class="form-control" required>
    <option value="">Pilih Hari</option>
    @php
      // Ambil list hari yang available dari controller untuk dosen
      $availableHari = isset($availableTimes[$jadwal->unique_number]) ? array_keys($availableTimes[$jadwal->unique_number]) : [];
    @endphp

    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
      <option value="{{ $hari }}"
        {{ $jadwal->hari == $hari ? 'selected' : '' }}
        {{ in_array($hari, $availableHari) ? '' : 'disabled' }}>
        {{ $hari }}
        @if(!in_array($hari, $availableHari))
          (tidak tersedia)
        @endif
      </option>
    @endforeach
  </select>
</div>


    <div class="form-group">
      <label for="jam">Jam</label>
      <select name="jam" id="jam" class="form-control" required>
        <option value="">Pilih Jam</option>
        {{-- opsi jam akan diisi via JS --}}
      </select>
      <small class="form-text text-muted">Format jam: HH:MM - HH:MM, contoh: 07:00 - 08:40</small>
    </div>

    <button type="submit" class="btn btn-primary">Update Jadwal</button>
    <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/jadwal/edit.css') }}">
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const availableTimes = @json($availableTimes);
  const uniq = '{{ $jadwal->unique_number }}';
  const hariEl = document.getElementById('hari');
  const jamEl = document.getElementById('jam');

  const JAM_SESI = [
    "07:00 - 07:50","07:50 - 08:40",
    "08:50 - 09:40","09:40 - 10:30",
    "10:40 - 11:30","12:10 - 13:10",
    "13:20 - 14:10","14:10 - 15:00",
    "15:30 - 16:20","16:20 - 17:10",
    "17:10 - 18:00","18:30 - 19:20",
    "19:20 - 20:10","20:10 - 21:00"
  ];

  function populateSessions(selectedHari, selectedJam = null) {
    jamEl.innerHTML = '<option value="">Pilih Jam</option>';

    const dayRanges = (availableTimes[uniq] && availableTimes[uniq][selectedHari]) || [];

    if (dayRanges.length === 0) {
      jamEl.disabled = true;
      return;
    }

    jamEl.disabled = false;

    JAM_SESI.forEach(sesi => {
      const [startSesi, endSesi] = sesi.split(' - ');
      for (const range of dayRanges) {
        if (startSesi >= range.start && endSesi <= range.end) {
          const option = document.createElement('option');
          option.value = sesi;
          option.textContent = sesi;
          if (selectedJam === sesi) {
            option.selected = true;
          }
          jamEl.appendChild(option);
          break;
        }
      }
    });
  }

  // Inisialisasi pilihan jam saat halaman load
  if (hariEl.value) {
    populateSessions(hariEl.value, '{{ $jadwal->jam }}');
  }

  // Update jam saat hari berubah
  hariEl.addEventListener('change', function() {
    populateSessions(this.value);
  });
});

</script>
@endpush
