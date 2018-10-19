@extends('layouts.main')

@section('metatags')
<title>Dashboard | OAMPI Evaluation System</title>
@endsection

@section('content')


  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        <small>{{ $myCampaign->first()->name }} </small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

     <section class="content">



                 <!-- ******** THE DATATABLE ********** -->
          <div class="row">
             
              <div class="col-lg-2 col-sm-2  col-xs-12"></div>
                
              <div class="col-lg-8 col-sm-8  col-xs-12">
                 <h1 class="text-center" style="color:#C1C8D1"><br/><br/>Show me all of my :</h1>

                          {{ Form::open(['route' => 'evalForm.grabAllWhosUpFor', 'class'=>'', 'id'=> 'showAll' ]) }}
                <div class="col-lg-11"><select name="evalType_id" class="form-control pull-left">
                  <option value="0"> --  Select One -- </option>
                  @foreach ($evalTypes as $evalType)
                  <option value="{{$evalType->id}}"><?php if ($evalType->id==1 ) echo date('Y'); else if($evalType->id==2){ if( date('m')>=7 && date('m')<=12 )echo date('Y'); else echo date('Y')-1;  } ?> {{$evalType->name}}</option>
                  @endforeach

                  
                </select>
                </div>
                <div class="col-lg-1">
                 {{Form::submit(' Go ', ['class'=>'btn btn-md btn-success', 'id'=>'showEvalBtn', 'style'=>"margin-bottom:20px;"])}}</div>

              </div>
              <div class="col-lg-2 col-sm-2  col-xs-12"></div>
              {{Form::close()}}
              <br/><br/><br/><hr/>
                      

          </div>
                <!-- ******** END THE DATATABLE ********** -->


           

       
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