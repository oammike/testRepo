@extends('layouts.main')

@section('metatags')
<title>Update Employee | OAMPI Evaluation System</title>

<style type="text/css">
/* Sortable items */

.sortable-list {
  background: none; /* #fcedc6;*/
  list-style: none;
  margin: 0;
  min-height: 60px;
  padding: 10px;
}
.sortable-item {
  background-color: #fcedc6;
  
  cursor: move;
  
  font-weight: bold;
  margin: 2px;
  padding: 10px 0;
  text-align: center;
}

/* Containment area */

#containment {
  background-color: #FFA;
  height: 230px;
}


/* Item placeholder (visual helper) */

.placeholder {
  background-color: #ccc;
  border: 3px dashed #fcedc0;
  min-height: 150px;
  width: 180px;
  float: left;
  margin-bottom: 5px;
  padding: 45px;
}
</style>
@endsection

@section('content')


  <!-- Content Header (Page header) -->
    <section class="content-header">
      
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{action('UserController@index')}}"> All Employees</a></li>
        <li class="active">Update Employee</li>
      </ol>
    </section>

     <section class="content">
      

          <div class="row" style="margin-top:50px">
           
            

            <div class="col-lg-12"> <!-- ******************* LEFT COLUMN ***************** -->

              <div class="box box-default">

              <!-- TABLE: LEFT -->
                <div class="box-header">

                 
                  <h2 class="text-center"> <img class="text-center" src="{{asset('public/img/logo-transparent.png')}}" width="90" /></h2>
                  <h3 class="text-center"> Update Employee Data <br/></h3>
                  

                </div>
                <div class="box-body">
                  
                 
                   {{ Form::open(['route' => ['user.update', $personnel->id], 'method'=>'put','class'=>'col-lg-12', 'id'=> 'addEmployee' ]) }}
                  <table class="table" style="width:85%; margin: 5px auto">
                    <tr>
                      <td><label>Employee Name: </label><input type="text" name="firstname" id="firstname" value="{{$personnel->firstname}}" class="form-control required" />
                                        <div id="alert-firstname" style="margin-top:10px"></div>

                                         <input type="text" name="middlename" id="middlename"  value="{{$personnel->middlename}}" class="form-control required" />
                                         <div id="alert-middlename" style="margin-top:10px"></div>

                                        <input type="text" name="lastname" id="lastname" value="{{$personnel->lastname}}" class="form-control required" />
                                        <div id="alert-lastname" style="margin-top:10px"></div>

                          <br/>
                          <label>Position: </label>
                          <select class="form-control" name="position_id" id="position_id">
                            <option value="0">- Select job title - </option>

                            @foreach ($positions as $pos)
                            <option value="{{$pos->id}}" @if ($pos->id == $personnel->position_id) selected="selected" @endif>{{$pos->name}} </option>

                            @endforeach
                            <option value="-1">** <em>add new position</em> ** </option>
                          </select>

                          <div id="newpos"></div>

                          <br/><br/>
                          

                          <label>Department / Program: </label>
                          <select class="form-control" name="campaign_id" id="campaign_id">
                            <option value="0">- Select one - </option>

                            @foreach ($campaigns as $campaign)
                            <option value="{{$campaign->id}}" @if ($campaign->id == $personnel->campaign[0]->id) selected="selected" @endif>{{$campaign->name}} </option>

                            @endforeach


                          </select><div id="alert-campaign" style="margin-top:10px"></div>  

                          <div id="newTeam">
                            <label>Immediate Supervisor: </label>
                            <select name='immediateHead_Campaigns_id' id='immediateHead_Campaigns_id' class='form-control'  style="text-transform:uppercase">
                              <option value='0'> -- Select Leader -- </option>
                              @foreach ($leaders as $tl)
                              <option value="{{$tl['id']}}" @if ($tl['id'] == $personnelTL_ihCampID) selected="selected" @endif><span style="text-transform:uppercase"> {{$tl['lastname']}}, </span>{{$tl['firstname']}} </option>
                              @endforeach
                               </select><br/><div id='alert-immediateHead'></div>

                          </div>  

                      </td>
                      <td><label>Employee Number: </label> <input type="text" class="form-control required" name="employeeNumber" id="employeeNumber" value="{{$personnel->employeeNumber}}" /> 
                         <div id="alert-employeeNumber" style="margin-top:10px"></div>

                         <label class="pull-left">OAMPI E-mail: &nbsp;&nbsp; </label> <input type="text" style="width:200px;" class="form-control required pull-left" name="oampi" id="oampi" value="{{$personnel->email}}" />
                         <div id="alert-email" style="margin-top:10px"></div>

                          <div class="clearfix" style="margin-top:215px"></div>
                         <label>Floor location: </label>
                          <select class="form-control" name="floor_id" id="floor_id" style="width:50%">
                            <option value="0">- Select one - </option>

                            @foreach ($floors as $floor)
                            <option value="{{$floor->id}}" @if ($floor->id == $personnel->floor[0]->id) selected="selected" @endif>{{$floor->name}} </option>

                            @endforeach

                          </select><div id="alert-floor" style="margin-top:10px"></div> 



                       </td>

                    </tr>

                    <tr>
                      <td><label>Date Hired: </label> <input required type="text" class="form-control datepicker" style="width:50%" name="dateHired" id="dateHired" value="{{date('m/d/Y',strtotime($personnel->dateHired) ) }} " />
                       <div id="alert-dateHired" style="margin-top:10px"></div></td>
                      <td><label>Date Regularized: </label> <input type="text" class="form-control datepicker" style="width:50%" name="dateRegularized" id="dateRegularized" <?php  if ( $personnel->dateRegularized !== null ) { ?> value="{{date('m/d/Y',strtotime($personnel->dateRegularized) ) }}" <?php } else {?> placeholder="specify date" <?php }; ?>   /> 
                      <div id="alert-dateRegularized" style="margin-top:10px"></div></td>

                    </tr>

                    <tr>
                      <td><label>System User Type: </label>
                        <div id="alert-userType" style="margin-top:10px"></div>
                        @foreach ($userTypes as $type)

                        <label> <input type="radio" name="userType_id" required value="{{$type->id}}" @if ($type->id == $personnel->userType_id) checked="checked" @endif /> {{$type->name}} </label><br/>

                        @endforeach
                        
                       
                      </td>

                      <td style="padding-top:0px">
                        
                        <label>Employment Status: </label>
                        <div id="alert-status" style="margin-top:10px"></div>
                        @foreach ($statuses as $status)

                        <label> <input type="radio" name="status_id" required value="{{$status->id}}"@if ($status->id == $personnel->status_id) checked="checked" @endif /> {{$status->name}} </label><br/>

                        @endforeach
                      

                         
                      </td>

                    </tr>

                    

                   

                  </table><p>&nbsp;</p>

               
                  <p class="text-center"> 
                   
                    <input name="name" id="name" type="hidden" />
                    <input name="email" id="email" type="hidden" value="{{$personnel->email}} "/>
                    <input name="password" id="password" type="hidden"/>
                    
                    <a href="{{action('UserController@index')}} " class="btn btn-md btn-default"><i class="fa fa-reply"></i> Back to all employees</a>
                    <input type="submit" class="btn btn-md btn-success " name='submit' value="Save" />
                    
                    <div id="alert-submit" style="margin-top:20px"></div>
                  </p>


                  
                 

                  
                 </form>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix" style="background:none">
                 
                  
                </div>
                <!-- /.box-footer -->
              </div><!-- /.box -->
            </div><!--end left -->


           


            
           

          
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

   //$('#changeNotice').validate();
   $("select[name='position_id']").on('change', function(){
     var pos =  $(this).find(':selected').val();

     if (pos == "-1"){ //add new position
      var htmcode = "<br/><input required type='text' class='form-control' name='newPosition' id='newPosition' placeholder='Enter new job position' value='' />";

        $('#newpos').html(htmcode);
        console.log(htmcode);

     }
     
   });

   
    $("select[name='campaign_id']").on('change', function(){
                  var camp = $(this).find(':selected').val();

                  if (camp !== 0){
                    var _token = "{{ csrf_token() }}";

                     $.ajax({
                      url:"{{url('/')}}/campaign/"+camp+"/leaders",
                      type:'GET',
                      data:{id:camp, _token:_token},
                      error: function(response)
                      {
                         
                        
                        console.log("Error leader: "+response.id);

                          
                          return false;
                      },
                      success: function(response)
                      {
                        var htmlcode2 = "<label>Immediate Supervisor: </label><select name='immediateHead_Campaigns_id' id='immediateHead_Campaigns_id' class='form-control'>";
                        htmlcode2 += "<option value='0'> -- Select Leader -- </option>";
                        $.each(response, function(index) {

                          htmlcode2 +="<option value='"+response[index].id+"'>"+response[index].lastname+", "+response[index].firstname+"</option>";
                        
                        }); //end each

                        htmlcode2 +="</select><br/><div id='alert-immediateHead'></div>";

                        $('#newTeam').html(htmlcode2);



                      }//end success

                      }); //end ajax
                    }//end if

    }); //end select on change





   $('#addEmployee').on('submit', function(e) {

    e.preventDefault();
    console.log('Enter submit');
      var _token = "{{ csrf_token() }}";
      var userType_id = $('input[name="userType_id" ]:checked').val();
      var firstname = $('#firstname').val();
      var middlename = $('#middlename').val();
      var lastname = $('#lastname').val();
      var status_id = $('input[name="status_id" ]:checked').val();
      var username = "{{$personnel->name}}";
      var employeeNumber = $('#employeeNumber').val();
      var campaign_id = $('select[name="campaign_id"]').find(':selected').val();
      var floor_id = $('select[name="floor_id"]').find(':selected').val();
     
      var email = $("#oampi").val();

      var immediateHead_Campaigns_id = $('select[name="immediateHead_Campaigns_id"]').find(':selected').val();
      var position_id = $('select[name="position_id"]').find(':selected').val();
      var dateHired = $('#dateHired').val();
      var dateRegularized = $('#dateRegularized').val();

      var alertCampaign = $('#alert-campaign');
      var alertImmediateHead = $('#alert-immediateHead');
      var alertPosition = $('#alert-position');
      var alertDateHired = $('#alert-dateHired');
      var alertStatus = $('#alert-status');
      var alertUserType = $('#alert-userType');

     

     
      

     

      if (!validateRequired(campaign_id,alertCampaign,"0")) { 
        console.log('not valid Program '+campaign_id); e.preventDefault(); e.stopPropagation();
        return false;
      } else if (!validateRequired(immediateHead_Campaigns_id,alertImmediateHead,"0")){ 
        console.log('not valid Head'); e.preventDefault(); e.stopPropagation(); return false;
      } else if (!validateRequired(position_id,alertPosition,"0")){ 
          console.log('not valid Position '+position_id); e.preventDefault(); e.stopPropagation();return false;
      } else if (position_id == "-1"){

          var newPos = $('input[name="newPosition"]').val();
          if (!validateRequired(newPos,alertPosition,"Enter new job position"))
          {
            console.log('not valid PosValue: '+newPos); e.preventDefault(); e.stopPropagation(); return false; 
          } else {
            //save first the new position
            console.log("save pos first");
              $.ajax({
                                url:"{{action('PositionController@store')}} ",
                                type:'POST',
                                data:{
                                  'name': newPos,
                                  _token:_token},

                                error: function(response)
                                { console.log("Error saving position: "); return false;
                                },
                                success: function(response)
                                {
                                  var posID = response.id;
                                  console.log("Saved position");

                                  //check the rest of the form
                                  if( !validateRequired(dateHired,alertDateHired,"") || !validateRequired(status_id,alertStatus,"") || !validateRequired(userType_id,alertUserType,"")){
                                    e.preventDefault(); e.stopPropagation(); return false; 

                                  } else {

                                    //save employee
                                    console.log("Save employee then");
                                    
                                    saveEmployee(firstname,middlename,lastname,employeeNumber,email,dateHired,dateRegularized, userType_id,status_id,posID,campaign_id,floor_id,immediateHead_Campaigns_id, _token);

                                  }



                                }//end success

                    }); //end ajax

          
          }//end else valid new position

      } else {

           if( !validateRequired(dateHired,alertDateHired,"") || !validateRequired(status_id,alertStatus,"") || !validateRequired(userType_id,alertUserType,""))
           {
             e.preventDefault(); e.stopPropagation(); return false; 
           } else {
           
            

            setTimeout(saveEmployee(firstname,middlename,lastname,employeeNumber,email,dateHired,dateRegularized, userType_id,status_id,position_id,campaign_id,floor_id,immediateHead_Campaigns_id, _token ),1);
            
            
         }

       } //end else if -1
      
     
      return false;
    }); //end addEmployee





   $( ".datepicker" ).datepicker();

       

});


function saveEmployee(firstname,middlename,lastname,employeeNumber,email,dateHired,dateRegularized, userType_id,status_id,position_id,campaign_id,floor_id,immediateHead_Campaigns_id, _token){

   //save movement
   console.log("Enter function");

   $.ajax({
            url:"{{action('UserController@update', $personnel->id)}}",
            type:'PUT',
            data:{

              
              'firstname': firstname,
              'middlename': middlename,
              'lastname': lastname,
              'employeeNumber': employeeNumber,
              'email': email,
              'dateHired': dateHired,
              'dateRegularized': dateRegularized,
              'userType_id': userType_id,
              'status_id': status_id,
              'position_id': position_id,
              'campaign_id': campaign_id,
              'floor_id':floor_id,
              'immediateHead_Campaigns_id': immediateHead_Campaigns_id,
              '_token':_token
            },

           
            success: function(response2)
            {
              console.log(response2);
               $('input[name="submit"').fadeOut();
                      var htmcode = "<span class=\"success\"> <i class=\"fa fa-save\"></i> Employee  data updated. <br /><br/>";
                     
                      htmcode += "<a href=\"{{action('UserController@show',$personnel->id)}}\" class='btn btn-sm btn-default text-black pull-right'><i class='fa fa-reply'></i> Back to Employee Profile </a> <br/><br/>";
                      
                      $('#alert-submit').addClass('alert alert-success').fadeIn().html(htmcode); 

            }
          });

return false;


   

}

function validateRequired(param, availability, defaultval) {

        
        if (param == null){

          availability.addClass('alert alert-danger').fadeIn();
            availability.html('<span class="success"> <i class="fa fa-warning"></i> This field is required. </span>');  
             return false;
        }

        else if(param.length <= 0 || param === defaultval) { 
            
            availability.addClass('alert alert-danger').fadeIn();
            availability.html('<span class="success"> <i class="fa fa-warning"></i> This field is required. </span>');   
             return false;         
            

        } else{
            availability.removeClass();
            availability.html('');
            return true;
                      
        }
       

}

   


   

 
</script>
<!-- end Page script -->

<!-- <script type="text/javascript" src="{{asset('public/js/jquery.validate.js')}}"></script> -->

@stop