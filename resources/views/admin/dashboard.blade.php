<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Boshqaruv — Admin</title>
</head>
<body>
    <main>
        <h1>Boshqaruv paneli</h1>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Chiqish</button>
        </form>
    </main>
</body>
</html>
