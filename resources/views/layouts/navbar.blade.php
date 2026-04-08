<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">

  <!-- Mobile Menu Toggle -->
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center w-100" id="navbar-collapse">

    <!-- Left Section -->
    <div class="d-flex align-items-center">
      <span class="fw-bold">Product by Wijeynewspaper WebDepartment</span>
    </div>

    <!-- Right Section -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- Username -->
      <li class="nav-item me-3">
        <span class="fw-semibold">
          {{ session('user.name') ?? 'User' }}
        </span>
      </li>

      <!-- Avatar -->
      <li class="nav-item me-3">
        <div class="avatar avatar-online">
          <img src="/assets/img/avatars/1.png"
            alt="User Avatar"
            class="w-px-40 h-auto rounded-circle" />
        </div>
      </li>

      <!-- Logout -->
      <li class="nav-item">
        <form id="logoutForm" action="{{ url('/logout') }}" method="POST" style="display:inline;">
          @csrf
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmLogout()">
            <i class="bx bx-power-off me-1"></i> Logout
          </button>
        </form>
      </li>

    </ul>
  </div>
</nav>

<!-- ✅ Logout Confirmation Script -->
<script>
  function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
      document.getElementById('logoutForm').submit();
    }
  }
</script>