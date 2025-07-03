    @extends('layouts.layout')

    @section('title', 'Preview Matrix Jadwal')
    @section('header_title', 'Preview Matrix Jadwal')

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/admin/jadwal/matrix_preview.css') }}">
    @endpush

    @section('content')
    <div class="container-fluid page-content-wrapper">
        
        {{-- Baris Judul dan Tombol Aksi --}}
        <div class="d-flex justify-content-between align-items-center">
            {{-- Perubahan: Menambahkan class .page-main-title dan menghapus .mb-0 --}}
            <h1 class="page-main-title">Preview Matrix Jadwal</h1>
            <div class="preview-actions">
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Kembali</a>
                <a href="{{ route('admin.jadwal.exportMatrix') }}" class="btn btn-success">Download Jadwal</a>
            </div>
        </div>

        {{-- Tabel Jadwal --}}
        <div class="matrix-wrapper">
            <table class="matrix-table">
                <thead>
                    <tr class="title-row">
                        <th colspan="{{ 2 + count($rooms) }}">
                            JADWAL PERKULIAHAN SEMESTER GENAP PRODI TEKNOLOGI INFORMASI UMY 2024/2025
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($days as $hari)
                        @if(isset($dailyData[$hari]))
                            {{-- Header untuk setiap hari (2 baris) --}}
                            <tr>
                                <td rowspan="2" class="header-sesi-jam"><b>SESI</b></td>
                                <td rowspan="2" class="header-sesi-jam"><b>JAM</b></td>
                                <td colspan="{{ count($rooms) }}" class="header-day">{{ strtoupper($hari) }}</td>
                            </tr>
                            <tr>
                                @foreach($rooms as $ruang)
                                    <td class="header-room"><b>{{ $ruang }}</b></td>
                                @endforeach
                            </tr>

                            {{-- Loop untuk data sesi dan matakuliah --}}
                            @foreach($sessionRanges as $sesi => $slots)
                                @if(isset($dailyData[$hari][$sesi]))
                                    @foreach($slots as $slotIdx => $jam)
                                        <tr>
                                            @if($slotIdx === 0)
                                                <td rowspan="{{ count($slots) }}">Sesi {{ $sesi }}</td>
                                            @endif
                                            <td>{{ $jam }}</td>
                                            @foreach($rooms as $ruang)
                                                @php
                                                    $jadwals = $dailyData[$hari][$sesi][$jam][$ruang] ?? [];
                                                @endphp
                                                <td class="matkul-cell">
                                                    @foreach($jadwals as $jadwal)
                                                        <div class="matkul-item semester-{{ $jadwal['semester'] ?? '' }}">
                                                            {!! nl2br(e($jadwal['text'])) !!}
                                                        </div>
                                                    @endforeach
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach

                                    {{-- Baris Pergantian Sesi --}}
                                    @if(isset($breakSlots[$sesi]) && $sesi < $dailyMaxSessions[$hari])
                                        <tr class="break-row">
                                            <td></td>
                                            <td class="break-time">{{ $breakSlots[$sesi]['time'] }}</td>
                                            <td colspan="{{ count($rooms) }}" class="break-text">{{ $breakSlots[$sesi]['text'] }}</td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($rooms) }}" class="text-center py-5">
                                <h4>Tidak ada data jadwal untuk ditampilkan.</h4>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endsection