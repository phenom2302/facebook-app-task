<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.head')
</head>
<body>
    <div class="container">
        @include('layouts.header')

        @if(session('flash_notice'))
            <div class="alert alert-{{ session('flash_notice_status') }}" role="alert">
                {{ session('flash_notice') }}
            </div>
        @endif

        @yield('body')
        @include('layouts.footer')
    </div>
    @yield('scripts')
</body>
</html>