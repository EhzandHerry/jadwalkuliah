@extends('layouts.layout')

@section('title', 'Edit Jadwal')
@section('header_title', 'Edit Jadwal')

@section('content')
<div class="container">
    <h1>Edit Jadwal Mata Kuliah {{ $jadwal->kode_matkul }} Kelas {{ $jadwal->kelas }}</h1>

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
                    <option value="{{ $r->nama_ruangan }}" 
                            data-capacity="{{ $r->kapasitas_kelas }}"
                            {{ $jadwal->nama_ruangan == $r->nama_ruangan ? 'selected' : '' }}>
                        {{ $r->nama_ruangan }} ({{ $r->kapasitas_kelas }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="hari">Hari</label>
            <select name="hari" id="hari" class="form-control" required>
                <option value="">Pilih Hari</option>
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                    <option value="{{ $hari }}" {{ $jadwal->hari == $hari ? 'selected' : '' }}>
                        {{ $hari }}
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
    const existingJadwals = @json($existingJadwals);
    const availableTimes = @json($availableTimes);
    const roomCapacities = @json($roomCapacities);
    
    // Info jadwal yang sedang diedit
    const currentDosenNidn = '{{ $jadwal->nidn }}';
    const currentMatkul = '{{ $jadwal->kode_matkul }}';
    const currentJam = '{{ $jadwal->jam }}';
    const currentSks = {{ optional($jadwal->mataKuliah)->sks ?? 1 }};

    const ALL_SLOTS = [
        "07:00 - 07:50", "07:50 - 08:40", "08:50 - 09:40", "09:40 - 10:30",
        "10:40 - 11:30", "12:10 - 13:10", "13:20 - 14:10", "14:10 - 15:00",
        "15:30 - 16:20", "16:20 - 17:10", "17:10 - 18:00", "18:30 - 19:20",
        "19:20 - 20:10", "20:10 - 21:00"
    ];

    function timeToMinutes(timeStr) {
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    function timeOverlaps(start1, end1, start2, end2) {
        const s1 = timeToMinutes(start1);
        const e1 = timeToMinutes(end1);
        const s2 = timeToMinutes(start2);
        const e2 = timeToMinutes(end2);
        return (s1 < e2) && (e1 > s2);
    }

    function isDosenAvailable(dosenNidn, hari, startTime, endTime) {
        const dosenTimes = availableTimes[dosenNidn];
        if (!dosenTimes || !dosenTimes[hari]) {
            return false;
        }

        return dosenTimes[hari].some(availableSlot => {
            if (typeof availableSlot === 'string') {
                const [availStart, availEnd] = availableSlot.split(' - ').map(t => t.trim());
                return timeToMinutes(startTime) >= timeToMinutes(availStart) && 
                       timeToMinutes(endTime) <= timeToMinutes(availEnd);
            } else {
                const [availStart, availEnd] = availableSlot.time.split(' - ').map(t => t.trim());
                return timeToMinutes(startTime) >= timeToMinutes(availStart) && 
                       timeToMinutes(endTime) <= timeToMinutes(availEnd);
            }
        });
    }

    function hasDosenConflict(dosenNidn, hari, startTime, endTime, currentMatkul) {
        return existingJadwals.some(jadwal => {
            return jadwal.dosen === dosenNidn && 
                   jadwal.hari === hari && 
                   jadwal.matkul !== currentMatkul &&
                   timeOverlaps(startTime, endTime, jadwal.start, jadwal.end);
        });
    }

    function checkRoomSlotAvailability(ruang, hari, startTime, endTime, dosenNidn, currentMatkul) {
        const roomCapacity = roomCapacities[ruang] || 1;
        
        const conflictingSchedules = existingJadwals.filter(jadwal => 
            jadwal.ruang === ruang && 
            jadwal.hari === hari && 
            timeOverlaps(startTime, endTime, jadwal.start, jadwal.end)
        );

        // Jika jumlah jadwal sudah mencapai atau melebihi kapasitas ruangan
        if (conflictingSchedules.length >= roomCapacity) {
            console.log(`❌ Room at/over capacity (${conflictingSchedules.length}/${roomCapacity})`);
            return false;
        }

        console.log(`✅ Room has available slots (${conflictingSchedules.length}/${roomCapacity})`);
        return true;
    }

    const hariEl = document.getElementById('hari');
    const ruangEl = document.getElementById('nama_ruangan');
    const jamEl = document.getElementById('jam');

    function populateTimeSlots() {
        const hari = hariEl.value;
        const ruang = ruangEl.value;
        
        jamEl.innerHTML = '<option value="">Pilih Jam</option>';

        if (!hari || !ruang) {
            jamEl.innerHTML = '<option value="">Pilih Ruang dan Hari</option>';
            return;
        }

        console.log(`\n🎯 === Populating time slots for ${currentMatkul} by ${currentDosenNidn} in ${ruang} on ${hari} ===`);
        console.log(`📝 Room capacity: ${roomCapacities[ruang] || 1} classes`);

        let hasAvailableSlots = false;

        for (let i = 0; i <= ALL_SLOTS.length - currentSks; i++) {
            const startTime = ALL_SLOTS[i].split(' - ')[0].trim();
            const endTime = ALL_SLOTS[i + currentSks - 1].split(' - ')[1].trim();
            const timeSlot = `${startTime} - ${endTime}`;

            console.log(`\n⏰ Checking slot: ${timeSlot}`);

            // 1. Cek ketersediaan dosen
            if (!isDosenAvailable(currentDosenNidn, hari, startTime, endTime)) {
                console.log(`❌ Lecturer not available: ${timeSlot}`);
                continue;
            }

            // 2. Cek konflik jadwal dosen dengan mata kuliah lain
            if (hasDosenConflict(currentDosenNidn, hari, startTime, endTime, currentMatkul)) {
                console.log(`❌ Lecturer has other class: ${timeSlot}`);
                continue;
            }

            // 3. PENGECEKAN KETAT: Cek slot ruang kelas
            if (!checkRoomSlotAvailability(ruang, hari, startTime, endTime, currentDosenNidn, currentMatkul)) {
                console.log(`❌ Room slot not available: ${timeSlot}`);
                continue;
            }

            // 4. Jika semua pengecekan lolos
            console.log(`✅ Time slot available: ${timeSlot}`);
            hasAvailableSlots = true;
            
            const option = document.createElement('option');
            option.value = ALL_SLOTS[i];
            option.textContent = timeSlot;

            // Set selected jika ini adalah jam yang sedang diedit
            if (timeSlot === currentJam) {
                option.selected = true;
            }
            
            jamEl.appendChild(option);
        }

        // Jika tidak ada slot yang tersedia
        if (!hasAvailableSlots) {
            const roomCapacity = roomCapacities[ruang] || 1;
            jamEl.innerHTML = `<option value="">Tidak ada waktu tersedia (Ruang penuh)</option>`;
            console.log(`❌ No available time slots - room capacity exceeded`);
        }
    }

    hariEl.addEventListener('change', populateTimeSlots);
    ruangEl.addEventListener('change', populateTimeSlots);

    // Inisialisasi saat halaman dimuat
    populateTimeSlots();

    // Debug info
    console.log('🏢 Room Capacities:', roomCapacities);
    console.log('📅 Existing Jadwals:', existingJadwals);
    console.log('⏰ Available Times:', availableTimes);
});
</script>
@endpush