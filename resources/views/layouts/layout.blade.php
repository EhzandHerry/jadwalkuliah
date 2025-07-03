<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    @stack('css') </head>

<body>
    <header class="header">
        <button class="toggle-btn" id="toggle-btn">☰</button> <h3>@yield('header_title', 'Welcome to Admin Dashboard')</h3>
    </header>
    

    <div class="sidebar" id="sidebar">
    <ul>
        {{-- --- PERUBAHAN DI SINI --- --}}
        <li><a href="{{ route('admin.dashboard') }}">Home</a></li>
        {{-- --- AKHIR PERUBAHAN --- --}}
        <li><a href="{{ route('admin.dosen.index') }}">Manajemen Dosen</a></li>
        <li><a href="{{ route('admin.mata_kuliah.index') }}">Manajemen Mata Kuliah</a></li>
        <li><a href="{{ route('admin.ruang_kelas.index') }}">Manajemen Ruang Kelas</a></li>
        <li><a href="{{ route('admin.matakuliah_dosen.index') }}">Mata Kuliah & Dosen</a></li>
        <li><a href="{{ route('admin.jadwal.index') }}">Manajemen Jadwal</a></li>
        </ul>
    <div class="logout-container">
        {{-- ====================================================== --}}
        {{-- PERUBAHAN DI SINI: Menambahkan konfirmasi onsubmit --}}
        {{-- ====================================================== --}}
        <form action="/logout" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin logout?');">
            @csrf
            <button type="submit">Logout</button>
        </form>
        {{-- ====================================================== --}}
    </div>
</div>


    <div class="content" id="content">
        @yield('content')
    </div>

    <script>
        document.getElementById("toggle-btn").addEventListener("click", function() {
            var sidebar = document.getElementById("sidebar");
            var content = document.getElementById("content");

            sidebar.classList.toggle("collapsed");
            content.classList.toggle("expanded");
        });
    </script>
    @stack('scripts')
</body>
</html>