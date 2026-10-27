@include('layout.header')
<div class="app-shell">
  @include('layout.sidebar')
  @yield('content')
</div>
@include('layout.footer')
