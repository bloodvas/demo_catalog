<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/js/app.js')  <!-- Подключаем Vue bundle -->
</head>
<body>
    <div id="app">
        @inertia  <!-- Inertia вставит компонент здесь -->
    </div>
</body>
</html>
