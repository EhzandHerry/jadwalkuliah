<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
</head>
<body>
    <header>
        <h1>Selamat datang, {{ Auth::user()->name }}!</h1>
        <!-- Logout Button -->
        <form action="{{ route('logout') }}" method="POST" id="logout-form">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <h2>Daftar Mata Kuliah aAnda:</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Kode Mata Kuliah</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Kelas</th>
                <th>Ruang Kelas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelas as $kelasItem)
            <tr>
                <td>{{ $kelasItem->mataKuliah->kode_matkul }}</td>
                <td>{{ $kelasItem->mataKuliah->nama_matkul }}</td>
                <td>{{ $kelasItem->mataKuliah->sks }}</td>
                <td>{{ $kelasItem->kelas }}</td>
                <td>
                    @if($kelasItem->ruangKelas)
                        {{ $kelasItem->ruangKelas->kode_ruangan }}
                    @else
                        Data Ruang Kelas Tidak Tersedia
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">Belum ada mata kuliah terdaftar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
