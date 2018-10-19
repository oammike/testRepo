@extends('layouts.main')


@section('metatags')
  <title>{{$user->firstname}}'s Profile</title>
    <meta name="description" content="profile page">

@stop


@section('content')




<section class="content-header">

      <h1>
      My <strong class="text-success">D</strong>aily <strong class="text-success">T</strong>ime <strong class="text-success">R</strong>ecord
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        
        <li class="active">My DTR</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        
        <div class="col-xs-12">
          


          <!-- Profile Image -->
                          <div class="box box-primary">

                            <div class="box-body box-profile">
                               <!-- Widget: user widget style 1 -->
                                    <div class="box box-widget widget-user">
                                      <!-- Add the bg color to the header using any of the bg-* classes -->
                                      <div class="widget-user-header bg-black" style="background: url('{{URL:: asset("public/img/bg.jpg")}}') top left;">
                                        
                                        <h3 style="text-shadow: 1px 2px #000000; text-transform:uppercase" class="widget-user-username">{{$user->lastname}}, {{$user->firstname}} {{$user->middlename}}</h3>
                                        <h5 style="text-shadow: 1px 2px #000000;"  class="widget-user-desc">{{$user->position->name}} </h5>
                                      </div>
                                      <div class="widget-user-image">
                                        
                                         @if ( file_exists('public/img/employees/'.$user->id.'.jpg') )
                                          <img src="{{asset('public/img/employees/'.$user->id.'.jpg')}}" class="user-image" alt="User Image">
                                          @else
                                          <img src="{{asset('public/img/useravatar.png')}}" class="user-image" alt="User Image">

                                            @endif

                                          
                                        <br/>
                                      </div>
                                      <div class="box-footer">
                                        <div class="row">
                                          <div class="col-sm-3">
                                            <div class="description-block">
                                              <p class="description-header"><i class="fa fa-users margin-r-5"></i> Department / Program : </p>
                                              <span class="description-text text-primary">
                                              @if(count($camps) > 1)

                                                  @foreach($camps as $ucamp)
                                                      {{$ucamp->name}} , 

                                                  @endforeach

                                              @else
                                              {{$camps->first()->name}}

                                              @endif
                                              </span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->
                                          <div class="col-sm-3">
                                            <div class="description-block">
                                              <p class="description-header"><i class="fa fa-street-view margin-r-5"></i> Immediate Head: </p>
                                              <span class="description-text text-primary">{{$immediateHead->firstname}} {{$immediateHead->lastname}}</span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->
                                          <div class="col-sm-3">
                                            <div class="description-block">
                                              <p class="description-header"><i class="fa fa-users margin-r-5"></i> Employee Number : </p>
                                              <span class="description-text text-primary">{{$user->employeeNumber}} </span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->
                                          <div class="col-sm-3 ">
                                            <div class="description-block">
                                              <p class="description-header"><i class="fa fa-envelope-o margin-r-5"></i> E-mail:</p>
                                              <span><a href="mailto:{{$user->email}}"> {{$user->email}}</a></span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->

                                          <!-- START CUSTOM TABS -->
     

                                         
                                          
                                          <!-- /.row -->
                                          <!-- END CUSTOM TABS -->
                                          
                                          <!-- /.col -->
                                          

                                      </div>
                                    </div>
                                    <!-- /.widget-user -->

                                    <div class="row">
                                          <!--   <div class="col-lg-1 col-sm-12"></div> -->
                                            <div class="col-lg-12 col-sm-12"><br/><br/>
                                              
                                              
                                                <div class="row">
                                                  <div class="col-lg-5 text-right"><a class="btn btn-default btn-sm" href="{{action('DTRController@show',['id'=>$user->id,'from'=>$prevFrom, 'to'=>$prevTo])}}">
                                                  <i class="fa fa-arrow-left"></i> </a></div>
                                                  <div class="col-lg-2">
                                                    <select class="form-control" name="payPeriod" id="payPeriod">
                                                      @if($cutoffID == 0)
                                                      <option value='0'>* Select cutoff period *</option>
                                                      @endif
                                                      @foreach($paycutoffs as $cutoffs)
                                                      <option value="{{$cutoffs->id}}" @if($cutoffID == $cutoffs->id) selected="selected" @endif data-fromDate="{{$cutoffs->fromDate}}" data-toDate="{{$cutoffs->toDate}}">{{ date('M d, Y', strtotime($cutoffs->fromDate)) }} - {{date('M d, Y', strtotime($cutoffs->toDate))}} </option>
                                                      @endforeach
                                                    </select>
                                                   

                                                  </div>
                                                  <div class="col-lg-5 text-left"><a class="btn btn-default btn-sm" href="{{action('DTRController@show',['id'=>$user->id,'from'=>$nextFrom, 'to'=>$nextTo])}}">
                                                  <i class="fa fa-arrow-right"></i></a></div>

                                                </div>

                                                <h4 class="text-center"><br/><br/>
                                                <small>Cutoff Period: </small><br/>
                                                <span class="text-success">&nbsp;&nbsp; {{$cutoff}} &nbsp;&nbsp; </span>

                                                  
                                              </h4>

                                              
                                              <a target="_blank" href="{{action('LogsController@viewRawBiometricsData', $user->id)}}" class="btn btn-xs btn-primary pull-right"><i class="fa fa-search"></i> View Raw Biometrics Data</a><br/><br/>
                                              <table id="biometrics" class="table table-bordered table-striped">
                                                  <thead>
                                                  <tr class="text-success">
                                                    
                                                    <th class="text-center" style="width:15%">Production Date</th>
                                                    <td class="text-center"></td>
                                                    <th class="text-center" style="width:12%">IN</th>
                                                    <th class="text-center" style="width:12%">OUT</th>
                                                    <th class="text-center" style="width:15%">Work Shift</th>
                                                    <th class="text-center">Hrs. Worked</th>
                                                    
                                                    <th class="text-center"  style="width:10%">OT<br/>(Billable hrs.)</th>
                                                    <th class="text-center"  style="width:10%">OT<br/>(Approved hrs.)</th>
                                                    <th  class="text-center">UT<br/>(hours)</th>
                                                   

                                                     
                                                  </tr>
                                                  </thead>
                                                  <tbody style="font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:0.9em">

                                                    

                                                    @if (count($myDTR) == 0)
                                                    <tr>
                                                      <td colspan='10' class="text-center"><h2 class="text-center text-default"><br/><br/><i class="fa fa-clock-o"></i>&nbsp;&nbsp; No Biometrics Data Available</h2><small>Kindly check again at the end of work day or tomorrow for the updated biometrics data.</small><br/><br/><br/></td>
                                                    </tr>

                                                    @else
                                                     

                                                     @foreach ($myDTR as $data)



                                                     <tr>
                                                        
                                                        <td class="text-center">{{ $data['productionDate'] }} </td>
                                                        <td class="text-center">{{ $data['day'] }} </td>
                                                        <td class="text-center">{!! $data['logIN'] !!} </td>
                                                        <td class="text-center">{!! $data['logOUT'] !!} </td>
                                                        @if ($data['shiftStart'] == null || $data['shiftEnd'] == null)
                                                        <td class="text-center"><em>No Work Schedule found</em> <a title="Plot Work Schedule" class="pull-right" href="{{action('UserController@show',$user->id)}}/createSchedule"> <i class="fa fa-calendar"></i></a> </td>

                                                        @else

                                                          @if($data['hasCWS']=='1') 
                                                        <td class="text-center">
                                                           @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                           {{ $data['shiftStart']}} - {!! $data['shiftEnd'] !!}<strong><a data-toggle="modal" data-target="#myModal_CWS{{$data['payday']}}" title="View Details" class="@if ($data['hasApprovedCWS'])text-green @else text-orange @endif pull-right" href="#" > <i class="fa fa-info-circle"></i></a></strong> </td>


                                                          @else

                                                            @if($theImmediateHead || $canChangeSched)
                                                            <td class="text-center">
                                                               @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                               {{ $data['shiftStart']}} - {!! $data['shiftEnd'] !!} <strong><a data-toggle="modal" data-target="#myModal_{{$data['payday']}}" title="Change Work Sched " class="text-primary pull-right" href="#" > <i class="fa fa-pencil"></i></a></strong> </td>
                                                            

                                                            @else
                                                            <td class="text-center">
                                                               @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                               {{ $data['shiftStart']}} - {!! $data['shiftEnd'] !!} <strong><a data-toggle="modal" data-target="#myModal_{{$data['payday']}}" title="Report Work Shift Issue " class="text-primary pull-right" href="#" > <i class="fa fa-flag-checkered"></i></a></strong> </td>
                                                            @endif

                                                         @endif
                                                        
                                                        @endif
                                                        <td class="text-center"> 
                                                          @if($data['isFlexitime'])<strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                          @if($data['isFlexitime']) <span style="text-decoration:line-through"><em> @endif
                                                          {!! $data['workedHours'] !!} 
                                                          @if($data['isFlexitime'])</em> </span> @endif
                                                        </td>



                                                        @if ($data['hasOT'])
                                                             

                                                                  @if ($theImmediateHead || $canChangeSched)
                                                                            @if ($data['hasApprovedOT'])
                                                                            <td class="text-center"> 
                                                                              @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                                              {!! $data['billableForOT'] !!} <a data-toggle="modal" data-target="#myModal_OT_details{{$data['payday']}}" title="View Details" class="pull-right text-green" style="font-size:1.2em;" href="#"><i class="fa fa-credit-card"></i></a></td>
                                                                            
                                                                            @else 

                                                                            <td class="text-center">
                                                                              @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                                              {!! $data['billableForOT'] !!} <a title="View Details" class="pull-right text-orange" style="font-size:1.2em;" href="{{action('UserOTController@show',$data['userOT']->first()['id'])}} "><i class="fa fa-credit-card"></i></a></td>
                                                                            
                                                                            @endif

                                                                  @else
                                                                  <td class="text-center">
                                                                    @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                                    {!! $data['billableForOT'] !!} <a data-toggle="modal" data-target="#myModal_OT_details{{$data['payday']}}" title="View Details" class="pull-right @if ($data['hasApprovedOT'])text-green @else text-orange @endif" style="font-size:1.2em;" href="#"><i class="fa fa-credit-card"></i></a></td>
                                                                  @endif

                                                             

                                                                

                                                        @else
                                                        <td class="text-center">
                                                          @if($data['isFlexitime']) <strong class="text-green"><i class="fa fa-refresh"></i> Flexi Sched</strong><br/> @endif 
                                                          {!! $data['billableForOT'] !!} {!! $data['OTattribute'] !!} </td>

                                                        @endif

                                                        <td class="text-center">@if( empty($data['approvedOT']) ) 0 @else {{$data['approvedOT']->first()['filed_hours']}} @endif</td>
                                                        <td class="text-center">{{$data['UT']}}</td>
                                                       
                                                        
                                                        
                                                     </tr>
                                                    
                                                        




                                                         @if ($theImmediateHead)
                                                            @include('layouts.modals-editDTR', [
                                                                  'modelRoute'=>'user_cws.store',
                                                                  'modelID' => '_'.$data["payday"], 
                                                                  'modelName'=>"Employee DTR ", 
                                                                  'modalTitle'=>'Edit', 
                                                                  'Dday' =>$data["day"],
                                                                  'DproductionDate' =>$data["productionDate"],
                                                                  'biometrics_id'=> $data["biometrics_id"],
                                                                  'approver' => $user->supervisor->immediateHead_Campaigns_id,
                                                                  'isRD'=> $data["isRD"],
                                                                  'timeStart_old'=>$data['shiftStart'],
                                                                  'timeEnd_old'=>$data['shiftEnd'],
                                                                  'formID'=>'reportIssue',
                                                                  'icon'=>'glyphicon-up' ])

                                                        @else
                                                            @include('layouts.modals-report', [
                                                                  'modelRoute'=>'user_cws.store',
                                                                  'modelID' => '_'.$data["payday"], 
                                                                  'modelName'=>"DTR issue ", 
                                                                  'modalTitle'=>'Report', 
                                                                  'Dday' =>$data["day"],
                                                                  'DproductionDate' =>$data["productionDate"],
                                                                  'biometrics_id'=> $data["biometrics_id"],
                                                                  'approver' => $user->supervisor->immediateHead_Campaigns_id,
                                                                  'isRD'=> $data["isRD"],
                                                                  'timeStart_old'=>$data['shiftStart'],
                                                                  'timeEnd_old'=>$data['shiftEnd'],
                                                                  'formID'=>'reportIssue',
                                                                  'icon'=>'glyphicon-up' ])
                                                        @endif


                                                        @if ($data['hasOT'])
                                                            @include('layouts.modals-OT_details', [
                                                                  'modelRoute'=>'user_ot.store',
                                                                  'modelID' => '_OT_details'.$data["payday"], 
                                                                  'modelName'=>"Overtime ", 
                                                                  'modalTitle'=>'OT Details', 
                                                                  'Dday' =>$data["day"],
                                                                  'DproductionDate' =>$data["productionDate"],
                                                                  'biometrics_id'=> $data["biometrics_id"],
                                                                  'approver' => $user->supervisor->immediateHead_Campaigns_id,
                                                                  'isRD'=> $data["isRD"],
                                                                  'formID'=>'submitOT',
                                                                  'icon'=>'glyphicon-up' ])

                                                        @else
                                                            @include('layouts.modals-OT', [
                                                                  'modelRoute'=>'user_ot.store',
                                                                  'modelID' => '_OT'.$data["payday"], 
                                                                  'modelName'=>"Overtime ", 
                                                                  'modalTitle'=>'Submit', 
                                                                  'Dday' =>$data["day"],
                                                                  'DproductionDate' =>$data["productionDate"],
                                                                  'biometrics_id'=> $data["biometrics_id"],
                                                                  'approver' => $user->supervisor->immediateHead_Campaigns_id,
                                                                  'isRD'=> $data["isRD"],
                                                                  'formID'=>'submitOT',
                                                                  'icon'=>'glyphicon-up' ])
                                                        @endif

                                                        


                                                        

                                                    
                                              
                                                      @endforeach
                                                  @endif

                                                  
                                                  </tbody>
                                                  
                                              </table>

                                              
                                            </div>
                                            <!-- /.col -->
                                           <!--  <div class="col-lg-1 col-sm-12"></div> -->

                                           
                                    </div>

                            </div>
                            

                            <!-- /.box-body -->
                          </div>

        </div>
        

      </div>

         

     

    </section>

@stop

@section('footer-scripts')
<script>
$(function () {

  //$(document).on('change','select.othrs.form-control',function(){
    $('select.othrs.form-control').on('change',function(){

       var timeStart = $(this).find('option:selected').attr('data-timestart');
       var timeEnd = $(this).find('option:selected').attr('data-timeend');

       console.log('start: ' + timeStart);
       console.log('end: ' + timeEnd);

       $('input[name="OTstart"]').val(timeStart);
       $('input[name="OTend"]').val(timeEnd);


    }); //end timeEnd check if on change


  // $("#biometrics").DataTable({
  //     "responsive":true,
  //     "scrollX":true,
  //     "stateSave": true,
  //      "processing":true,
  //     // "dom": '<"col-xs-1"f><"col-xs-11 text-right"l><"clearfix">rt<"bottom"ip><"clear">',
  //     //"lengthMenu": [20, 50, 100],//[5, 20, 50, -1], 
  //     "order": [[ 0, "asc" ]],
  //     "aLengthMenu": [[15, 25, 50, 100], [15, 25, 50, 100]],
  //     "iDisplayLength": 20,
  //     "lengthChange": false,
  //     "oLanguage": {
  //        "sSearch": "<small>To refine search, simply type-in</small><br> any values you want to look for:",
  //        "class": "pull-left"
  //      }
  //   });

  $('a').tooltip().css({"cursor":"pointer"});

$('#payPeriod').on('change',function(){
  var fromDate = $(this).find('option:selected').attr('data-fromDate'); //$(this).find('option:selected').val();
  var toDate = $(this).find('option:selected').attr('data-toDate');
  
  window.location.href= "{{url('/')}}/user_dtr/{{$user->id}}?from="+fromDate+"&to="+toDate;
});



$('.widget-user-image img').on('mouseover', function(e){
  $(this).css({"cursor":"pointer"});

}).on('click', function(){
  window.location.href = "{{action('UserController@show',$user->id)}} ";
});

});




    $(".incr-btn").on("click", function (e) {
        var $button = $(this);
        var oldValue = $button.parent().find('.quantity').val();
        var maxval = $('input[name="quantity"').attr('data-max'); // $button.parent().find('.quantity').attr('data-max');
        console.log(maxval);

        if (oldValue >= maxval )
          {
            $button.parent().find('.incr-btn[data-action="increase"]').addClass('inactive');
            $('#plus').fadeOut();
          } else
          {
            $button.parent().find('.incr-btn[data-action="decrease"]').removeClass('inactive');
            $button.parent().find('.incr-btn[data-action="increase"]').removeClass('inactive');
            $('#plus').fadeIn();

          }
        
        if ($button.data('action') == "increase") {
            var newVal = Math.round((parseFloat(oldValue) + 0.5),2);
            if (newVal >= maxval) $('#plus').fadeOut();
        } else {
            // Don't allow decrementing below 1
            if (oldValue > 1) {
                var newVal = parseFloat(oldValue) - 0.5;
            } else {
                newVal = 1;
                $button.addClass('inactive');
            }
            $('#plus').fadeIn();
        }
        $button.parent().find('.quantity').val(newVal);
        e.preventDefault();
    });

</script>
@stop