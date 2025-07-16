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
            <input type="text" name="search" id="search" class="form-control" placeholder="Nama mata kuliah..." value="{{ request('search') }}">
        </div>
        <div class="form-group">
            <select name="semester_type" id="semester_type" class="form-control">
                <option value="gasal" {{ request('semester_type', 'genap') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                <option value="genap" {{ request('semester_type', 'genap') == 'genap' ? 'selected' : '' }}>Genap</option>
            </select>
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

    // PERBAIKAN: Function untuk cek konflik dosen dengan mata kuliah sama di ruang berbeda
    function hasDosenSameSubjectConflict(dosenNidn, hari, startTime, endTime, currentMatkul, currentRuang) {
        const conflictingSchedules = existingJadwals.filter(jadwal => 
            jadwal.dosen === dosenNidn && 
            jadwal.hari === hari && 
            jadwal.matkul === currentMatkul &&
            jadwal.ruang !== currentRuang && // Ruang berbeda
            timeOverlaps(startTime, endTime, jadwal.start, jadwal.end)
        );
        
        console.log(`🔍 Checking same subject conflict for ${currentMatkul} by ${dosenNidn}:`);
        console.log(`   Found ${conflictingSchedules.length} conflicts in other rooms`);
        if (conflictingSchedules.length > 0) {
            console.log(`   Conflicting rooms: ${conflictingSchedules.map(j => j.ruang).join(', ')}`);
        }
        
        return conflictingSchedules.length > 0;
    }

    // PERBAIKAN: Function untuk cek slot ruang yang lebih ketat
    function checkRoomSlotAvailability(ruang, hari, startTime, endTime, dosenNidn, currentMatkul) {
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

        // PERBAIKAN: Cek apakah dosen sudah mengajar mata kuliah sama di ruang lain
        if (hasDosenSameSubjectConflict(dosenNidn, hari, startTime, endTime, currentMatkul, ruang)) {
            console.log(`❌ Lecturer already teaching same subject in another room at this time`);
            return false;
        }

        // Jika sudah mencapai atau melebihi kapasitas
        if (conflictingSchedules.length >= roomCapacity) {
            console.log(`❌ Room at/over capacity (${conflictingSchedules.length}/${roomCapacity})`);
            
            // Cek apakah semua jadwal yang ada adalah mata kuliah dan dosen yang sama (kelas paralel)
            const allSameSubjectAndLecturer = conflictingSchedules.every(jadwal => 
                jadwal.dosen === dosenNidn && jadwal.matkul === currentMatkul
            );
            
            if (!allSameSubjectAndLecturer) {
                console.log(`❌ Room full with different subjects/lecturers`);
                return false;
            }
            
            // PERBAIKAN: Bahkan untuk kelas paralel, jika ruang sudah penuh tidak boleh tambah lagi
            console.log(`❌ Room full - no more parallel classes allowed`);
            return false;
        }

        // Cek apakah ada mata kuliah/dosen lain di ruang ini di waktu yang sama
        const hasOtherSubjectsOrLecturers = conflictingSchedules.some(jadwal => 
            jadwal.dosen !== dosenNidn || jadwal.matkul !== currentMatkul
        );

        if (hasOtherSubjectsOrLecturers) {
            console.log(`❌ Room has other subjects/lecturers at this time`);
            return false;
        }

        console.log(`✅ Room has available slots (${conflictingSchedules.length}/${roomCapacity})`);
        return true;
    }

    // Helper function untuk info kelas paralel
    function getParallelClassInfo(ruang, hari, startTime, endTime, dosenNidn, currentMatkul) {
        const parallelClasses = existingJadwals
            .filter(jadwal => 
                jadwal.ruang === ruang && 
                jadwal.hari === hari && 
                jadwal.dosen === dosenNidn &&
                jadwal.matkul === currentMatkul &&
                timeOverlaps(startTime, endTime, jadwal.start, jadwal.end)
            )
            .map(jadwal => jadwal.kelas);

        if (parallelClasses.length > 0) {
            return `(Paralel: ${parallelClasses.join(', ')})`;
        }
        
        return null;
    }

    // Event listeners untuk setiap baris kelas
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

                // 3. PERBAIKAN: Cek konflik dosen mengajar mata kuliah sama di ruang lain
                if (hasDosenSameSubjectConflict(dosenNidn, hari, startTime, endTime, matkul, ruang)) {
                    console.log(`❌ Lecturer already teaching same subject in another room: ${timeSlot}`);
                    continue;
                }

                // 4. Cek slot ruang kelas
                if (!checkRoomSlotAvailability(ruang, hari, startTime, endTime, dosenNidn, matkul)) {
                    console.log(`❌ Room slot not available: ${timeSlot}`);
                    continue;
                }

                // 5. Jika semua pengecekan lolos
                console.log(`✅ Time slot available: ${timeSlot}`);
                hasAvailableSlots = true;
                
                const option = document.createElement('option');
                option.value = ALL_SLOTS[i];
                option.textContent = timeSlot;

                // Tambahkan info jika ini akan jadi kelas paralel
                const parallelInfo = getParallelClassInfo(ruang, hari, startTime, endTime, dosenNidn, matkul);
                if (parallelInfo) {
                    option.textContent += ` ${parallelInfo}`;
                }
                
                jamEl.appendChild(option);
            }

            // Jika tidak ada slot yang tersedia
            if (!hasAvailableSlots) {
                const roomCapacity = roomCapacities[ruang] || 1;
                
                // Hitung berapa banyak slot yang terpakai di hari ini untuk ruang ini
                const usedSlots = existingJadwals.filter(jadwal => 
                    jadwal.ruang === ruang && jadwal.hari === hari
                ).length;
                
                // Cek apakah ada konflik mata kuliah sama di ruang lain
                const sameSubjectInOtherRoom = existingJadwals.some(jadwal => 
                    jadwal.dosen === dosenNidn && 
                    jadwal.matkul === matkul && 
                    jadwal.hari === hari &&
                    jadwal.ruang !== ruang
                );

                let errorMessage = '';
                if (sameSubjectInOtherRoom) {
                    errorMessage = 'Dosen sudah mengajar mata kuliah ini di ruang lain pada hari ini';
                } else {
                    errorMessage = `Tidak ada waktu tersedia (Ruang penuh: ${usedSlots}/${roomCapacity} slot terpakai)`;
                }
                
                jamEl.innerHTML = `<option value="">${errorMessage}</option>`;
                console.log(`❌ No available time slots - ${errorMessage}`);
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