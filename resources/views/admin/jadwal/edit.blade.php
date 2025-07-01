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
                    $uniq = $jadwal->unique_number;
                    $availableHari = isset($availableTimes[$uniq]) ? array_keys($availableTimes[$uniq]) : [];
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
                <option value="">Pilih Ruang dan Hari terlebih dahulu</option>
            </select>
            <small class="form-text text-muted">Format jam: HH:MM - HH:MM, contoh: 07:00 - 08:40</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/jadwal/edit.css') }}">
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Variabel dari Controller ---
    const availableTimes = @json($availableTimes);
    const existingJadwals = @json($existingJadwals);
    const roomCapacities = @json($roomCapacities);
    
    // Info jadwal yang sedang diedit
    const currentDosenUniq = '{{ $jadwal->unique_number }}';
    const currentMatkul = '{{ $jadwal->kode_mata_kuliah }}';
    const currentJam = '{{ $jadwal->jam }}';

    // --- Elemen-elemen Form ---
    const hariEl = document.getElementById('hari');
    const ruangEl = document.getElementById('nama_ruangan');
    const jamEl = document.getElementById('jam');

    // --- Daftar Semua Sesi Jam ---
    const SESSION_SLOTS = [
        "07:00 - 07:50", "07:50 - 08:40", "08:50 - 09:40", "09:40 - 10:30",
        "10:40 - 11:30", "12:10 - 13:10", "13:20 - 14:10", "14:10 - 15:00",
        "15:30 - 16:20", "16:20 - 17:10", "17:10 - 18:00", "18:30 - 19:20",
        "19:20 - 20:10", "20:10 - 21:00"
    ];
    
    function isOverlap(start1, end1, start2, end2) {
        return !(end1 <= start2 || end2 <= start1);
    }

    function populateJamOptions() {
        const selectedHari = hariEl.value;
        const selectedRuang = ruangEl.value;
        
        jamEl.innerHTML = '<option value="">Pilih Jam</option>';
        if (!selectedHari || !selectedRuang) {
            jamEl.innerHTML = '<option value="">Pilih Ruang dan Hari</option>';
            return;
        }

        const dosenDayRanges = (availableTimes[currentDosenUniq] && availableTimes[currentDosenUniq][selectedHari]) || [];
        if (dosenDayRanges.length === 0) return;

        SESSION_SLOTS.forEach(sesi => {
            const [startSesi, endSesi] = sesi.split(' - ').map(s => s.trim());

            // 1. Cek Ketersediaan Waktu Dosen
            const isDosenAvailable = dosenDayRanges.some(range => {
                const [startRange, endRange] = range.split(' - ').map(s => s.trim());
                return startSesi >= startRange && endSesi <= endRange;
            });
            if (!isDosenAvailable) return;

            // 2. Cek Konflik Jadwal Lain
            const conflictingSchedules = existingJadwals.filter(j => 
                j.hari === selectedHari && 
                j.ruang === selectedRuang && 
                isOverlap(startSesi, endSesi, j.start, j.end)
            );

            const roomCapacity = roomCapacities[selectedRuang] || 1;
            let isSlotAvailable = true;

            if (conflictingSchedules.length >= roomCapacity) {
                // Jika ruangan sudah penuh, cek apakah bisa untuk kelas paralel
                const allSameCourseAndLecturer = conflictingSchedules.every(j => 
                    j.kode_mata_kuliah === currentMatkul &&
                    j.unique_number === currentDosenUniq
                );

                if (!allSameCourseAndLecturer) {
                    isSlotAvailable = false; // Slot penuh dan diisi oleh matkul/dosen lain
                }
            }
            
            if (isSlotAvailable) {
                const option = document.createElement('option');
                option.value = sesi;
                option.textContent = sesi;
                if (sesi === currentJam) {
                    option.selected = true;
                }
                jamEl.appendChild(option);
            }
        });
    }

    // --- Event Listeners ---
    hariEl.addEventListener('change', populateJamOptions);
    ruangEl.addEventListener('change', populateJamOptions);

    // --- Inisialisasi Saat Halaman Dimuat ---
    populateJamOptions();
});
</script>
@endpush
