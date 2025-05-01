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
            <form action="/login" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Enter your email address" required>
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
