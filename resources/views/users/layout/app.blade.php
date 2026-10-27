@include('layout.header')
<div class="app-shell">
  @include('users.layout.sidebar')
  @yield('content')
</div>
@include('layout.footer')
