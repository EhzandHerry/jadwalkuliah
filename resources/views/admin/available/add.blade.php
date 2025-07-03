@extends('layouts.layout')

@section('title', 'Add Available Time')
@section('header_title', 'Manajemen Waktu Ketersediaan untuk ' . $dosen->name)

@section('content')
<div class="available-container">
    <div class="available-form">
        <h1>Tambah Waktu Ketersediaan untuk {{ $dosen->name }}</h1>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.dosen.storeAvailable', $dosen->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="hari">Hari</label>
                <select name="hari" id="hari" required class="form-control">
                    <option value="">-- Pilih Hari --</option>
                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                        <option value="{{ $hari }}"
                            {{ old('hari') == $hari ? 'selected':'' }}
                            @if(in_array($hari, $existingDays)) disabled @endif
                        >
                            {{ $hari }}
                            @if(in_array($hari, $existingDays))
                                (sudah diinput)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="waktu_mulai">Waktu Mulai</label>
                <select name="waktu_mulai" id="waktu_mulai" required class="form-control">
                    <option value="">-- Pilih Jam Mulai --</option>
                    <optgroup label="Sesi 1"> <option value="07:00">07:00</option> <option value="07:50">07:50</option> </optgroup>
                    <optgroup label="Sesi 2"> <option value="08:50">08:50</option> <option value="09:40">09:40</option> </optgroup>
                    <optgroup label="Sesi 3"> <option value="10:40">10:40</option> </optgroup>
                    <optgroup label="Sesi 4"> <option value="12:10">12:10</option> </optgroup>
                    <optgroup label="Sesi 5"> <option value="13:20">13:20</option> <option value="14:10">14:10</option> </optgroup>
                    <optgroup label="Sesi 6"> <option value="15:30">15:30</option> <option value="16:20">16:20</option> <option value="17:10">17:10</option> </optgroup>
                    <optgroup label="Sesi 7"> <option value="18:30">18:30</option> <option value="19:20">19:20</option> <option value="20:10">20:10</option> </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label for="waktu_selesai">Waktu Selesai</label>
                {{-- Opsi di sini akan dikontrol penuh oleh JavaScript --}}
                <select name="waktu_selesai" id="waktu_selesai" required class="form-control" disabled>
                    <option value="">-- Pilih Jam Mulai Terlebih Dahulu --</option>
                </select>
            </div>

            <div class="form-group d-flex">
                <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                <a href="{{ route('admin.available.manage', $dosen->id) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/available/add.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeSelect = document.getElementById('waktu_mulai');
    const endTimeSelect = document.getElementById('waktu_selesai');

    // Mendefinisikan semua kemungkinan waktu selesai di dalam JavaScript
    const endTimeData = [
        { label: 'Sesi 1', times: ['07:50', '08:40'] },
        { label: 'Sesi 2', times: ['09:40', '10:30'] },
        { label: 'Sesi 3', times: ['11:30'] },
        { label: 'Sesi 4', times: ['13:10'] },
        { label: 'Sesi 5', times: ['14:10', '15:00'] },
        { label: 'Sesi 6', times: ['16:20', '17:10', '18:00'] },
        { label: 'Sesi 7', times: ['19:20', '20:10', '21:00'] }
    ];

    function updateEndTimeOptions() {
        const selectedStartTime = startTimeSelect.value;
        
        // Simpan nilai yang dipilih sebelumnya (jika ada)
        const previouslySelectedEndTime = endTimeSelect.value;

        // Kosongkan dropdown
        endTimeSelect.innerHTML = '';

        if (!selectedStartTime) {
            endTimeSelect.disabled = true;
            const placeholder = document.createElement('option');
            placeholder.value = "";
            placeholder.textContent = "-- Pilih Jam Mulai Terlebih Dahulu --";
            endTimeSelect.appendChild(placeholder);
            return;
        }

        endTimeSelect.disabled = false;
        
        // Tambahkan kembali placeholder default
        const defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.textContent = "-- Pilih Jam Selesai --";
        endTimeSelect.appendChild(defaultOption);

        // Buat ulang dropdown waktu selesai dari data di JavaScript
        endTimeData.forEach(groupData => {
            // Saring waktu yang lebih besar dari waktu mulai
            const validTimes = groupData.times.filter(time => time > selectedStartTime);

            // Jika ada waktu yang valid di dalam grup ini, buat optgroup
            if (validTimes.length > 0) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = groupData.label;
                
                validTimes.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    optgroup.appendChild(option);
                });
                
                endTimeSelect.appendChild(optgroup);
            }
        });
        
        // Coba set kembali nilai yang dipilih sebelumnya jika masih ada di opsi baru
        if (endTimeSelect.querySelector(`option[value="${previouslySelectedEndTime}"]`)) {
            endTimeSelect.value = previouslySelectedEndTime;
        }
    }

    // Tambahkan event listener ke dropdown waktu mulai
    startTimeSelect.addEventListener('change', updateEndTimeOptions);

    // Panggil fungsi saat halaman pertama kali dimuat untuk mengatur status awal
    updateEndTimeOptions();
});
</script>
@endpush
