
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form method="POST" action="/register">
        @csrf
        <input type="text" name="name" placeholder="Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="password" name="password_confirmation" placeholder="Re-enter Password" required><br><br>
        <button type="submit">Register</button>
    </form>
    @if(session('error'))
        <p style="color:red;">{{ session('error') }}</p>
    @endif
</body>
</html>
