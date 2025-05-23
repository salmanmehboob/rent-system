 <script src="{{ asset('assets/vendor/jquery-3.7.1.js') }}"></script>

    <!-- jquery repeater file -->
 <script src="{{ asset('assets/js/jquery.repeater.min.js') }}"></script>

 <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.js') }}"></script>

    <!-- Bootstrap core JavaScript-->
 <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
 <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
 <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
 {{-- <script src="{{ asset('assets/vendor/chart.js/Chart.min.js') }}"></script> --}}

    <!-- Page level custom scripts -->
 {{-- <script src="{{ asset('assets/js/demo/chart-area-demo.js') }}"></script>
 <script src="{{ asset('assets/js/demo/chart-pie-demo.js') }}"></script> --}}


     <!-- select2 -->
 
 <script src="{{ asset('assets/vendor/select2/js/select2.min.js')  }}"></script> --}}


 <!-- Toastr -->
 <script src="{{ asset('assets/vendor/toastr/js/toastr.min.js') }}"></script>

 <!-- Datatable -->

 <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
 <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
 <!-- Sweet Alert -->
 <script src="{{ asset('assets/vendor/sweetalert2/dist/sweetalert2.min.js') }}"></script>


 <script src="{{ asset('assets/js/custom.js')  }}"></script>


 <script>
    var urlPath = '<?php echo url(""); ?>';
    var CSRF_TOKEN = '<?php echo csrf_token(); ?>';


    window.sessionMessages = {
        success: @json(session('success')),
        error: @json(session('error')),
        info: @json(session('info'))
    };
 </script>



 @stack('js')