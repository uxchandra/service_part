<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/png">
  <title>StepServicePart</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

  <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
  
  
  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />


  <!-- Datatable Jquery -->
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.4.1/css/dataTables.dateTime.min.css">
  <!-- Start GA -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-94034622-3');
  </script>

  @stack('meta')
<!-- /END GA --></head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg bg-light"></div>
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3"> 
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars text-dark"></i></a></li>      
          </ul>
        </form>
        <ul class="navbar-nav navbar-right">
          <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <img alt="image" src="{{ asset('assets/img/avatar/image.png') }}" class="rounded-circle mr-1">
            <div class="d-inline-block text-dark">Hi, {{ auth()->user()->name }}</div></a>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item" href="{{ route('logout') }}"
                  onclick="event.preventDefault();
                      // Hentikan polling Livewire jika ada
                      if (typeof Livewire !== 'undefined') {
                          Livewire.dispatch('stopPolling');
                      }
                      Swal.fire({
                          title: 'Konfirmasi Keluar',
                          text: 'Apakah Anda yakin ingin keluar?',
                          icon: 'warning',
                          showCancelButton: true,
                          confirmButtonColor: '#3085d6',
                          cancelButtonColor: '#d33',
                          confirmButtonText: 'Ya, Keluar!'
                      }).then((result) => {
                          if (result.isConfirmed) {
                              document.getElementById('logout-form').submit();
                          }
                      });">
                  <i class="fas fa-sign-out-alt"></i> {{ __('Keluar') }}
              </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                  </a>
            </div>
          </li>
        </ul>
      </nav>

      <div class="main-sidebar sidebar-style-1 bg-dark">
        <aside id="sidebar-wrapper">
      
          <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo STEP" style="height: 35px; width: auto; margin-right: 5px; margin-top: 20px; margin-bottom: 25px">
            <a href="/" class="text-dark" style="text-transform: none; font-size: 18px;">ServicePart</a>
          </div>
      
          <ul class="sidebar-menu">
               <li class="sidebar-item mt-4">
                   <a class="nav-link  {{ Request::is('dashboard') || Request::is('dashboard') ? 'active' : '' }}" href="/dashboard">
                       <i class="fas fa-fire "></i> <span class="align-middle">Dashboard</span>
                   </a>
               </li>  
               
                <li><a class="nav-link {{ Request::is('barang') ? 'active' : '' }}" href="/barang"><i class="fas fa-thin fa-cubes"></i><span>Data Barang</span></a></li>
                <li><a class="nav-link {{ Request::is('barang-masuk') ? 'active' : '' }}" href="/barang-masuk"><i class="fas fa-arrow-up"></i> <span>Posting</span></a></li>
                <li><a class="nav-link {{ Request::is('orders') ? 'active' : '' }}" href="/orders"><i class="fas fa-file-alt"></i> <span>Orders</span></a></li>
                <li><a class="nav-link {{ Request::is('pulling') ? 'active' : '' }}" href="/pulling"><i class="fas fa-arrow-down"></i> <span>Pulling</span></a></li>
                <li><a class="nav-link {{ Request::is('packing') ? 'active' : '' }}" href="/packing"><i class="fas fa-box"></i> <span>Isp Packing</span></a></li>
            </ul>
      
        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
            @yield('content')
          <div class="section-body">
          </div>
        </section>
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          Copyright STEP &copy; 2025
        </div>
        <div class="footer-right">
          
        </div>
      </footer>
    </div>
  </div>


  
  <!-- General JS Scripts -->
  <script src="{{ asset('assets/modules/popper.js') }}"></script>
  <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
  <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
  <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
  <script src="{{ asset('assets/js/stisla.js') }}"></script>

  <!-- JS Libraies -->
  
  <!-- Select2 Jquery -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>

  <!-- Page Specific JS File -->
  
  <!-- Template JS File -->
  <script src="{{ asset('assets/js/scripts.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  <!-- Datatables Jquery -->
  <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <!-- Day Js Format -->
  <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

  @stack('scripts')

  <script>
    $(document).ready(function() {
        $('[data-toggle="sidebar"]').on('click', function() {
            // Close all dropdown menus when sidebar is toggled
            $('.sidebar-menu .dropdown-menu').slideUp(300).removeClass('show');
            $('.sidebar-menu .has-dropdown').removeClass('expanded');
            localStorage.removeItem('openDropdown');
        });

        var sidebar = $('.main-sidebar');

        // Cek apakah ada posisi scroll yang tersimpan
        if (localStorage.getItem('sidebarScroll')) {
            sidebar.scrollTop(localStorage.getItem('sidebarScroll'));
        }

        // Simpan posisi scroll saat sebelum berpindah halaman
        sidebar.on('scroll', function() {
            localStorage.setItem('sidebarScroll', sidebar.scrollTop());
        });

        // Hentikan event Stisla untuk dropdown
        $('.sidebar-menu .has-dropdown').off('click.stisla').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $this = $(this);
            var $dropdownMenu = $this.next('.dropdown-menu');
            var $dropdown = $this.closest('.dropdown');
            var dropdownId = $dropdown.attr('id');
            var isOpen = $dropdownMenu.hasClass('show');

            // Tutup semua dropdown lain
            $('.sidebar-menu .dropdown-menu').not($dropdownMenu).slideUp(300).removeClass('show');
            $('.sidebar-menu .has-dropdown').not($this).removeClass('active');

            // Toggle dropdown yang diklik - MODIFIED: Don't add 'active' class here
            if (!isOpen) {
                $this.addClass('expanded'); // Use 'expanded' class instead of 'active'
                $dropdownMenu.slideDown(300).addClass('show');
                localStorage.setItem('openDropdown', dropdownId);
            } else {
                $this.removeClass('expanded'); // Remove 'expanded' instead of 'active'
                $dropdownMenu.slideUp(300).removeClass('show');
                localStorage.removeItem('openDropdown');
            }
        });

        // Tutup dropdown saat klik di luar
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sidebar-menu .dropdown').length) {
                $('.sidebar-menu .dropdown-menu').slideUp(300).removeClass('show');
                $('.sidebar-menu .has-dropdown').removeClass('expanded'); // Change from 'active' to 'expanded'
                localStorage.removeItem('openDropdown');
            }
        });

        // Buka dropdown yang tersimpan di localStorage
        var openDropdownId = localStorage.getItem('openDropdown');
        if (openDropdownId) {
            var $dropdown = $('#' + openDropdownId);
            var $header = $dropdown.find('> .nav-link.has-dropdown');
            var $dropdownMenu = $dropdown.find('.dropdown-menu');
            $header.addClass('expanded'); // Change from 'active' to 'expanded'
            $dropdownMenu.slideDown(300).addClass('show');
        }

        // Buka dropdown yang berisi item active
        $('.sidebar-menu .dropdown').each(function() {
            var $dropdown = $(this);
            var $header = $dropdown.find('> .nav-link.has-dropdown');
            var $dropdownMenu = $dropdown.find('.dropdown-menu');
            var dropdownId = $dropdown.attr('id');

            // Tambahkan kelas parent-active jika ada item active
            if ($dropdownMenu.find('.nav-link.active').length > 0) {
                $header.addClass('parent-active');

                // Buka dropdown jika belum terbuka
                if (!$dropdownMenu.hasClass('show')) {
                    $header.addClass('expanded'); // Change from 'active' to 'expanded'
                    $dropdownMenu.slideDown(300).addClass('show');
                    localStorage.setItem('openDropdown', dropdownId);
                }
            }
        });
    });
  </script>
  
  {{-- <script>
    $(document).ready(function() {
      var currentPath = window.location.pathname;
  
      $('.nav-link a[href="' + currentPath + '"]').addClass('active');
    });

  </script> --}}

  <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
</body>
</html>
