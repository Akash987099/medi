<aside class="sidebar" id="main-navigation" aria-label="Main navigation">
  <a href="{{ route('admin.index') }}" class="brand" aria-label="MediCare home">
    <span class="brand-mark"><i class="fas fa-plus"></i></span>
    <span class="brand-name">Medi<span>Care</span></span>
  </a>

  <div class="sidebar-scroll">
    <div class="workspace-label"><span class="workspace-dot"></span> Riverside Clinic</div>

    <p class="nav-label">Workspace</p>
    <nav class="sidebar-nav">
      <a href="{{ route('admin.index') }}" class="sidebar-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i><span>Dashboard</span>
      </a>
      <div class="sidebar-group">
        <button class="sidebar-link sidebar-toggle {{ request()->routeIs('category.*', 'service.*') ? 'active' : '' }}" type="button" aria-expanded="{{ request()->routeIs('category.*', 'service.*') ? 'true' : 'false' }}" aria-controls="masters-submenu">
          <i class="fas fa-layer-group"></i><span>Masters</span><i class="fas fa-chevron-down submenu-chevron"></i>
        </button>
        <div class="sidebar-submenu" id="masters-submenu" @if (!request()->routeIs('category.*', 'service.*')) hidden @endif>
          <a href="{{ route('category.index') }}" class="sidebar-submenu-link {{ request()->routeIs('category.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Category</a>
          <a href="{{ route('service.index') }}" class="sidebar-submenu-link {{ request()->routeIs('service.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Services</a>
        </div>
      </div>
      <div class="sidebar-group">
        <button class="sidebar-link sidebar-toggle {{ request()->routeIs('doctor.*', 'hospital.*') ? 'active' : '' }}" type="button" aria-expanded="{{ request()->routeIs('doctor.*', 'hospital.*') ? 'true' : 'false' }}" aria-controls="users-submenu">
          <i class="fas fa-users"></i><span>Users</span><i class="fas fa-chevron-down submenu-chevron"></i>
        </button>
        <div class="sidebar-submenu" id="users-submenu" @if (!request()->routeIs('doctor.*', 'hospital.*')) hidden @endif>
          <a href="{{ route('hospital.index') }}" class="sidebar-submenu-link {{ request()->routeIs('hospital.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Hospital</a>
          <a href="{{ route('doctor.index') }}" class="sidebar-submenu-link {{ request()->routeIs('doctor.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Doctor</a>
        </div>
      </div>
      <a href="#appointments" class="sidebar-link">
        <i class="far fa-calendar-check"></i><span>Appointments</span><span class="nav-count">08</span>
      </a>
      <a href="#patient-flow" class="sidebar-link">
        <i class="fas fa-user-friends"></i><span>Patients</span>
      </a>
      <a href="#services" class="sidebar-link">
        <i class="fas fa-heartbeat"></i><span>Care services</span>
      </a>
      <a href="#care-team" class="sidebar-link">
        <i class="fas fa-user-md"></i><span>Care team</span>
      </a>
    </nav>

    <div class="sidebar-spacer"></div>
    <div class="welcome-note">
      <i class="far fa-sun"></i>
      <p>“Care is remembered in the little things.”</p>
    </div>
    <div class="sidebar-bottom">
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="sidebar-link logout" type="submit"><i class="fas fa-sign-out-alt"></i><span>Sign out</span></button>
      </form>
    </div>
  </div>
</aside>
<button class="sidebar-backdrop" type="button" aria-label="Close navigation"></button>
