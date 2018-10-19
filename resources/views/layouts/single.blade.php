<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  @yield('metatags')
   <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
   <link rel="icon" href="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}" sizes="32x32"/> 
  <link rel="icon" href="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-256x256.png')}}" sizes="192x192"/> 
  <link rel="apple-touch-icon-precomposed" href="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-256x256.png')}}"/> 
  <meta name="msapplication-TileImage" content="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon.png')}}"/>

 <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 <link href="{{asset('public'.elixir('css/all.css'))}}" rel="stylesheet" />
  <!-- Font Awesome -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"> -->

  <!-- Ionicons -->
 <!--  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css"> -->
  <!-- Theme style -->
  
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition @yield('bodyClasses')" >

   @yield('content')
    
    <!-- /.content -->


<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.2.0 -->
<!-- <script src="{{asset('public/plugins/jQuery/jQuery-2.2.0.min.js')}}"></script> -->
<!-- Bootstrap 3.3.6 -->
<!-- <script src="{{asset('public/bootstrap/js/bootstrap.min.js')}}"></script>
 --><!-- AdminLTE App -->
<!-- <script src="{{asset('public/dist/js/app.min.js')}}"></script>
 -->
<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. Slimscroll is required when using the
     fixed layout. -->

<script type="text/javascript" src="{{asset('public'.elixir('js/all.js'))}}"></script>
     @yield('footer-scripts')
</body>
</html>
