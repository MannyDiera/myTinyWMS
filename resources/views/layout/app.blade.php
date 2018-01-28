<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') </title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{!! asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! asset('css/app.css') !!}" />

    @yield('extra_head')

</head>

<body class="top-navigation">
    <!-- Wrapper-->
    <div id="wrapper">

        <!-- Page wraper -->
        <div id="page-wrapper" class="gray-bg">

            <!-- Page wrapper -->
            @include('layout.topnavbar')

            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-12">
                    <h2>@yield('title')</h2>
                    <ol class="breadcrumb pull-left">
                        @yield('breadcrumb')
                    </ol>
                    <div class="btn-toolbar pull-right">
                        @yield('subnav')
                    </div>
                </div>
            </div>

            <!-- Main view  -->
            <div class="wrapper wrapper-content">
                @include('flash::message')

                @yield('content')
            </div>

            <!-- Footer -->
            @include('layout.footer')
        </div>

        @if (!empty($__env->yieldContent('datatableFilters')))
            <div id="datatableFilter" class="hidden">
                <div class="pull-left m-b-md">
                    <h4 class="text-left">Filter:</h4>
                    @yield('datatableFilters')
                </div>
            </div>
        @endif
    </div>

    <script src="{!! asset('js/vendor.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('js/app.js') !!}" type="text/javascript"></script>

    @if (!empty($__env->yieldContent('datatableFilters')))
        <script>
            $('#dataTableBuilder').on( 'init.dt', function () {
                if ($('#datatableFilter').html().length) {
                    $('#dataTableBuilder_filter').append($('#datatableFilter').html());
                    $('#datatableFilter').remove();

                    $('.datatableFilter-select').each(function () {
                        $(this).change(function () {
                            window.LaravelDataTables.dataTableBuilder.columns($(this).attr('data-target-col')).search($(this).val()).draw();
                            saveFilterState($(this).attr('id'), $(this).attr('data-target-col'), $(this).val());
                        });

                        if ($(this).attr('data-pre-select')) {
                            $(this).val($(this).attr('data-pre-select'));
                        }
                    });

                    loadFilterState();
                }
            });

            function saveFilterState(elementId, col, value) {
                var currentFilterState = JSON.parse(localStorage.getItem('datatables-filterState'));
                if (currentFilterState === null) {
                    currentFilterState = {};
                }

                currentFilterState[elementId] = {value: value, col: col};
                localStorage.setItem('datatables-filterState', JSON.stringify(currentFilterState));
            }

            function loadFilterState() {
                var currentFilterState = JSON.parse(localStorage.getItem('datatables-filterState'));
                console.log(currentFilterState, typeof currentFilterState);
                if (currentFilterState !== null) {
                    $.each(currentFilterState, function (elementId, item) {
                        $('#'+elementId).val(item.value);
                    });
                }
            }
        </script>
    @endif

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            if ($('#select_all').length) {
                $('#select_all').change(function () {
                    if ($(this).is(':checked')) {
                        $('#dataTableBuilder tbody input[type=checkbox]').attr('checked', 'checked');
                    } else {
                        $('#dataTableBuilder tbody input[type=checkbox]').attr('checked', null);
                    }
                });

                $('body').on('click', '#dataTableBuilder tbody tr td', function () {
                    var checkbox = $(this).parent().find('input[type=checkbox]');
                     if (checkbox.is(':checked')) {
                        checkbox.attr('checked', null);
                     } else {
                        checkbox.attr('checked', 'checked');
                     }
                })
            }
        });
    </script>
    @stack('scripts')
</body>
</html>