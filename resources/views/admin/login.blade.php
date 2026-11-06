<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Kirish — Admin</title>
</head>
<body>
    <main>
        <h1>Admin kirish</h1>
        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <label for="password">Parol</label>
            <input id="password" type="password" name="password" required>
            <label><input type="checkbox" name="remember" value="1"> Eslab qol</label>
            @foreach ($errors->all() as $error)
                <p role="alert">{{ $error }}</p>
            @endforeach
            <button type="submit">Kirish</button>
        </form>
    </main>
</body>
</html>
