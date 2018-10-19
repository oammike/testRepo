@extends('layouts.main')


@section('metatags')
  <title>{{$user->firstname}}'s Profile</title>
    <meta name="description" content="profile page">

@stop


@section('content')




<section class="content-header">

      <h1>Overtime Request<small></small>
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
                          <div class="box box-primary">

                            <div class="box-body box-profile">
                               <!-- Widget: user widget style 1 -->
                                    <div class="box box-widget widget-user">
                                      <!-- Add the bg color to the header using any of the bg-* classes -->
                                      <div class="widget-user-header bg-black" style="background: url('{{URL:: asset("public/img/bg.jpg")}}') top left;">
                                        <h3 style="text-shadow: 1px 2px #000000; text-transform:uppercase" class="widget-user-username">{{$user->lastname}}, {{$user->firstname}} {{$user->middlename}}</h3>
                                        <h5 style="text-shadow: 1px 2px #000000;"  class="widget-user-desc">{{$user->position->name}} </h5>
                                      </div>
                                      <div class="widget-user-image"><a  class="user-image"href="{{action('UserController@show',$user->id)}}"><img  width="90" src="{{$profilePic}}" class="user-image" alt="User Image"></a></div>
                                      <div class="box-footer">
                                        <div class="row">
                                          <div class="col-sm-6">
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
                                          <div class="col-sm-6 ">
                                            <div class="description-block">
                                              <p class="description-header"><i class="fa fa-envelope-o margin-r-5"></i> E-mail:</p>
                                              <span><a href="mailto:{{$user->email}}"> {{$user->email}}</a></span>
                                            </div>
                                            <!-- /.description-block -->
                                          </div>
                                          <!-- /.col -->

                                          <!-- START CUSTOM TABS -->
     

                                          <p>&nbsp;</p><p>&nbsp;</p>
                                          <div class="row">
                                            <div class="col-lg-1 col-sm-12"></div>
                                            <div class="col-lg-10 col-sm-12">

                                              <a href="{{action('DTRController@show', $user->id)}}" class="btn btn-flat btn-sm btn-default pull-right"><i class="fa fa-calendar"></i> View DTR</a>
                                              <h4><i class="fa fa-clock-o"></i> Overtime<br/><br/><br/></h4>

                                              <table class="table">
                                                 {{ Form::open(['route' => ['user_ot.update', $OT->id], 'method'=>'put','class'=>'col-lg-12', 'id'=> 'editOT' ]) }}
                                                <tr>
                                                  <th>Date Requested</th>
                                                  <th>Production Date</th>
                                                  <th style="width:45%">OT details</th>
                                                  <th></th>
                                                </tr>
                                                <tr>
                                                  <td>{{$OT->created_at}} </td>
                                                  <td>{{$details[0]['productionDate']}} </td>

                                                 
                                                  <td>

                                                    <strong>Billable hours:  &nbsp;</strong> {{$details[0]['billableHours']}}<br/>
                                                    <strong class="text-danger">Filed OT hours: {{$details[0]['filedHours']}}</strong><br/>
                                                    Time: <strong>{{$details[0]['timeStart']}} - {{$details[0]['timeEnd']}} </strong> <br/>
                                                    <strong>Reason: </strong> <em> <small>{{$details[0]['reason']}}</small></em></td>

                                                  @if (is_null($OT->isApproved))
                                                  <td>
                                                    <a href="#" id="approve" data-action="1" class="updateCWS btn btn-sm btn-success"><i class="fa fa-thumbs-up"></i> Approve</a>
                                                    <a href="#" id="reject" data-action="0" class="updateCWS btn btn-sm btn-danger"><i class="fa fa-thumbs-down"></i> Deny</a>

                                                  </td>
                                                  @else

                                                  <td>
                                                    @if($OT->isApproved) <h4 class="text-success">Approved</h4>
                                                    @else <h4 class="text-danger">Denied</h4>@endif

                                                  </td>


                                                  @endif
                                                </tr>
                                                {{Form::close()}}
                                              </table><br/><br/><br/><br/>
                                              <div id="alert-submit"></div>
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
<script>
$(function () {


    $('.updateCWS').on('click', function(e)
    {
      e.preventDefault(); e.stopPropagation();
      var _token = "{{ csrf_token() }}";
      var cwsAction = $(this).attr('data-action');

      $.ajax({
                url: "{{action('UserOTController@update', $OT->id)}}",
                type:'PUT',
                data:{

                  
                  'id': {{$OT->id}},
                  'isApproved': cwsAction,
                  '_token':_token
                },

               
                success: function(response)
                {
                  console.log(response);
                  $('.table').fadeOut();
                  var htmcode = "<span class=\"success\"> <i class=\"fa fa-save\"></i> OT request updated. <br /><br/>";
                  $('#alert-submit').addClass('alert alert-success').fadeIn().html(htmcode).delay(2000).fadeOut(function()
                  { 
                    window.location = "{{action('DTRController@show', $user->id)}}";
                  }); 
                }
      });


    });


  });
</script>
@stop