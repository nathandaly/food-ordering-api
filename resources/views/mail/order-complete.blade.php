<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body>
        <h1>Hello, {{ $email_data['user']->name }}</h1>
    </body>
</html>
