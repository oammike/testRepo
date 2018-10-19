@extends('layouts.main')


@section('metatags')
  <title>{{$user->firstname}}'s Profile</title>
    <meta name="description" content="profile page">
    <link rel="stylesheet" href="{{URL::asset('public/css/coverphoto.css')}}" />
   
    <style type="text/css">
    .ui-draggable {cursor: move; }
    .coverphoto, .output {
      max-width: 1024px;
      height: auto;
      border: 1px solid black;
      /*margin: 10px auto;*/
    }
  </style>


@stop


@section('content')




<section class="content-header">

      <h1>
      Employee Profile
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        
        <li class="active">Employee Profile</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-1"></div>
        <div class="col-xs-10">
          


          <!-- Profile Image -->
                          <div class="box box-primary" >

                            <div class="box-body box-profile"style="max-width:1024px; margin: 0 auto">
                               <!-- Widget: user widget style 1 -->
                                    <div class="box box-widget widget-user">

                                      <div class="widget-user-image-profilepage" style="z-index:100">
                                        
                                         @if ( file_exists('public/img/employees/'.$user->id.'.jpg') )
                                          <img src="{{asset('public/img/employees/'.$user->id.'.jpg')}}" class="user-image" alt="User Image">
                                          @else
                                          <img src="{{asset('public/img/useravatar.png')}}" class="user-image" alt="User Image">

                                            @endif

                                          
                                        
                                      </div>
                                      
                                       

                                      <!-- Add the bg color to the header using any of the bg-* classes -->
                                      @if ($user->hascoverphoto !== null)
                                      <?php $cover = URL::to('/') . "/storage/uploads/cover-".$user->id."_".$user->hascoverphoto.".png";// URL::asset("public/img/cover/".$user->id.".jpg"); ?>
                                      <div class="coverphoto output widget-user-header-profilepage bg-black" style="background: url('{{$cover}}') left no-repeat; background-size:1024px auto">
                                      @else
                                      <div class="coverphoto output widget-user-header-profilepage bg-black" style="background: url('{{URL:: asset("public/img/cover/bg.jpg")}}') left no-repeat; background-size:1024px auto">
                                      @endif

                                       <input type="hidden" name="coverimg" id="coverimg" value="" />

                                      
                                      <h3 style="text-shadow: 1px 2px #000000; text-transform:uppercase;z-index:100" class="widget-user-username-profilepage">
                                          {{$user->lastname}}, {{$user->firstname}} {{$user->middlename}} <br/>
                                          <span style="text-shadow: 1px 2px #000000; font-weight:bold"  class="widget-user-desc-profilepage">{{$user->position->name}} </span> </h3>

                                      
                                        
                                      </div>

                                      <div id="alert-submit" class="text-right" style="margin-top:10px"></div>
                                      
                                      <div class="box-footer">
                                        <div class="row">
                                           <div class="col-sm-3"></div>
                                           

                                          <div class="col-sm-3">
                                            <div class="description-block" >
                                              <p class="description-header" style="font-size:smaller"><i class="fa fa-users margin-r-5"></i> Department / Program : </p>
                                              <span class="description-text text-primary">
                                             
                                              {{$camps}}

                                              
                                              </span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->
                                          <div class="col-sm-4">
                                            <div class="description-block">
                                              <p class="description-header" style="font-size:smaller"><i class="fa fa-envelope-o margin-r-5"></i> E-mail:</p>
                                              <span><a href="mailto:{{$user->email}}"> {{$user->email}}</a></span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->

                                          <div class="col-sm-2"><a href="{{action('DTRController@show',$user->id)}}" class="btn btn-flat btn-primary pull-right btn-sm"><i class="fa fa-calendar"></i> View Daily Time Record</a></div>

                                          <!-- START CUSTOM TABS -->
     

                                          <p>&nbsp;</p><p>&nbsp;</p>
                                          <div class="row">
                                            <div class="col-lg-1 col-sm-12"></div>
                                            <div class="col-lg-10 col-sm-12">

                                              
                                              <br/><br/>
                                              <div class="clearfix"></div>
                                              <!-- Custom Tabs -->
                                              <div class="nav-tabs-custom">
                                                <ul class="nav nav-tabs">
                                                  <li class="active"><a href="#tab_1" data-toggle="tab">EMPLOYMENT DATA</a></li>
                                                  <li><a href="#tab_2" data-toggle="tab">WORK SCHEDULE </a></li>
                                                  <!-- <li><a href="#tab_3" data-toggle="tab">CONTACT INFO</a></li> -->
                                                 
                                                
                                                  
                                                </ul>
                                                <div class="tab-content">
                                                  <div class="tab-pane active" id="tab_1">
                                                    <div class="row" > 
                                                      <div class="clearfix" style="padding-top:30px;">&nbsp;</div>
                                                      <div class="col-xs-1"></div>
                                                      <div class="col-xs-10">
                                                         <div class="text-left col-xs-12" style="border-bottom: solid 1px #eee; padding-bottom:15px;">

                                                         <div class="text-left col-xs-12" style="border-bottom: solid 1px #eee; padding-bottom:15px;">
                                                          <strong><i class="fa fa-smile-o margin-r-5"></i> Employment Status : </strong>
                                                           {{$user->status->name}}</div>

                                                           


                                                           <div class="text-left col-xs-12" style="border-bottom: solid 1px #eee; padding-bottom:15px;">
                                                          <strong><i class="fa fa-calendar margin-r-5"></i> Date Hired : </strong>
                                                           {{date("M d, Y", strtotime($user->dateHired)) }}</div>



                                                        

                                                            <div class="text-left col-xs-12" style="border-bottom: solid 1px #eee; padding-bottom:15px;">
                                                            <strong><i class="fa fa-street-view margin-r-5"></i> Immediate Supervisor: </strong>
                                                           {{$immediateHead->firstname}} {{$immediateHead->lastname}}




                                                            </div>


                                                          <div class="clearfix"></div>
                                                         
                                                  
                                                          <br />

                                                          
                                                          

                                                         
                                                         

                                                         

                                                          </div> 
                                                          <div class="col-xs-1"></div>
                                                      </div>


                                         


                                                      </div>
                                                      <!-- /.row -->
                                                    

                                                  </div><!--end pane1 -->
                                                  <!-- /.tab-pane -->



                                                  <div class="tab-pane" id="tab_2">
                                                    

                                                    @if (empty($workSchedule))
                                                    <h3 class="text-center text-primary"><br/><br/><i class="fa fa-clock-o"></i>&nbsp;&nbsp; No Work Schedule defined</h3><p class="text-center"><small>Kindly inform HR or immediate head to plot {{$user->firstname}}'s  work schedule.</small><br/><br/><br/>
                                                    <a href="{{action('UserController@createSchedule', $user->id)}}" class="btn btn-md btn-success"><i class="fa fa-calendar"></i> Plot Schedule Now</a></p>
                                                    
                                                    @else
                                                    <h4 style="margin-top:30px"><i class="fa fa-clock-o"></i> WORK SCHEDULE <br/><br/></h4>

                                                    <div class="row">
                                                      <div class="col-lg-2"></div>
                                                      <div class="col-lg-8">
                                                          <table class="table">
                                                         

                                                          @if ($workSchedule[0]['type'] == "shifting")
                                                          <tr>
                                                            <th style="width:60%">Work Days</th>
                                                            <th style="width:40%" class="text-center">Rest Days</th>
                                                          </tr>
                                                          <tr>
                                                            <td>

                                                              @foreach($workSchedule[0]['workDays'] as $wd)

                                                              
                                                              <span class="pull-left">{{ date('M d, Y - D', strtotime($wd->productionDate))}} </span><span class="pull-right"> {{ date('h:i A', strtotime('1999-01-01'.$wd->timeStart)) }} - {{ date('h:i A', strtotime('1999-01-01'.$wd->timeEnd)) }}</span><br/>

                                                              @endforeach

                                                            </td>

                                                            <td class="text-center">
                                                              @foreach($workSchedule[0]['RD'] as $rd)

                                                              
                                                              {{ date('M d, Y - D', strtotime($rd->productionDate))}}<br/>

                                                              @endforeach

                                                            </td>
                                                          </tr>


                                                          @else

                                                           <tr>
                                                              <th style="width:60%">Work Days</th>
                                                              <th style="width:40%" class="text-center">Rest Days</th>
                                                           </tr>
                                                           <tr>
                                                              <td>

                                                                @foreach($workSchedule[0]['workDays'] as $wd)

                                                                
                                                                <span class="pull-left">{{ jddayofweek($wd->workday,1)}} </span><span class="pull-right"> {{ date('h:i A', strtotime('1999-01-01'.$wd->timeStart)) }} - {{ date('h:i A', strtotime('1999-01-01'.$wd->timeEnd)) }}</span><br/>

                                                                @endforeach

                                                              </td>

                                                              <td class="text-center">
                                                                @foreach($workSchedule[0]['RD'] as $rd)

                                                                
                                                                {{ jddayofweek($rd->workday,1) }}<br/>

                                                                @endforeach

                                                              </td>
                                                            </tr>
                                                          @endif

                                                        </table>
                                                      </div>
                                                      <div class="col-lg-2"></div>
                                                  </div>

                                                    @endif

                                                    <?php /*
                                                    <div class="row">
                                                      <div class="col-lg-7"><strong> Shifts: </strong><br/>

                                                        @if(count($user->schedules) >=1 )
                                                            @if ($user->schedules->first()->isFlexi)
                                                            <h4>Flexi sched</h4>

                                                            @else

                                                           <table class="table no-border">
                                                                @foreach($user->schedules as $sched)
                                                                <tr>
                                                                 <td>{{$sched->workday}}</td> 
                                                                 <td>{{ date('h:i A', strtotime('1999-01-01'.$sched->timeStart)) }} - {{ date('h:i A', strtotime('1999-01-01'.$sched->timeEnd)) }} </td>
                                                               </tr>
                                                                @endforeach
                                                           </table>



                                                            @endif

                                                        @else

                                                         <!-- <table class="table no-border">
                                                              @foreach($user->schedules as $sched)
                                                              <tr>
                                                               <td>{{$sched->workday}}</td> 
                                                               <td>{{ date('h:i A', strtotime('1999-01-01'.$sched->timeStart)) }} - {{ date('h:i A', strtotime('1999-01-01'.$sched->timeEnd)) }} </td>
                                                             </tr>
                                                              @endforeach
                                                         </table> -->

                                                        @endif
                                                       

                                                         </div>
                                                      <div class="col-lg-3"><strong> Rest Days: </strong>
                                                      <br/><br/>
                                                        @foreach($user->restdays as $sched)
                                                         {{$sched->RD}} <br/>
                                                        @endforeach</div>

                                                      <div class="col-lg-2"><label>Flexible schedule: @if(count($user->schedules) >0 ) @if($user->schedules->first()->isFlexi) YES @else NO @endif @else N/A  @endif</label> </div>

                                                       
                                                    </div>

                                                    */ ?>
                                                    
                                                   

                                                  </div>
                                                  <!-- /.tab-pane -->



                                                  <div class="tab-pane" id="tab_3">
                                                    <h4 style="margin-top:30px"><i class="fa fa-pencil"></i> CONTACT INFORMATION <br/><br/></h4>
                                                    <div class="row">
                                                      <div class="col-lg-4"><strong> Current Address: </strong><br/>
                                                        {{$user->currentAddress1}} <br/>
                                                        {{$user->currentAddress2}}<br/>
                                                        {{$user->currentAddress3}}</div>
                                                      <div class="col-lg-4"><strong> Permanent Address: </strong>
                                                      <br/>
                                                        {{$user->permanentAddress1}} <br/>
                                                        {{$user->permanentAddress2}}  <br/>
                                                        {{$user->permanentAddress3}} </div>

                                                        <div class="col-lg-4"><strong><i class="fa fa-mobile"></i> Mobile Number: </strong>
                                                        {{$user->mobileNumber}}  <br/><br/>
                                                        <strong><i class="fa fa-phone"></i> Telephone Number: </strong>{{$user->phoneNumber}} <br/>
                                                        </div>
                                                    </div>
                                                  </div>
                                                  <!-- /.tab-pane -->
                                                </div>
                                                <!-- /.tab-content -->
                                              </div>
                                              <!-- nav-tabs-custom -->
                                            </div>
                                            <!-- /.col -->
                                            <div class="col-lg-1 col-sm-12"></div>

                                           
                                          </div>
                                          <!-- /.row -->
                                          <!-- END CUSTOM TABS -->
                                          
                                          <!-- /.col -->
                                          

                                      </div>
                                    </div>
                                    <!-- /.widget-user -->

                            </div>
                            

                            <!-- /.box-body -->
                          </div>

        </div>
        <div class="col-xs-1"></div>

      </div>

     

    </section>

@stop

@section('footer-scripts')





@stop