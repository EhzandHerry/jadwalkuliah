@extends('layouts.layout')

@section('title', 'Manajemen Jadwal')
@section('header_title', 'Manajemen Jadwal')

@section('content')
<div class="container-fluid jadwal-index-container">
    <h1 class="mb-4">Daftar Jadwal</h1>

    <form action="{{ route('admin.jadwal.index') }}" method="GET" class="filter-controls">
        <a href="{{ route('admin.jadwal.previewMatrix') }}" class="btn btn-success">
            <i class="fas fa-table"></i> Preview Matrix Jadwal
        </a>
        <div class="form-group">
            <select name="semester_type" id="semester_type" class="form-control">
                <option value="gasal" {{ request('semester_type', 'genap') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                <option value="genap" {{ request('semester_type', 'genap') == 'genap' ? 'selected' : '' }}>Genap</option>
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="search" id="search" class="form-control" placeholder="Nama mata kuliah..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-apply-filter">
            <i class="fas fa-filter"></i> Terapkan Filter
        </button>
    </form>

    @if ($errors->any() || session('error') || session('success'))
    <div class="mt-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
    </div>
    @endif

    <div class="table-responsive mt-4">
        <table class="jadwal-table table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Kode MK</th>
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
                @forelse($kelas as $k)
                <tr data-kelas-id="{{ $k->id }}" data-kode-matkul="{{ optional($k->mataKuliah)->kode_matkul }}" data-nidn="{{ $k->nidn }}">
                    <td>{{ optional($k->mataKuliah)->kode_matkul ?? '-' }}</td>
                    <td>{{ optional($k->mataKuliah)->nama_matkul ?? '-' }}</td>
                    <td>{{ $k->kelas }}</td>
                    <td data-sks="{{ optional($k->mataKuliah)->sks ?? 0 }}">{{ optional($k->mataKuliah)->sks ?? '-' }}</td>
                    <td>{{ optional($k->dosen)->nama ?? '-' }}</td>
                    
                    @if($k->jadwal_id)
                        <td>{{ $k->nama_ruangan }}</td>
                        <td>{{ $k->hari }}</td>
                        <td>{{ $k->jam }}</td>
                        <td>
                            <a href="{{ route('admin.jadwal.edit', $k->jadwal_id) }}" class="btn btn-warning btn-sm mr-1">Edit</a>
                            <form action="{{ route('admin.jadwal.destroy', $k->jadwal_id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin hapus jadwal ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    @elseif($k->nidn)
                        <td>
                            <select name="nama_ruangan" data-id="{{ $k->id }}" class="form-control form-control-sm" style="min-width: 180px;" required form="form-jadwal-{{$k->id}}">
                                <option value="">Pilih Ruang Kelas</option>
                                @foreach($ruangKelasList as $r)
                                    <option value="{{ $r->nama_ruangan }}" data-capacity="{{ $r->kapasitas_kelas }}">
                                        {{ $r->nama_ruangan }} ({{ $r->kapasitas_kelas }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="hari" id="hari-{{ $k->id }}" class="form-control form-control-sm" style="min-width: 110px;" required form="form-jadwal-{{$k->id}}">
                                <option value="">Pilih Hari</option>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                                    <option value="{{ $hari }}">{{ $hari }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="jam" id="jam-{{ $k->id }}" class="form-control form-control-sm" style="min-width: 130px;" disabled required form="form-jadwal-{{$k->id}}">
                                <option value="">Pilih Hari Dulu</option>
                            </select>
                        </td>
                        <td>
                            <form action="{{ route('admin.jadwal.assignRuang', $k->id) }}" method="POST" id="form-jadwal-{{$k->id}}" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan jadwal ini?')">
                                @csrf
                            </form>
                            <button type="submit" form="form-jadwal-{{$k->id}}" class="btn btn-primary btn-sm">Simpan</button>
                        </td>
                    @else
                        <td colspan="3"><span class="text-danger font-italic">Dosen belum dipilih.</span></td>
                        <td>-</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <strong>Tidak ada data yang cocok dengan filter yang diterapkan.</strong>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/jadwal/index.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const existingJadwals = @json($existingJadwals);
    const availableTimes = @json($availableTimes);
    const roomCapacities = @json($roomCapacities);

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

    // PERBAIKAN KETAT: Function untuk cek slot ruang yang benar-benar ketat
    function checkRoomSlotAvailability(ruang, hari, startTime, endTime, dosenNidn, currentMatkul) {
        // Ambil kapasitas ruang
        const roomCapacity = roomCapacities[ruang] || 1;
        
        console.log(`\n🔍 Checking room ${ruang} (capacity: ${roomCapacity})`);
        
        // Ambil semua jadwal yang bentrok di ruang dan waktu yang sama
        const conflictingSchedules = existingJadwals.filter(jadwal => 
            jadwal.ruang === ruang && 
            jadwal.hari === hari && 
            timeOverlaps(startTime, endTime, jadwal.start, jadwal.end)
        );

        console.log(`📊 Found ${conflictingSchedules.length} conflicting schedules in room ${ruang}:`);
        conflictingSchedules.forEach(schedule => {
            console.log(`   - ${schedule.matkul} by ${schedule.dosen} (${schedule.start} - ${schedule.end})`);
        });

        // ATURAN KETAT: Jika jumlah jadwal sudah mencapai atau melebihi kapasitas ruangan
        if (conflictingSchedules.length >= roomCapacity) {
            console.log(`❌ Room at/over capacity (${conflictingSchedules.length}/${roomCapacity})`);
            
            // BAHKAN UNTUK KELAS PARALEL: Jika ruangan sudah penuh, TIDAK BOLEH menambah lagi
            // Tidak peduli apakah itu mata kuliah dan dosen yang sama
            console.log(`❌ Room full - no more classes allowed regardless of parallel status`);
            return false;
        }

        // Jika belum mencapai kapasitas, cek apakah masih ada slot
        console.log(`✅ Room has available slots (${conflictingSchedules.length}/${roomCapacity})`);
        return true;
    }

    // TAMBAHAN: Function untuk cek apakah ini akan jadi kelas paralel
    function isParallelClass(ruang, hari, startTime, endTime, dosenNidn, currentMatkul) {
        const conflictingSchedules = existingJadwals.filter(jadwal => 
            jadwal.ruang === ruang && 
            jadwal.hari === hari && 
            timeOverlaps(startTime, endTime, jadwal.start, jadwal.end)
        );

        // Jika ada jadwal lain di ruang dan waktu yang sama
        if (conflictingSchedules.length > 0) {
            // Cek apakah semua jadwal adalah dari mata kuliah dan dosen yang sama
            const allSameSubjectAndLecturer = conflictingSchedules.every(jadwal => 
                jadwal.dosen === dosenNidn && jadwal.matkul === currentMatkul
            );
            
            return allSameSubjectAndLecturer;
        }
        
        return false;
    }

    document.querySelectorAll('tr[data-kelas-id]').forEach(row => {
        const hariEl = row.querySelector('select[name="hari"]');
        if (!hariEl) return;

        const ruangEl = row.querySelector('select[name="nama_ruangan"]');
        const jamEl = row.querySelector('select[name="jam"]');
        
        if (!ruangEl || !jamEl) return;

        const matkul = row.dataset.kodeMatkul;
        const dosenNidn = row.dataset.nidn;
        const sks = parseInt(row.querySelector('td[data-sks]').dataset.sks) || 1;

        function populateTimeSlots() {
            const hari = hariEl.value;
            const ruang = ruangEl.value;
            
            jamEl.innerHTML = '<option value="">Pilih Jam</option>';
            jamEl.disabled = true;

            if (!hari) {
                jamEl.innerHTML = '<option value="">Pilih Hari Dulu</option>';
                return;
            }

            if (!ruang) {
                jamEl.innerHTML = '<option value="">Pilih Ruang Dulu</option>';
                return;
            }
            
            jamEl.disabled = false;

            console.log(`\n🎯 === Populating time slots for ${matkul} by ${dosenNidn} in ${ruang} on ${hari} ===`);
            console.log(`📝 Room capacity: ${roomCapacities[ruang] || 1} classes`);

            let hasAvailableSlots = false;

            for (let i = 0; i <= ALL_SLOTS.length - sks; i++) {
                const startTime = ALL_SLOTS[i].split(' - ')[0].trim();
                const endTime = ALL_SLOTS[i + sks - 1].split(' - ')[1].trim();
                const timeSlot = `${startTime} - ${endTime}`;

                console.log(`\n⏰ Checking slot: ${timeSlot}`);

                // 1. Cek ketersediaan dosen
                if (!isDosenAvailable(dosenNidn, hari, startTime, endTime)) {
                    console.log(`❌ Lecturer not available: ${timeSlot}`);
                    continue;
                }

                // 2. Cek konflik jadwal dosen dengan mata kuliah lain
                if (hasDosenConflict(dosenNidn, hari, startTime, endTime, matkul)) {
                    console.log(`❌ Lecturer has other class: ${timeSlot}`);
                    continue;
                }

                // 3. PENGECEKAN KETAT: Cek slot ruang kelas
                if (!checkRoomSlotAvailability(ruang, hari, startTime, endTime, dosenNidn, matkul)) {
                    console.log(`❌ Room slot not available: ${timeSlot}`);
                    continue;
                }

                // 4. Jika semua pengecekan lolos
                console.log(`✅ Time slot available: ${timeSlot}`);
                hasAvailableSlots = true;
                
                const option = document.createElement('option');
                option.value = ALL_SLOTS[i];
                option.textContent = timeSlot;

                // Tambahkan info jika ini akan jadi kelas paralel
                if (isParallelClass(ruang, hari, startTime, endTime, dosenNidn, matkul)) {
                    const parallelClasses = existingJadwals
                        .filter(jadwal => 
                            jadwal.ruang === ruang && 
                            jadwal.hari === hari && 
                            jadwal.dosen === dosenNidn &&
                            jadwal.matkul === matkul &&
                            timeOverlaps(startTime, endTime, jadwal.start, jadwal.end)
                        )
                        .map(jadwal => jadwal.kelas);

                    if (parallelClasses.length > 0) {
                        option.textContent += ` (Paralel: ${parallelClasses.join(', ')})`;
                    }
                }
                
                jamEl.appendChild(option);
            }

            // Jika tidak ada slot yang tersedia
            if (!hasAvailableSlots) {
                const roomCapacity = roomCapacities[ruang] || 1;
                
                // Hitung berapa banyak slot yang terpakai di hari ini
                const usedSlots = existingJadwals.filter(jadwal => 
                    jadwal.ruang === ruang && jadwal.hari === hari
                ).length;
                
                jamEl.innerHTML = `<option value="">Tidak ada waktu tersedia (Ruang penuh: ${usedSlots}/${roomCapacity} slot terpakai)</option>`;
                console.log(`❌ No available time slots - room capacity exceeded`);
            }
        }
        
        hariEl.addEventListener('change', populateTimeSlots);
        ruangEl.addEventListener('change', populateTimeSlots);
    });

    // Debug info
    console.log('🏢 Room Capacities:', roomCapacities);
    console.log('📅 Existing Jadwals:', existingJadwals);
    console.log('⏰ Available Times:', availableTimes);
});
</script>
@endpush