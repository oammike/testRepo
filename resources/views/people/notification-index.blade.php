@extends('layouts.main')

@section('metatags')
<title>Notifications | OAMPI Evaluation System</title>
@endsection

@section('content')


  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Your Notifications
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Notifications</li>
      </ol>
    </section>

     <section class="content">
      <!-- ******** THE DATATABLE ********** -->
          <div class="row">
             <div class="col-lg-12">
              <div class="box box-primary">
                      <div class="box-header ">
                        
                      </div><!--end box-header-->
                      
                      <div class="box-body">

                          <div class="tab-pane" id="timeline">
                            <!-- The timeline -->
                            <ul class="timeline timeline-inverse">

                              @foreach( $yourNotifs as $notif )
                             
                              <!-- timeline time label -->
                              <li class="time-label">
                                    <span class="bg-default">
                                     {{date('M d, Y', strtotime($notif['created_at'])) }}
                                    </span>
                              </li>
                              <!-- /.timeline-label -->


                              <!-- timeline item -->
                              <li>
                                <i class="{{ $notif['icon']}} @if(!$notif['seen']) bg-green @endif"></i>

                                <div class="timeline-item">
                                  <span class="time"><i class="fa fa-clock-o"></i> {{$notif['ago']}} ago</span> 

                                  <h5 class="timeline-header  @if(!$notif['seen']) bg-warning @endif"><a href="{{$notif['actionlink']}}" @if(!$notif['seen']) class="text-success" @endif>{{ $notif['title']}} </a> 
                                    </h5>

                                  <div class="timeline-body @if(!$notif['seen']) bg-warning @endif">
                                    {!! $notif['fromImage'] !!} <strong> {{$notif['from']}}</strong> <small>({{$notif['position']}})</small> <em> {{$notif['message']}}</em> 
                                  </div>
                                  <div class="timeline-footer @if(!$notif['seen']) bg-warning @endif" >
                                   
                                  </div>
                                </div>
                              </li>
                              <!-- END timeline item -->

                              @endforeach

                              
                             
                              <li>
                                <i class="fa fa-clock-o bg-gray"></i>
                              </li>
                            </ul>
                          </div>
                          <!-- /.tab-pane -->


                      </div><!--end box-body-->
              </div><!--end box-primary-->

          </div><!--end main row-->
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