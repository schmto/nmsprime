
      <!-- ================== BEGIN BASE JS ================== -->
      <script src="{{asset('components/assets-admin/plugins/jquery/jquery-1.9.1.min.js')}}"></script>
      <script src="{{asset('components/assets-admin/plugins/jquery/jquery-migrate-1.1.0.min.js')}}"></script>
      <script src="{{asset('components/assets-admin/plugins/jquery-ui/ui/minified/jquery-ui.min.js')}}"></script>
      <script src="{{asset('components/assets-admin/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
      <!--[if lt IE 9]>
        <script src="{{asset('components/assets-admin/crossbrowserjs/html5shiv.js')}}"></script>
        <script src="{{asset('components/assets-admin/crossbrowserjs/respond.min.js')}}"></script>
        <script src="{{asset('components/assets-admin/crossbrowserjs/excanvas.min.js')}}"></script>
      <![endif]-->
      <script src="{{asset('components/assets-admin/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
      <script src="{{asset('components/assets-admin/plugins/select2-v4/vendor/select2/select2/dist/js/select2.js')}}"></script>
      <script src="{{asset('components/assets-admin/plugins/DataTables/js/jquery.dataTables.min.js')}}"></script>
      <!-- ================== END BASE JS ================== -->

      <!-- ================== BEGIN PAGE LEVEL JS ================== -->
      <script src="{{asset('components/assets-admin/js/apps.min.js')}}"></script>
      <!-- ================== END PAGE LEVEL JS ================== -->


      <script type="text/javascript">

        /*
         * global document ready function
         */
        $(document).ready(function() {
          App.init();
          // Dashboard.init();

          // Select2 Init - intelligent HTML select
          $("select").select2();

          // Intelligent Data Tables
          $('.itable').dataTable( {
            "lengthMenu": [[10, 25, 100, 250, 500, -1], [10, 25, 100, 250, 500, "All"]]
          } );

        });


        /*
         * Table on-hover click
         * NOTE: This automatically adds on-hover click to all table 'td' elements which are in class 'ClickableTd'.
         *       Please note that the table needs to be in class 'table-hover' for visual marking.
         *
         * HOWTO:
         *  - If clicked on td element which is assigned in class ClickableTd the function bellow is called.
         *  - fetch parent element of td element, which should/(must?) be a row.
         *  - search in tr HTML code for an HTML "a" element and fetch the href attribute
         * INFO: - working directly with row element also adds a click object to checkbox entry, which disabled checkbox functionality
         */
        $('.ClickableTd').click(function () {
          window.location = $(this.parentNode).find('a').attr("href");
        });

      </script>