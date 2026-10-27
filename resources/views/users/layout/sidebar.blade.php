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
      @if ($user->role === 1)
      <a href="{{ route('user.doctors.index') }}" class="sidebar-link {{ request()->routeIs('user.doctors.*') ? 'active' : '' }}">
        <i class="fas fa-user-md"></i><span>Doctors</span>
      </a>
      @endif
      <a href="{{ route('user.reviews.index') }}" class="sidebar-link {{ request()->routeIs('user.reviews.*') ? 'active' : '' }}">
        <i class="far fa-star"></i><span>Reviews</span>
      </a>
      <a href="{{ route('user.faqs.index') }}" class="sidebar-link {{ request()->routeIs('user.faqs.*') ? 'active' : '' }}">
        <i class="far fa-question-circle"></i><span>FAQs</span>
      </a>
      <a href="{{ route('user.gallery.index') }}" class="sidebar-link {{ request()->routeIs('user.gallery.*') ? 'active' : '' }}">
        <i class="far fa-images"></i><span>Gallery</span>
      </a>
      <a href="{{ route('user.videos.index') }}" class="sidebar-link {{ request()->routeIs('user.videos.*') ? 'active' : '' }}">
        <i class="fas fa-video"></i><span>Videos</span>
      </a>
      <a href="{{ route('user.news.index') }}" class="sidebar-link {{ request()->routeIs('user.news.*') ? 'active' : '' }}">
        <i class="far fa-newspaper"></i><span>News</span>
      </a>
      <a href="{{ route('user.articles.index') }}" class="sidebar-link {{ request()->routeIs('user.articles.*') ? 'active' : '' }}">
        <i class="far fa-file-alt"></i><span>Articles</span>
      </a>
      <a href="{{ route('user.blogs.index') }}" class="sidebar-link {{ request()->routeIs('user.blogs.*') ? 'active' : '' }}">
        <i class="fas fa-blog"></i><span>Blogs</span>
      </a>
      <a href="{{ route('user.services.index') }}" class="sidebar-link {{ request()->routeIs('user.services.*') ? 'active' : '' }}">
        <i class="fas fa-hand-holding-medical"></i><span>Services</span>
      </a>
      <a href="{{ route('user.packages.index') }}" class="sidebar-link {{ request()->routeIs('user.packages.*') ? 'active' : '' }}">
        <i class="fas fa-box-open"></i><span>Packages</span>
      </a>
      <a href="{{ route('user.slider.index') }}" class="sidebar-link {{ request()->routeIs('user.slider.*') ? 'active' : '' }}">
        <i class="far fa-images"></i><span>Slider</span>
      </a>
      <a href="{{ route('user.cms-pages.index') }}" class="sidebar-link {{ request()->routeIs('user.cms-pages.*') ? 'active' : '' }}">
        <i class="far fa-file-alt"></i><span>CMS Pages</span>
      </a>
      <a href="{{ route('user.about-us.index') }}" class="sidebar-link {{ request()->routeIs('user.about-us.*') ? 'active' : '' }}">
        <i class="fas fa-hospital"></i><span>About Us</span>
      </a>
      <a href="{{ route('user.smtp.index') }}" class="sidebar-link {{ request()->routeIs('user.smtp.*') ? 'active' : '' }}">
        <i class="fas fa-envelope"></i><span>SMTP Setup</span>
      </a>
      <a href="{{ route('user.social-media.index') }}" class="sidebar-link {{ request()->routeIs('user.social-media.*') ? 'active' : '' }}">
        <i class="fas fa-share-alt"></i><span>Social Media</span>
      </a>
      <a href="{{ route('user.website-settings.index') }}" class="sidebar-link {{ request()->routeIs('user.website-settings.*') ? 'active' : '' }}">
        <i class="fas fa-globe"></i><span>Website Settings</span>
      </a>
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
