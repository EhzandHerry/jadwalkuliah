{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.layout')

@section('title', 'Dashboard Admin')

@section('header_title', 'Jadwal Kuliah Hari Ini: ' . $hariIni)

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Jadwal Kuliah Hari Ini: {{ $hariIni }}</h2>

        @if (session('info'))
            <div class="alert alert-info" role="alert">
                {{ session('info') }}
            </div>
        @endif

        @if ($jadwalHariIni->isEmpty())
            <div class="alert alert-info" role="alert">
                Tidak ada jadwal kuliah hari ini.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>No.</th>
                            <th>Mata Kuliah</th>
                            <th>Kelas</th>
                            <th>Dosen Pengampu</th>
                            <th>Ruang Kelas</th>
                            <th>Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwalHariIni as $index => $jadwal)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $jadwal->mataKuliah->nama_matkul ?? 'N/A' }}</td>
                                <td>{{ $jadwal->kelas ?? 'N/A' }}</td> {{-- UBAH BAGIAN INI --}}
                                <td>{{ $jadwal->dosen->nama ?? 'N/A' }}</td>
                                <td>{{ $jadwal->ruangKelas->nama_ruangan ?? 'N/A' }}</td>
                                <td>
                                    <?php
                                        $jamParts = explode(' - ', $jadwal->jam);
                                        if (count($jamParts) === 2) {
                                            $jamMulai = \Carbon\Carbon::parse($jamParts[0])->format('H:i');
                                            $jamSelesai = \Carbon\Carbon::parse($jamParts[1])->format('H:i');
                                            echo $jamMulai . ' - ' . $jamSelesai;
                                        } else {
                                            echo $jadwal->jam;
                                        }
                                    ?>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush