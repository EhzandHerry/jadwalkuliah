@extends('layouts.layout')

@section('title', 'Edit Waktu Ketersediaan')
{{-- PERBAIKAN: Menggunakan optional() untuk mencegah error jika user tidak ditemukan --}}
@section('header_title', 'Edit Waktu Ketersediaan Dosen ' . optional($available->user)->nama)

@section('content')
<div class="available-container">
    <div class="available-form">
        {{-- PERBAIKAN: Menggunakan optional() di sini juga --}}
        <h1>Edit Waktu Ketersediaan Dosen</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.available.update', $available->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="hari">Hari</label>
                <input type="text" id="hari" class="form-control" value="{{ $available->hari }}" disabled>
            </div>

            @php
                // Format waktu dari database untuk perbandingan di dropdown
                $startTime = \Carbon\Carbon::parse($available->waktu_mulai)->format('H:i');
                $endTime = \Carbon\Carbon::parse($available->waktu_selesai)->format('H:i');
            @endphp

            <div class="form-group">
                <label for="waktu_mulai">Waktu Mulai</label>
                <select name="waktu_mulai" id="waktu_mulai" required class="form-control">
                    <option value="">-- Pilih Jam Mulai --</option>
                    <optgroup label="Sesi 1">
                        <option value="07:00" {{ old('waktu_mulai', $startTime) == '07:00' ? 'selected' : '' }}>07:00</option>
                        <option value="07:50" {{ old('waktu_mulai', $startTime) == '07:50' ? 'selected' : '' }}>07:50</option>
                    </optgroup>
                    <optgroup label="Sesi 2">
                        <option value="08:50" {{ old('waktu_mulai', $startTime) == '08:50' ? 'selected' : '' }}>08:50</option>
                        <option value="09:40" {{ old('waktu_mulai', $startTime) == '09:40' ? 'selected' : '' }}>09:40</option>
                    </optgroup>
                    <optgroup label="Sesi 3">
                        <option value="10:40" {{ old('waktu_mulai', $startTime) == '10:40' ? 'selected' : '' }}>10:40</option>
                    </optgroup>
                    <optgroup label="Sesi 4">
                        <option value="12:10" {{ old('waktu_mulai', $startTime) == '12:10' ? 'selected' : '' }}>12:10</option>
                    </optgroup>
                    <optgroup label="Sesi 5">
                        <option value="13:20" {{ old('waktu_mulai', $startTime) == '13:20' ? 'selected' : '' }}>13:20</option>
                        <option value="14:10" {{ old('waktu_mulai', $startTime) == '14:10' ? 'selected' : '' }}>14:10</option>
                    </optgroup>
                    <optgroup label="Sesi 6">
                        <option value="15:30" {{ old('waktu_mulai', $startTime) == '15:30' ? 'selected' : '' }}>15:30</option>
                        <option value="16:20" {{ old('waktu_mulai', $startTime) == '16:20' ? 'selected' : '' }}>16:20</option>
                        <option value="17:10" {{ old('waktu_mulai', $startTime) == '17:10' ? 'selected' : '' }}>17:10</option>
                    </optgroup>
                    <optgroup label="Sesi 7">
                        <option value="18:30" {{ old('waktu_mulai', $startTime) == '18:30' ? 'selected' : '' }}>18:30</option>
                        <option value="19:20" {{ old('waktu_mulai', $startTime) == '19:20' ? 'selected' : '' }}>19:20</option>
                        <option value="20:10" {{ old('waktu_mulai', $startTime) == '20:10' ? 'selected' : '' }}>20:10</option>
                    </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label for="waktu_selesai">Waktu Selesai</label>
                <select name="waktu_selesai" id="waktu_selesai" required class="form-control">
                    {{-- Opsi akan di-generate oleh JavaScript --}}
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-action btn-update">Simpan</button>
            </div>
             <a href="{{ route('admin.available.manage', $available->id_dosen) }}" class="btn-secondary">
                Batal
            </a>
        </form>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/available/edit.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeSelect = document.getElementById('waktu_mulai');
    const endTimeSelect = document.getElementById('waktu_selesai');
    
    // Simpan nilai awal dari database
    const initialEndTime = '{{ old('waktu_selesai', $endTime) }}';

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
        
        endTimeSelect.innerHTML = ''; // Kosongkan dropdown

        if (!selectedStartTime) {
            endTimeSelect.disabled = true;
            const placeholder = document.createElement('option');
            placeholder.value = "";
            placeholder.textContent = "-- Pilih Jam Mulai Terlebih Dahulu --";
            endTimeSelect.appendChild(placeholder);
            return;
        }

        endTimeSelect.disabled = false;
        
        const defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.textContent = "-- Pilih Jam Selesai --";
        endTimeSelect.appendChild(defaultOption);

        endTimeData.forEach(groupData => {
            const validTimes = groupData.times.filter(time => time > selectedStartTime);
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
        
        // Set kembali nilai yang dipilih sebelumnya jika masih valid
        if (endTimeSelect.querySelector(`option[value="${initialEndTime}"]`)) {
            endTimeSelect.value = initialEndTime;
        }
    }

    startTimeSelect.addEventListener('change', updateEndTimeOptions);

    // Panggil fungsi saat halaman dimuat untuk mengisi dropdown berdasarkan nilai awal
    updateEndTimeOptions();
});
</script>
@endpush
