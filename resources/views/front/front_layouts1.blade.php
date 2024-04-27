<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ isset($seoDetail) ? $seoDetail->seo_title : '' }} | {{ ucwords($frontSetting->app_name)}}</title>
    <meta name="description" content="{{ isset($seoDetail) ? $seoDetail->seo_description : '' }}">
    <meta name="keywords" content="{{ isset($seoDetail) ? $seoDetail->seo_keywords : '' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="refresh" content="3;url=https://jnanaerp.com/admin/login" />

    <meta property="og:title" content="{{ isset($seoDetail) ? $seoDetail->seo_title : '' }} | {{ ucwords($frontSetting->app_name)}}">
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ route('front.index') }}">
    <meta property="og:site_name" content="{{ ucwords($frontSetting->app_name)}}" />
    <meta property="og:description" content="{{ isset($seoDetail) ? $seoDetail->seo_description : '' }}">

    @include('front.sections.styles')

    @yield('styles')
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-R5DYG4DCFG"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-R5DYG4DCFG');
    </script>
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '665350178790948');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=665350178790948&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <script src='https://www.google.com/recaptcha/api.js'></script>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-11201708782"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-11201708782');
</script>
<!-- Event snippet for Website sale conversion page -->
<script>
  gtag('event', 'conversion', {
      'send_to': 'AW-11201708782/CKefCNftxa8YEO6Fst0p',
      'transaction_id': ''
  });
</script>
<!-- <script src="https://support.jierp.in/jnana_support/js/min/jquery.min.js"></script>
        <script id="sbinit" src="https://support.jierp.in/jnana_support/js/main.js"></script> -->




</head>

<body class="antialiased bg-body text-body font-body">
	<div class="">
        @include('front.sections.header')

        @yield('content')

        @include('front.sections.footer')

        @include('front.sections.scripts')

        @yield('scripts')

        <script>
            "use strict";

            function changeLang(langKey) {
                art.sendXhr({
                    url: "{{route('front.change-language')}}",
                    type: "POST",
                    data: { key: langKey },
                    container: "#ajax-register-form",
                    success: function(response) {
                        if(response.status == 'success'){
                            window.location.reload();
                        }
                    }
                });
            }
        </script>
    </div>
</body>

</html>
