<!doctype html>
<html>
<head>
    @include('includes.head')
</head>
<body class="flex justify-center h-screen p-3">
<div id="app" class="w-[800px] mt-6">
    <div class="content">
        @yield('content')
    </div>
    <footer class="row">
        @include('includes.footer')
    </footer>
</div>
</body>
</html>
