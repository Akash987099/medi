<aside class="sidebar" id="main-navigation" aria-label="Care partner navigation">
  <a href="{{ route('user.index') }}" class="brand" aria-label="MediCare workspace home">
    <span class="brand-mark"><i class="fas fa-plus"></i></span>
    <span class="brand-name">Medi<span>Care</span></span>
  </a>

  <div class="sidebar-scroll">
    <div class="workspace-label"><span class="workspace-dot"></span> {{ $user->role === 1 ? 'Hospital workspace' : 'Doctor workspace' }}</div>

    <p class="nav-label">Workspace</p>
    <nav class="sidebar-nav">
      <a href="{{ route('user.index') }}" class="sidebar-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
        <i class="fas fa-th-large"></i><span>Dashboard</span>
      </a>
      <a href="{{ route('user.ai-studio.index') }}" class="sidebar-link ai-agent-link {{ request()->routeIs('user.ai-studio.*') ? 'active' : '' }}">
        <span class="ai-agent-icon"><i class="fas fa-robot"></i></span><span class="ai-agent-copy"><strong>AI Agent</strong><small>Chat &amp; image tools</small></span><span class="nav-count">AI</span>
      </a>
      @php($practiceOpen = request()->routeIs('user.doctors.*','user.patients.*','user.staff.*','user.roles.*','user.appointments.*','user.reviews.*'))
      <button class="sidebar-link sidebar-toggle {{ $practiceOpen ? 'active' : '' }}" type="button" aria-expanded="{{ $practiceOpen ? 'true' : 'false' }}" aria-controls="practice-menu"><i class="fas fa-briefcase-medical"></i><span>Practice Management</span><i class="fas fa-chevron-down submenu-chevron"></i></button>
      <div class="sidebar-submenu" id="practice-menu" @unless($practiceOpen) hidden @endunless>
        @if ($user->role === 1)<a href="{{ route('user.doctors.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.doctors.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Doctors</a>@endif
        @if ($user->role === 2)<a href="{{ route('user.patients.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.patients.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Patients</a>@endif
        <a href="{{ route('user.appointments.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.appointments.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Appointments</a>
        <a href="{{ route('user.staff.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.staff.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Staff</a>
        <a href="{{ route('user.roles.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.roles.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Roles</a>
        <a href="{{ route('user.reviews.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.reviews.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Reviews</a>
      </div>
      @php($contentOpen = request()->routeIs('user.services.*','user.packages.*','user.books.*','user.gallery.*','user.videos.*','user.news.*','user.articles.*','user.blogs.*','user.slider.*','user.cms-pages.*','user.about-us.*','user.faqs.*','user.social-media.*','user.website-settings.*'))
      <button class="sidebar-link sidebar-toggle {{ $contentOpen ? 'active' : '' }}" type="button" aria-expanded="{{ $contentOpen ? 'true' : 'false' }}" aria-controls="content-menu"><i class="fas fa-layer-group"></i><span>Content &amp; Website</span><i class="fas fa-chevron-down submenu-chevron"></i></button>
      <div class="sidebar-submenu" id="content-menu" @unless($contentOpen) hidden @endunless>
        <a href="{{ route('user.services.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.services.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Services</a><a href="{{ route('user.packages.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.packages.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Packages</a><a href="{{ route('user.books.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.books.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Books Catalog</a><a href="{{ route('user.gallery.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.gallery.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Gallery</a><a href="{{ route('user.videos.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.videos.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Videos</a><a href="{{ route('user.news.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.news.*') ? 'active' : '' }}"><span class="submenu-dot"></span>News</a><a href="{{ route('user.articles.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.articles.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Articles</a><a href="{{ route('user.blogs.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.blogs.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Blogs</a><a href="{{ route('user.slider.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.slider.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Slider</a><a href="{{ route('user.cms-pages.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.cms-pages.*') ? 'active' : '' }}"><span class="submenu-dot"></span>CMS Pages</a><a href="{{ route('user.about-us.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.about-us.*') ? 'active' : '' }}"><span class="submenu-dot"></span>About Us</a><a href="{{ route('user.faqs.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.faqs.*') ? 'active' : '' }}"><span class="submenu-dot"></span>FAQs</a><a href="{{ route('user.social-media.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.social-media.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Social Media</a><a href="{{ route('user.website-settings.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.website-settings.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Website Settings</a>
      </div>
      <button class="sidebar-link sidebar-toggle {{ request()->routeIs('user.smtp.*') || request()->routeIs('user.email.*') ? 'active' : '' }}" type="button" aria-expanded="{{ request()->routeIs('user.smtp.*') || request()->routeIs('user.email.*') ? 'true' : 'false' }}" aria-controls="email-management-menu">
        <i class="fas fa-envelope-open-text"></i><span>Email Management</span><i class="fas fa-chevron-down submenu-chevron"></i>
      </button>
      <div class="sidebar-submenu" id="email-management-menu" @unless(request()->routeIs('user.smtp.*') || request()->routeIs('user.email.*')) hidden @endunless>
        <a href="{{ route('user.smtp.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.smtp.*') ? 'active' : '' }}"><span class="submenu-dot"></span>SMTP Setup</a>
        <a href="{{ route('user.email.templates.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.email.templates.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Email Templates</a>
        <a href="{{ route('user.email.send.index') }}" class="sidebar-submenu-link {{ request()->routeIs('user.email.send.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Send Email</a>
      </div>
      <a href="{{ route('user.color-settings.index') }}" class="sidebar-link {{ request()->routeIs('user.color-settings.*') ? 'active' : '' }}"><i class="fas fa-palette"></i><span>Color Settings</span></a>
      @if (request()->routeIs('user.index'))
      <a href="#profile-heading" class="sidebar-link">
        <i class="far fa-id-card"></i><span>Account details</span>
      </a>
      @endif
    </nav>

    <div class="sidebar-spacer"></div>
    <div class="welcome-note">
      <i class="far fa-sun"></i>
      <p>“Care is remembered in the little things.”</p>
    </div>
    <div class="sidebar-bottom">
      <form action="{{ route('user.logout') }}" method="POST">
        @csrf
        <button class="sidebar-link logout" type="submit"><i class="fas fa-sign-out-alt"></i><span>Sign out</span></button>
      </form>
    </div>
  </div>
</aside>
<button class="sidebar-backdrop" type="button" aria-label="Close navigation"></button>
