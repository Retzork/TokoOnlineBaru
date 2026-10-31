<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('backend/images/icon_univ_bsi.png') }}">
    <title>Toko Online</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('backend/extralibs/multicheck/multicheck.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/dist/css/style.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- HTML5 Shim and Respond.js for IE8 support -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div id="main-wrapper">
        <!-- Topbar Header -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <!-- Sidebar toggle (mobile only) -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                    <!-- Logo -->
                    <a class="navbar-brand">
                        <b class="logo-icon p-l-10">
                            <img src="{{ asset('backend/images/icon_univ_bsi.png') }}" alt="homepage" class="light-logo" style="width: 30px; height: auto;" />
                        </b>                                       
                        <span class="logo-text">
                            <img src="{{ asset('backend/images/logo_text.png') }}" alt="homepage" class="light-logo" />
                        </span>
                    </a>
                    <!-- Mobile Menu Toggle -->
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="ti-more"></i>
                    </a>
                </div>

                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block">
                            <a class="nav-link sidebartoggler" href="javascript:void(0)" data-sidebartype="minisidebar">
                                <i class="mdi mdi-menu font-24"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @if (Auth::user()->foto)
                                    <img src="{{ asset('storage/img-user/' . Auth::user()->foto) }}" alt="user" class="rounded-circle" width="31">
                                @else
                                    <img src="{{ asset('storage/img-user/img-default.jpg') }}" alt="user" class="rounded-circle" width="31">
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <a class="dropdown-item" href="{{ route('backend.user.edit', Auth::user()->id) }}">
                                    <i class="ti-user m-r-5 m-l-5"></i> Profil Saya
                                </a>
                                <a class="dropdown-item" href="" onclick="event.preventDefault(); document.getElementById('keluar-app').submit();">
                                    <i class="fa fa-power-off m-r-5 m-l-5"></i> Keluar
                                </a>
                                <div class="dropdown-divider"></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Left Sidebar -->
        <aside class="left-sidebar" data-sidebarbg="skin5">
            <div class="scroll-sidebar">
                <!-- Sidebar Navigation -->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav" class="p-t-30">
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('backend.beranda') }}" aria-expanded="false">
                                <i class="fa-solid fa-house" style="font-size: 20px;"></i>
                                <span class="hide-menu">Beranda</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('backend.user.index') }}" aria-expanded="false">
                                <i class="fa-solid fa-user" style="font-size: 20px;"></i>
                                <span class="hide-menu">User</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('backend.customer.index') }}" aria-expanded="false">
                                <i class="fa-solid fa-users" style="font-size: 20px;"></i>
                                <span class="hide-menu">Customer</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                                <i class="fa-solid fa-receipt" style="font-size: 20px;"></i>
                                <span class="hide-menu">Pesanan</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a href="{{ route('pesanan.proses') }}" class="sidebar-link">
                                        <i class="fa-solid fa-spinner" style="font-size: 20px;"></i>
                                        <span class="hide-menu">Pesanan Proses</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('pesanan.selesai') }}" class="sidebar-link">
                                        <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i>
                                        <span class="hide-menu">Pesanan Selesai</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                                <i class="fa-solid fa-cart-shopping" style="font-size: 20px;"></i>
                                <span class="hide-menu">Data Produk</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item"><a href="{{ route('backend.kategori.index') }}"
                                    class="sidebar-link"><i class="fa-solid fa-list" style="font-size: 20px;"></i><span class="hidemenu"> Kategori </span></a>
                                    </li>
                                <li class="sidebar-item"><a href="{{ route('backend.produk.index') }}"
                                        class="sidebar-link"><i class="fa-solid fa-tag" style="font-size: 20px;"></i><span class="hidemenu"> Produk </span></a>
                                    </li>
                            </ul>

                            <li class="sidebar-item">
                                <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                                    <i class="mdi mdi-receipt"></i>
                                    <span class="hide-menu">Laporan</span>
                                </a>
                                <ul aria-expanded="false" class="collapse first-level">
                                    <li class="sidebar-item">
                                        <a href="{{ route('backend.laporan.formuser') }}" class="sidebar-link">
                                            <i class="fa-solid fa-user" style="font-size: 20px;"></i>
                                            <span class="hide-menu">User</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="{{ route('backend.laporan.formproduk') }}" class="sidebar-link">
                                            <i class="fa-solid fa-tag" style="font-size: 20px;"></i>
                                            <span class="hide-menu">Produk</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="{{ route('backend.laporan.formpesananproses') }}" class="sidebar-link">
                                            <i class="fa-solid fa-spinner" style="font-size: 20px;"></i>
                                            <span class="hide-menu">Pesanan Proses</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="{{ route('backend.laporan.formpesananselesai') }}" class="sidebar-link">
                                            <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i>
                                            <span class="hide-menu">Pesanan Selesai</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            
                            <li class="sidebar-item">
                                <a 
                                    class="sidebar-link waves-effect waves-dark" 
                                    href="#" 
                                    aria-expanded="false" 
                                    onclick="confirmKeluar()"
                                >
                                <i class="fa-solid fa-right-to-bracket" style="font-size: 20px;"></i>
                                    <span class="hide-menu">Keluar</span>
                                </a>
                            </li>
                                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                            <script>
                                function confirmKeluar() {
                                    Swal.fire({
                                    title: 'Konfirmasi Keluar',
                                    text: 'Apakah Anda yakin ingin keluar?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Ya, Keluar',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('backend.login') }}";
                                    }
                                });
                                }
                            </script>                           
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <!-- Breadcrumb Section -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                        
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="container-fluid">
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="footer text-center">
                Web Programming. Studi Kasus Toko Online <a href="https://bsi.ac.id/">Kuliah..? BSI Aja !!!</a>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    
    <script src="{{ asset('backend/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('backend/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('backend/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/sparkline/sparkline.js') }}"></script>
    <script src="{{ asset('backend/dist/js/waves.js') }}"></script>
    <script src="{{ asset('backend/dist/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('backend/dist/js/custom.min.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/multicheck/datatable-checkbox-init.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/multicheck/jquery.multicheck.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/DataTables/datatables.min.js') }}"></script>
    <script>
        $('#zero_config').DataTable();
    </script>

<head>
    <!-- Include SweetAlert2 CSS and JS from Local Files -->
    <link href="{{ asset('assets/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/sweetalert/sweetalert2.all.min.js') }}"></script>
</head>
<body>
    <!-- Laravel Blade conditional for session success -->
    @if (session('success'))
        <script>
            // SweetAlert2 runs after the page is fully loaded
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                });
            });
        </script>
    @endif
</body>
<body>
    <script type="text/javascript">
        // Ensure jQuery is ready before executing
        $(document).ready(function() {
            // Konfirmasi delete
            $('.show_confirm').click(function(event) {
                var form = $(this).closest("form"); // Get the closest form element
                var konfdelete = $(this).data("konf-delete"); // Get the name of the item to delete
                event.preventDefault(); // Prevent the default action of the button (if it's a submit button)
    
                Swal.fire({
                    title: 'Konfirmasi Hapus Data?',
                    html: "Data yang dihapus <strong>" + konfdelete + "</strong> tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, dihapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
                        .then(() => {
                            form.submit(); // Submit the form if confirmed
                        });
                    }
                });
            });
        });
        </script>
</body>

    <!-- SweetAlert End -->

    <!-- Logout Form -->
    <form id="keluar-app" action="{{ route('backend.logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</body>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <!-- <script
    src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script> -->
    <script>
    ClassicEditor
        create(document.querySelector('#ckeditor'))
        .catch(error => {
            console.error(error);
        });
    </script>
<body>
    <script type="text/javascript">
        // Function to preview photo
        function previewFoto() {
          const foto = document.querySelector('input[name="foto"]');
          const fotoPreview = document.querySelector('.foto-preview');
          
          // Make the preview visible
          fotoPreview.style.display = 'block';
          
          const fotoReader = new FileReader();
          
          // Read the selected file
          fotoReader.readAsDataURL(foto.files[0]);
          
          // Onload event to update the preview image
          fotoReader.onload = function(fotoEvent) {
            fotoPreview.src = fotoEvent.target.result;
            fotoPreview.style.width = '100%';
          };
        }
      </script> 
</body>     
</html>
