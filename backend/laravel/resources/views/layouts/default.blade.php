<!DOCTYPE html>
<html>
    <head>
        @include('components.head')
        @yield('meta')
        @yield('style')
        @yield('script_head')
    </head>
    <body>
        <noscript>※当ウェブサイトを快適に閲覧して頂くためjavascriptを有効にしてください</noscript>
        <div class="app-layout">
            @include('components.header')
            @yield('content')
            @include('components.footer')
        </div>
        @include('components.offcanvas')
        @yield('script_body')
    </body>
</html>
