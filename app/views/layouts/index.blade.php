<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=5">
    <meta name="format-detection" content="telephone=no">
    <title>Phalcon Sample</title>

    @yield('css')
    <link rel="stylesheet" href="/css/common.min.css">
</head>
<body>
<div class="wrap">
    @yield('body')
</div>
@if (!empty($flashMessage))
<script>alert('{{$flashMessage}}')</script>
@endif
</body>
</html>