<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Welcome Back!</h2>
            <p>Please login to your account</p>

            {{-- ====================================================== --}}
            {{-- PENAMBAHAN DI SINI: Blok untuk Menampilkan Error --}}
            {{-- ====================================================== --}}
            @if ($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{-- ====================================================== --}}

            <form action="/login" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Enter your email address" required value="{{ old('email') }}">
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
