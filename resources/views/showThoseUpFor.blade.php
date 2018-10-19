@extends('layouts.main')

@section('metatags')
<title>Dashboard | OAMPI Evaluation System</title>
@endsection

@section('content')


  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
       
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
                 <h1 class="text-center" style="color:#C1C8D1"><br/><br/>Show all those who are up for :</h1>

                          {{ Form::open(['route' => 'evalForm.grabAllWhosUpFor', 'class'=>'', 'id'=> 'showAll' ]) }}
                <div class="col-lg-11"><select name="evalType_id" class="form-control pull-left">
                  <option value="0"> --  Select One -- </option>
                  @foreach ($evalTypes as $evalType)
                  <option @if($evalSetting->id == $evalType->id) selected="selected" @endif value="{{$evalType->id}}"><?php if ($evalType->id==1 ) echo date('Y'); else if($evalType->id==2){ if( date('m')>=7 && date('m')<=12 )echo date('Y'); else echo date('Y')-1;  } ?> {{$evalType->name}}</option>
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
               
               

      @if (count($mySubordinates) < 8)

      <!-- ******** LESS THAN 8 ********** -->
          <div class="row">

            

            <h4 class="text-center"> <small>Employees who are up for </small><br/>{{$evalSetting->name}} <br /> <br/></h4>

            @if (count($mySubordinates) == 0)

            <h3 class="text-center text-success"><br /><br />No entries found.</h3>

            @else
            
            @foreach ($mySubordinates as $employee)

            @if($employee->id !== Auth::user()->id)
            <div class="col-lg-3 col-sm-6 col-xs-12">
               <!-- Widget: user widget style 1 -->
                          <div class="box box-widget widget-user-2" style="min-height:465px">
                            <!-- Add the bg color to the header using any of the bg-* classes 
                            done & agent = gray
                            done & leader = darkgreen black
                            !done & leader = black
                            !done & agent = green

                          -->
                            <div class="widget-user-header  @if ($doneEval[$employee->id]['evaluated'] && ($employee->userType_id == '4') ) bg-gray @elseif ($doneEval[$employee->id]['evaluated'] && ($employee->userType_id !== '4') ) bg-black @elseif (!$doneEval[$employee->id]['evaluated'] && ($employee->userType_id !== '4') )bg-darkgreen  @else bg-green @endif">

                              <div class="widget-user-image">
                                 @if ( file_exists('public/img/employees/'.$employee->id.'.jpg') )
                                <a href="{{action('UserController@show',$employee->id)}}"> 
                                  <img src="{{asset('public/img/employees/'.$employee->id.'.jpg')}}" class="img-circle pull-left"width="70"  alt="User Image"></a>
                                @else
                                  <a href="{{action('UserController@show',$employee->id)}}" > 
                                    <img src="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}"width="70"  class="img-circle pull-left" alt="Employee Image"></a>

                                  @endif
                               
                                
                              </div>
                              <!-- /.widget-user-image -->
                              <h3 class="widget-user-username"> {{$employee->lastname }}, {{$employee->firstname}} </h3>
                              <h5 class="widget-user-desc"> {{$employee->position->name}} <br /><br/><em>Status: {{$employee->status->name}} </em></h5>
                             
                            </div>
                            <div class="box-footer no-padding">
                              
                              <ul class="nav nav-stacked">
                                <li><a href="#">Date Hired <span class="pull-right badge bg-gray">{{ date_format(date_create($employee->dateHired), "M d, Y")  }} </span></a></li>
                                <li><a href="#">Absences <span class="pull-right badge bg-red">N/A</span></a></li>
                                <li><a href="#">Tardiness <span class="pull-right badge bg-red">N/A</span></a></li>
                                <li><a href="#">Issued Disciplinary Actions <span class="pull-right badge bg-red">N/A</span></a></li>

                                

                                @if ($doneEval[$employee->id]['evaluated' ]&& $doneEval[$employee->id]['score'] != 0 && !$doneEval[$employee->id]['isDraft'])
                                <li><a href="#">
                                  <span class="pull-left"><strong>Current Rating</strong> <br/>
                                    <small>{{$doneEval[$employee->id]['startPeriod'] }} - {{$doneEval[$employee->id]['endPeriod'] }} </small></span><h3 class="pull-right text-danger">{{$doneEval[$employee->id]['score']}} %</h3></a></li>
                              

                                @else

                                <li><a href="#">
                                  <span class="pull-left">{{$evalSetting->name}} <br/>
                                    <small>
                                   
                                     Period covered: </small></span><h5 class="pull-right text-danger text-center"> {{date('M d, Y',strtotime($doneEval[$employee->id]['startPeriod']))  }} <br/>to<br/> {{date('M d, Y',strtotime($doneEval[$employee->id]['endPeriod']))  }} </h5></a></li>
                              

                                @endif

                              </ul>
                                
                              @if ( $doneEval[$employee->id]['evaluated'] && !$doneEval[$employee->id]['isDraft']  && $doneEval[$employee->id]['score'] > 0 )
                             
                               <p class="text-center"><a class="btn btn-md btn-default" href="{{action('EvalFormController@show',$doneEval[$employee->id]['evalForm_id']) }} "><i class="fa fa-check"></i> See Evaluation</a></p>
                              
                              @elseif ($doneEval[$employee->id]['isDraft'] == '1' && $doneEval[$employee->id]['evaluated'] &&  $doneEval[$employee->id]['score'] > 0 ) 
                              <p class="text-center"><a class="btn btn-md btn-danger" href="{{action('EvalFormController@edit',$doneEval[$employee->id]['evalForm_id']) }} "><i class="fa fa-check"></i> Continue Evaluating </a></p>
                             

                              @else <p class="text-center"><a class="btn btn-md btn-success" href="{{action('EvalFormController@newEvaluation', ['user_id'=>$employee->id, 'evalType_id'=>$evalSetting->id, 'currentPeriod'=>$doneEval[$employee->id]['startPeriod'], 'endPeriod'=>$doneEval[$employee->id]['endPeriod']]) }} "><i class="fa fa-check-square-o"></i> Evaluate Now </a></p> 
                              @endif
                            <div class="clearfix"></div>


                            </div>
                          </div>
                          <!-- /.widget-user -->

            </div><!--end employee card-->
            @endif
            @endforeach

            @endif
          </div><!-- end row -->
<!-- ******** END < 8 ********** -->

      @else

       <div class="row">

           <h4 class="text-center"> <small>Employees who are up for </small><br/>{{$evalSetting->name}} <br /> <br/></h4>

            <div class="col-lg-12">
              <table class="table" id="myTeam" >
                <thead >
                      <tr>
                        <th> </th>
                        
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Evaluation Period</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">Action</th>
                        
                      </tr>
                      </thead>
                      <tbody>
                        @foreach ($mySubordinates as $employee)

                        @if ($employee->id !== Auth::user()->id )
                      <tr id="row{{$employee->id}}">
                         @if ( file_exists('public/img/employees/'.$employee->id.'.jpg') )
                         <td class="text-center "><a href="{{action('UserController@show',$employee->id)}}"> 
                          <img src="{{asset('public/img/employees/'.$employee->id.'.jpg')}}" width='60' class="img-circle pull-left"width="70"  alt="User Image"/> </a></td>
                         @else
                         <td class="text-center ">
                          <a href="{{action('UserController@show',$employee->id)}}" > 
                            <img src="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}" class="img-circle pull-left"width="70"  alt="User Image"></a> </td>
                         @endif
                        
                        
                        <td>{{$employee->lastname}}</td>
                        <td>{{$employee->firstname}}</a></td>
                        <td>{{$employee->position->name}}</td>
                        <td>{{$employee->status->name}}</td>
                        <td>{{$doneEval[$employee->id]['startPeriod']}} to  {{$doneEval[$employee->id]['endPeriod']}}  </td>
                        <td class="text-danger">
                          @if ($doneEval[$employee->id]['evaluated' ]&& $doneEval[$employee->id]['score'] != 50)
                          <h4 class="text-center">{{$doneEval[$employee->id]['score']}} %</h4>
                          @endif

                        </td>
                        <td>

                         

                           @if ($doneEval[$employee->id]['evaluated'] && !$doneEval[$employee->id]['isDraft'] && $doneEval[$employee->id]['score'] != 0) <!--  -->
                               <p><a class="btn btn-sm btn-primary" href="{{action('EvalFormController@show',$doneEval[$employee->id]['evalForm_id']) }} " style="margin-bottom:5px"><i class="fa fa-check"></i> See Evaluation</a>
                               <!--  <a href="#" class="btn btn-xs btn-flat btn-default"style="margin-bottom:5px"><i class="fa fa-trash"></i> Delete </a> --> <div class="clearfix"></div>
                              </p>

                              @elseif ($doneEval[$employee->id]['evaluated'] && $doneEval[$employee->id]['isDraft'])
                              <p class="text-center"><a class="btn btn-sm btn-danger" href="{{action('EvalFormController@edit',$doneEval[$employee->id]['evalForm_id']) }} "><i class="fa fa-check"></i> Continue </a></p>

                              @else <p><a class="btn btn-sm btn-success" href="{{action('EvalFormController@newEvaluation', ['user_id'=>$employee->id, 'evalType_id'=>$evalSetting->id, 'currentPeriod'=>$doneEval[$employee->id]['startPeriod'], 'endPeriod'=>$doneEval[$employee->id]['endPeriod']]) }} "><i class="fa fa-check-square-o"></i> Evaluate Now</a>
                             
                           </p> 
                              @endif
                          
                           
                           <!--  <a href="#" class="btn btn-sm btn-flat btn-default"style="margin-bottom:5px"><i class="fa fa-pencil"></i> Edit</a> <div class="clearfix"></div>
                            -->

                        

                        </td>
                        
                      </tr>
                       @endif <!--end if self check -->
                      @endforeach
                      </tbody>

              </table>

            </div>



       </div>

      @endif

      @if (count($changedImmediateHeads) > 0 ) 


<!-- ******** FORMER MEMBERS ********** -->

      <div class="row">
        <div class="col-xs-12">

          <div class="box box-primary" style="margin-top:80px; background-color:#dedede; border:2px dotted #666">

           <div class="box-header"><h4 class="text-primary">Past Team Member Evaluation(s): </h4>
           </div>

           <div class="box-body">

            @if (count($changedImmediateHeads) < 8)

                <!-- ******** LESS THAN 8 ********** -->
                <div class="row">

                  <h4 class="text-center"><small>Former Team Members with Pending</small> <br/> {{$evalSetting->name}}<br /> 
                    <small class="text-black"><em></em></small><br /><br/></h4>

                  @if (count($changedImmediateHeads) == 0)

                  <h3 class="text-center text-success"><br /><br />No entries found.</h3>

                  @else
                  <?php $ctr=0; ?>
                  @foreach ($changedImmediateHeads as $employee)
                  @if($employee['user_id'] !== Auth::user()->id )
                  <div class="col-lg-3 col-sm-6 col-xs-12"><!-- && $doneEval[$employee['id']] !== null -->
                     <!-- Widget: user widget style 1 -->
                                <div class="box box-widget widget-user-2">
                                  <!-- Add the bg color to the header using any of the bg-* classes 
                                  done & agent = gray
                                  done & leader = darkgreen black
                                  !done & leader = black
                                  !done & agent = green

                                -->
                                  <div class="widget-user-header  @if ($doneMovedEvals[$ctr]['evaluated'] && ($employee['userType_id'] == '4') ) bg-gray @elseif ($doneMovedEvals[$ctr]['evaluated'] && ($employee['userType_id'] !== '4') ) bg-black @elseif (!$doneMovedEvals[$ctr]['evaluated'] && ($employee['userType_id'] !== '4') )bg-darkgreen  @else bg-green @endif">

                                    
                                      <div class="widget-user-image">
                                      <a href="{{action('UserController@show',$employee['id'])}}" class="text-primary"> 
                                       @if ( file_exists('public/img/employees/'.$employee['id'].'.jpg') )
                                     <img src="{{asset('public/img/employees/'.$employee['id'].'.jpg')}}" class="img-circle pull-left" alt="User Image" width="70" style="margin-top:-10px" >
                                      @else
                                        <img src="{{asset('public/img/useravatar.png')}}" class="img-circle pull-left" alt="Employee Image"  width="70" style="margin-top:-10px">

                                        @endif
                                     
                                      </a>
                                    </div>
                                    <!-- /.widget-user-image -->
                                    <h3 class="widget-user-username"> {{$employee['lastname'] }}, {{$employee['firstname']}} </h3>
                                    <h5 class="widget-user-desc"> {{$employee['position']}} </h5>
                                   
                                  </div>
                                  <div class="box-footer no-padding">
                                    
                                    <ul class="nav nav-stacked">
                                      <li><a href="#">Date Hired <span class="pull-right badge bg-gray">{{ date_format(date_create($employee['dateHired']), "M d, Y")  }} </span></a></li>
                                      <li><a href="#">Absences <span class="pull-right badge bg-red">N/A</span></a></li>
                                      <li><a href="#">Tardiness <span class="pull-right badge bg-red">N/A</span></a></li>
                                      <li><a href="#">Received Disciplinary Actions <span class="pull-right badge bg-red">N/A</span></a></li>

                                     

                                      @if ($doneMovedEvals[$ctr]['evaluated' ]&& $doneMovedEvals[$ctr]['score'] != 0)
                                      <li><a href="#">
                                        <span class="pull-left"><strong>Current Rating</strong> <br/>
                                          <small>
                                         
                                            {{date('M d, Y',strtotime($doneMovedEvals[$ctr]['startPeriod']))  }} to  {{date('M d, Y',strtotime($doneMovedEvals[$ctr]['endPeriod']))  }}</small></span><h3 class="pull-right text-danger">{{$doneMovedEvals[$ctr]['score']}} %</h3></a></li>
                                    

                                      @else

                                      
                                           <li><a href="#">
                                        <span class="pull-left">{{$evalSetting->name}} <br/>
                                          <small>
                                         
                                           Period covered: </small></span><h5 class="pull-right text-danger text-center"> {{date('M d, Y',strtotime($doneMovedEvals[$ctr]['startPeriod']))  }} <br/>to<br/> {{date('M d, Y',strtotime($doneMovedEvals[$ctr]['endPeriod']))  }} </h5></a></li>
                                    
                                    

                                      @endif

                                    </ul>

                                      
                                    @if ($doneMovedEvals[$ctr]['evaluated'] && !$doneMovedEvals[$ctr]['isDraft'] && $doneMovedEvals[$ctr]['score'] > 0 )
                                     <p class="text-center"><a class="btn btn-md btn-default" href="{{action('EvalFormController@show',$doneMovedEvals[$ctr]['evalForm_id']) }} "><i class="fa fa-check"></i> See Evaluation</a><br/>
                                     <!-- <a href="#"  style="margin-top:5px" class="btn btn-xs btn-flat btn-default" data-toggle="modal" data-target="#myModal{{$doneMovedEvals[$ctr]['evalForm_id']}}"><i class="fa fa-trash"></i> Delete</a> --><div class="clearfix"></div>

                                     @include('layouts.modals', [
                                'modelRoute'=>'evalForm.destroy',
                                'modelID' => $doneMovedEvals[$ctr]['evalForm_id'], 
                                'modelName'=>"Evaluation of: ". $employee['firstname']." ". $employee['lastname'], 
                                'modalTitle'=>'Delete', 
                                'modalMessage'=>'Are you sure you want to delete this?', 
                                'formID'=>'deleteEmployee',
                                'icon'=>'glyphicon-trash' ])


                                    </p>

                                    @elseif ($doneMovedEvals[$ctr]['isDraft'] == '1' && $doneMovedEvals[$ctr]['evaluated'] &&  $doneMovedEvals[$ctr]['score'] > 0 ) 
                                    <p class="text-center"><a class="btn btn-md btn-danger" href="{{action('EvalFormController@edit',$doneMovedEvals[$ctr]['evalForm_id']) }} "><i class="fa fa-check"></i> Continue Evaluating </a></p>
                             
                                    @else <p class="text-center"><a class="btn btn-md btn-success" href="{{action('EvalFormController@newEvaluation', ['user_id'=>$employee['id'], 'evalType_id'=>$evalSetting->id, 'currentPeriod'=> $doneMovedEvals[$ctr]['startPeriod'], 'endPeriod'=>$doneMovedEvals[$ctr]['endPeriod'] ]) }} "><i class="fa fa-check-square-o"></i> Evaluate Now</a></p> 
                                    @endif
                                  <div class="clearfix"></div>


                                  </div>
                                </div>
                                <!-- /.widget-user -->

                  </div><!--end employee card-->
                  @endif
                  <?php $ctr++; ?>
                  @endforeach

                  @endif <!--end else subordinates not 0 -->

                </div><!-- end row -->
                <!-- ******** END < 8 ********** -->

            @else
            <?php $ctr=0; ?>

                   <div class="row">

                        <h4 class="text-center"><small>Former Team Members with Pending</small> <br/> {{$evalSetting->name}}<br /> 
                    <small class="text-black"><em></em></small><br /><br/></h4>

                        <div class="col-lg-12">
                          <table class="table" id="myTeam" >
                            <thead >
                                  <tr>
                                    <th> </th>
                                    
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Evaluation Period</th>
                                    <th class="text-center">Rating</th>
                                    <th class="text-center">Action</th>
                                    
                                  </tr>
                                  </thead>
                                  <tbody>
                                    @foreach ($changedImmediateHeads as $employee)

                                    @if ($employee['id'] !== Auth::user()->id && $doneMovedEvals[$ctr] !== null)
                                  <tr id="row{{$employee['id']}}">
                                    <td class="text-center ">
                                      <a href="{{action('UserController@show',$employee['id'])}}">
                                     @if ( file_exists('public/img/employees/'.$employee['id'].'.jpg') )
                                     <img src="{{asset('public/img/employees/'.$employee['id'].'.jpg')}}" width='60' height='60' class="img-circle" alt="User Image"/> 
                                     @else
                                     <img src="{{asset('public/img/useravatar.png')}}" class="img-circle" width='60' height='60' alt="User Image"> 
                                     @endif
                                    </a>
                                  </td>
                                    
                                    <td>{{$employee['lastname']}}</td>
                                    <td>{{$employee['firstname']}}</td>
                                    <td>{{$employee['position']}}</td>
                                    <td>{{$employee['status']}}</td>
                                    <td>{{$doneMovedEvals[$ctr]['startPeriod']}} to {{$doneMovedEvals[$ctr]['endPeriod']}} </td>
                                    <td class="text-danger">
                                      @if ($doneMovedEvals[$ctr]['evaluated' ]&& $doneMovedEvals[$ctr]['score'] != 50)
                                      <h4 class="text-center">{{$doneMovedEvals[$ctr]['score']}} %</h4>
                                      @endif

                                    </td>
                                    <td>
                                      @if ($doneMovedEvals[$ctr]['evaluated'] && $doneMovedEvals[$ctr]['score'] != 50 )
                                           <p><a class="btn btn-sm btn-primary" href="{{action('EvalFormController@show',$doneMovedEvals[$ctr]['evalForm_id']) }} " style="margin-bottom:5px"><i class="fa fa-check"></i> See Evaluation</a>
                                            <!-- <a href="#"  style="margin-top:5px" class="btn btn-xs btn-flat btn-default" data-toggle="modal" data-target="#myModal{{$doneMovedEvals[$ctr]['evalForm_id']}}"><i class="fa fa-trash"></i> Delete</a> --><div class="clearfix"></div>
                                          </p>

                                          @include('layouts.modals', [
                                      'modelRoute'=>'evalForm.destroy',
                                      'modelID' => $doneMovedEvals[$ctr]['evalForm_id'], 
                                      'modelName'=>"Evaluation of: ". $employee['firstname']." ". $employee['lastname'], 
                                      'modalTitle'=>'Delete', 
                                      'modalMessage'=>'Are you sure you want to delete this?', 
                                      'formID'=>'deleteEmployee',
                                      'icon'=>'glyphicon-trash' ])


                                          @else <p><a class="btn btn-sm btn-success" href="{{action('EvalFormController@newEvaluation', ['user_id'=>$employee['id'], 'evalType_id'=>$evalSetting->id]) }} "><i class="fa fa-check-square-o"></i> Evaluate Now</a>
                                         
                                       </p> 
                                          @endif
                                      
                                       
                                       <!--  <a href="#" class="btn btn-sm btn-flat btn-default"style="margin-bottom:5px"><i class="fa fa-pencil"></i> Edit</a> <div class="clearfix"></div>
                                        -->

                                    

                                    </td>
                                    
                                  </tr>
                                   @endif <!--end if self check -->


                                    <?php $ctr++; ?>
                                  @endforeach
                                  </tbody>

                          </table>

                        </div>



                   </div>

            @endif





           </div><!--end div body-->

          </div><!--end box primary-->

           

        </div> <!--end col xs 12-->
      </div> <!--end former teams -->

<!-- ******** END FORMER MEMBERS ********** -->

@endif


                




       
     </section>
          



@endsection


@section('footer-scripts')

<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>



<!-- Page script -->
<script>
 $('#myTeam').DataTable({
    "scrollX": false,
    //"iDisplayLength": 25,
    "responsive": true,
    "lengthMenu": [[5, 20, 50, -1], [5, 20, 50, "All"]],
    "aoColumns": [
                  { "bSortable": false },
                  {"width":200},
                  {"width":200},
                  {"width":400},
                  {"width":100},
                  {"width":100},
                  {"width":90},
                  { "bSortable": false },
    ] 
    
   
   });


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