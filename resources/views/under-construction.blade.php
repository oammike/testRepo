@extends('layouts.main')

@section('metatags')
<title>Access Denied | OAMPI Evaluation System</title>
@endsection

@section('content')


  <!-- Content Header (Page header) -->
    <section class="content-header">
     
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Access Denied</li>
      </ol>
    </section>

     <section class="content">



                 <!-- ******** THE DATATABLE ********** -->
          <div class="row">
              <div class="col-lg-3 col-sm-4 col-xs-12" style="background-color:#fff">

                        
                            
              </div> 
              <div class="col-lg-3 col-sm-4  col-xs-9">
                
                
                      

          </div>
               

     
          <div class="row">

            <h3 class="text-center"> <br/><br /><i class="fa fa-exclamation-triangle text-danger"></i><br/> Module Under Construction<br /> <small>Viewing of this module will be available once system upgrade is completed.</small>
              <br /><br/></h3>

            

          </div><!-- end row -->





       
     </section>
          



@endsection


@section('footer-scripts')

<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>



<!-- Page script -->
<script>



  $(function () {
   'use strict';
   

   


      
      
   });

   

  /*
   * Custom Label formatter
   * ----------------------
   */
  function labelFormatter(label, series) {
    return '<div style="font-size:13px; text-align:center; padding:2px; color: #fff; font-weight: 600;">'
        + label
        + "<br>"
        + Math.round(series.percent) + "%</div>";
  }
</script>
<!-- end Page script -->

<script type="text/javascript" src="{{asset('public/js/dashboard.js')}}"></script>

@stop