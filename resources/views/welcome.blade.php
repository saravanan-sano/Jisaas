<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company->short_name }}</title>
    <link rel="icon" type="image/png" href="{{ $company->small_light_logo_url }}">
    <meta name="msapplication-TileImage" href="{{ $company->small_light_logo_url }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap">

    @if ($themeMode == 'dark')
        <link rel="stylesheet" href="{{ asset('css/antd.dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/antd.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pos_invoice_css.css') }}">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-R5DYG4DCFG"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-R5DYG4DCFG');
    </script>
    <!-- Meta Pixel Code -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '665350178790948');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=665350178790948&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
    <!-- <script src="https://support.jierp.in/jnana_support/js/min/jquery.min.js"></script> -->
        <!-- <script id="sbinit" src="https://support.jierp.in/jnana_support/js/main.js"></script> -->

</head>

<body class="{{ $themeMode == 'dark' ? 'dark_theme' : 'light_theme' }}">
    <div id="app"></div>
    <script>

        window.config = {
            'path': '{{ url('/') }}',
            'download_lang_csv_url': "{{ route('api.extra.langs.download') }}",
            'invoice_url': "{{ route('api.extra.pdf.v1', '') }}",
            'view_invoice_url': "{{ route('api.extra.viewpdf.v1', '') }}",
            'pos_invoice_css': "{{ asset('css/pos_invoice_css.css') }}",
            'verify_purchase_background': "{{ asset('images/verify_purchase_background.jpg') }}",
            'login_background': "{{ asset('images/login_background.svg') }}",
            'purchase_sample_file': "{{ asset('images/purchase_sample_file.csv') }}",
            'product_sample_file': "{{ asset('images/jnanaerp_products.csv') }}",
            'category_sample_file': "{{ asset('images/jnanaerp_categories.csv') }}",
            'brand_sample_file': "{{ asset('images/jnanaerp_brands.csv') }}",
            'customers_sample_file': "{{ asset('images/jnanaerp_customers.csv') }}",
            'suppliers_sample_file': "{{ asset('images/jnanaerp_suppliers.csv') }}",
            'staff_members_sample_file': "{{ asset('images/jnanaerp_staff_members.csv') }}",
            'translatioins_sample_file': "{{ asset('images/jnanaerp_translations.csv') }}",
            'perPage': 10,
            'product_name': "{{ $appName }}",
            'product_version': "{{ $appVersion }}",
            'modules': @json($enabledModules),
            'installed_modules': @json($installedModules),
            'theme_mode': "{{ $themeMode }}",
            'country_code': "{{$country_code}}" ,
            'app_type': "{{ $appType }}"
        };
    </script>
    @if (app_type() == 'multiple')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif
    <script src="{{ asset('js/app.js?version='.date("ymdhis").'') }}"></script>
</body>

</html>
