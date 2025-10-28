<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ url('/dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        <!-- Custom Logo -->
        <br><img src="{{ asset('assets/img/favicon/logo.png') }}" alt="Logo" class="w-px-150 h-auto" /><br><br>
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Pages -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Pages</span>
    </li>

    <!-- Dashboard -->
    <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
      <a href="{{ url('/dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>

    <!-- Account Settings (dropdown) -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-dock-top"></i>
        <div data-i18n="Account Settings">Hitad Print</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div data-i18n="Account">Print Paid</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div data-i18n="Notifications">Print Unpaid</div>
          </a>
        </li>
        <li class="menu-item {{ request()->is('advertisements') ? 'active' : '' }}">
          <a href="{{ url('/advertisements') }}" class="menu-link">
            <div data-i18n="Connections">Print AllAds</div>
          </a>
        </li>
      </ul>
    </li>


    <!-- User Interface (dropdown) -->
    <li class="menu-item">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-layout"></i>
        <div data-i18n="HitAd Online">HitAd Online</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="https://www.hitad.lk/" class="menu-link" target="_blank">
            <div data-i18n="Hitad Web">Hitad Web</div>
          </a>
        </li>

        <li class="menu-item">
          <a href="http://betaadmin.hitad.lk/home" class="menu-link" target="_blank">
            <div data-i18n="Hitad Web Admin">Hitad Web Admin</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="http://betaadmin.hitad.lk/view-pendingads" class="menu-link" target="_blank">
            <div data-i18n="Hitad Web Ads">Hitad Web Ads</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="http://betaadmin.hitad.lk/new-ads" class="menu-link" target="_blank">
            <div data-i18n="Hitad Web New Ad">Hitad Web New Ad</div>
          </a>
        </li>

      </ul>
    </li>

    <li class="menu-item {{ request()->is('categories') ? 'active' : '' }}">
      <a href="{{ url('/categories') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-category"></i>
        <div data-i18n="Categories">Ad Categories</div>
      </a>
    </li>

    <!-- Tables -->
    <li class="menu-item {{ request()->is('adtypes') ? 'active' : '' }}">
      <a href="{{ url('/adtypes') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file"></i>
        <div data-i18n="Ad Type Management">Ad Type </div>
      </a>
    </li>


    <li class="menu-item {{ request()->is('adsizes') ? 'active' : '' }}">
      <a href="{{ url('/adsizes') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-table"></i>
        <div data-i18n="Ad Size Management">Ad Size </div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('adcriterias') ? 'active' : '' }}">
      <a href="{{ url('/adcriterias') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-check-square"></i>
        <div data-i18n="Ad Criteria Management">Ad Criteria </div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('adcriteria-options') ? 'active' : '' }}">
      <a href="{{ url('/adcriteria-options') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-list-check"></i>
        <div data-i18n="Criteria Options">Ad Criteria Options</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('districts') ? 'active' : '' }}">
      <a href="{{ url('/districts') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-map"></i>
        <div data-i18n="Districts">Districts</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('cities') ? 'active' : '' }}">
      <a href="{{ url('/cities') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-buildings"></i>
        <div data-i18n="Cities">Cities</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('users') ? 'active' : '' }}">
      <a href="{{ url('/users') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div data-i18n="Users">Users</div>
      </a>
    </li>

  </ul>
</aside>