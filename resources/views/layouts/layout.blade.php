<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    @stack('css') <!-- This line will include the additional page-specific styles -->
</head>

<body>
    <!-- Header -->
    <header class="header">
        <button class="toggle-btn" id="toggle-btn">☰</button> <!-- Sidebar Toggle Button -->
        <h3>@yield('header_title', 'Welcome to Admin Dashboard')</h3>
    </header>
    
    <!-- Sidebar -->
   <!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <ul>
        <li><a href="{{ route('admin.dosen.index') }}">Manajemen Dosen</a></li>
        <li><a href="{{ route('admin.mata_kuliah.index') }}">Manajemen Matakuliah</a></li>
        <li><a href="{{ route('admin.ruang_kelas.index') }}">Manajemen Ruang Kelas</a></li>
        <li><a href="{{ route('admin.matakuliah_dosen.index') }}">Matakuliah & Dosen</a></li>
        <li><a href="{{ route('admin.jadwal.index') }}">Manajemen Jadwal</a></li>
        <!-- <li><a href="{{ route('admin.available.dashboard') }}">Available Time Dashboard</a></li> -->
    </ul>
    <div class="logout-container">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</div>


    <!-- Content section -->
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
