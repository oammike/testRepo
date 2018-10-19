@extends('layouts.main')

@section('metatags')
<title>Edit Personnel Change Notice | OAMPI Evaluation System</title>

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
      <h1>
        Personnel Change Notice
        <small>{{$personnel->firstname}} {{$personnel->lastname}} </small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{action('MovementController@index')}}"> All Movements</a></li>
        <li class="active">{{$personnel->firstname}} {{$personnel->lastname}}</li>
      </ol>
    </section>

     <section class="content">
      @if (Auth::user()->userType_id == 1 || Auth::user()->userType_id == 2 ) 
      <a href="{{action('ImmediateHeadController@create')}} " class="btn btn-sm btn-default btn-flat pull-right"><i class="fa fa-users"></i> Add New Leader</a>
     
      @endif

          <div class="row" style="margin-top:50px">
           
            

            <div class="col-lg-12"> <!-- ******************* LEFT COLUMN ***************** -->

              <div class="box box-default">

              <!-- TABLE: LEFT -->
                <div class="box-header">

                 
                  <h2 class="text-center"> <img class="text-center" src="{{asset('public/img/logo-transparent.png')}}" width="90" /></h2>
                  <h3 class="text-center"> Personnel Change Notice <br/><br/><br/></h3>
                  

                </div>
                <div class="box-body">
                  <form name="changeNotice" id="changeNotice" method="POST"> 
                  <table class="table" style="width:85%; margin: 5px auto">
                    <tr>
                      <td>Employee Name: <h4>{{$personnel->firstname}} {{$personnel->lastname}}</h4> </td>
                      <td>Employee Number:<h4>{{$personnel->employeeNumber}}  </h4>  </td>

                    </tr>

                    <tr>
                      <td><h4>Date Requested: </h4> <input required type="text" class="form-control datepicker" style="width:50%" name="requestDate" id="requestDate" value="{{date('m/d/Y', strtotime($movement->dateRequested))}} " />
                       <div id="alert-requestDate" style="margin-top:10px"></div></td>
                      <td><h4>Effectivity Date: </h4> <input required type="text" class="form-control datepicker" style="width:50%" name="effectivityDate" id="effectivityDate" value="{{date('m/d/Y', strtotime($movement->effectivity))}}  " /> 
                      <div id="alert-effectivityDate" style="margin-top:10px"></div></td>

                    </tr>

                    <tr>
                      <td>
                        <h4><br/><br/>Reason for Change: <br/><br/></h4>
                        
                       
                      </td>

                      <td style="padding-top:50px">
                        <div id="alert-reason" style="margin-top:10px"></div>
                        @foreach ($movementTypes as $type)
                        <label> <input disabled="disabled" type="radio" name="reason" required value="{{$type->id}}" @if ($movement->personnelChange_id ==$type->id) checked="checked" @endif /> @if ($movement->personnelChange_id == $type->id)<span class="text-success">@endif  {{$type->name}} @if ($movement->personnelChange_id == $type->id)</span>@endif</label><br/>
                        

                        @endforeach
                        
                        
                         
                      </td>

                    </tr>

                     <tr>
                      <td>
                        <h4><br/><br/>Details: <br/><br/></h4>
                      </td>
                      <td>
                        <table class="table"> 
                          <tr>
                            <th class="text-center col-sm-6" >From</th>
                            <th class="text-center col-sm-6" width="350" >To</th>

                          </tr>
                          

                          <tr id="details">
                            @if ($movement->personnelChange_id == "1")
                            <td>Program: <strong>{{$personnel->campaign[0]->name}} </strong></td>
                            <td><select name="program" id="program" class="choices form-control">
                              
                              @foreach ($campaigns as $c)<option @if ($hisNew->id == $c->id) selected="selected" @endif value="{{$c->id}}">{{$c->name}} </option> @endforeach</select>
                              <br/><div id='alert-program'></div>
                              <div id='newTeam'>
                                  <br/><strong>Immediate Supervisor</strong>
                                  <select name='newHead' id='newhead' class='form-control'>
                                    <option value='{{$hisNewIDvalue}}'> -- Select Leader -- </option>
                                    @foreach ($TLset as $tl)
                                    <option value="{{$tl->id}}" @if ($hisNewIDvalue == $tl->id) selected="selected" @endif>{{$tl->lastname}}, {{$tl->firstname}} </option>

                                    @endforeach
                                  </select><br/><div id='alert-newHead'></div>

                                  <br/><br/><strong>Floor</strong>
                                  <select name='newFloor' id='newFloor' class='form-control'>
                                  <option value='0'> -- Select Floor -- </option>

                                    @foreach ($floors as $floor)
                                    <option value='{{$floor->id}}' @if($floor->id == $hisFloor->id) selected="selected" @endif>{{$floor->name}}</option>

                                    @endforeach
                                  </select><br/><div id='alert-newFloor'></div>

                              </div>

                              

                            </td>

                            @endif

                            @if ($movement->personnelChange_id == "2")
                            <td>Position: <strong>{{$personnel->position->name}} </strong></td>
                            <td><select name="position" id="position" class="choices form-control">
                              <option value=\"0\">Select New Job Position</option>
                              
                              @foreach ($positions as $c)
                                @if ($c->id !== $personnel->position_id)
                                <option @if ($hisNew->id == $c->id) selected="selected" @endif value="{{$c->id}}">{{$c->name}} </option>

                                @endif 
                              @endforeach
                              <option value="-1">** <em>add new position</em> ** </option>
                              </select>
                              <br/><div id='alert-position'></div><div id='newPosition'></div></td>

                            @endif



                            @if ($movement->personnelChange_id == "3")
                            <td>Status: <strong>{{$personnel->status->name}} </strong></td>
                            <td><select name="status" id="status" class="choices form-control">
                              
                              @foreach ($statuses as $c)
                                @if ($c->id !== $personnel->status_id)
                                <option @if ($hisNew->id == $c->id) selected="selected" @endif value="{{$c->id}}">{{$c->name}} </option> 

                                @endif
                              @endforeach</select>
                              <br/><div id='alert-status'></div><div id='newStatus'></div></td>

                            @endif
                            
                          </tr>

                        </table>
                        

                      </td>

                    </tr>

                    <tr>
                      <td colspan="2"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
                        <p>Please indicate your conformity by signing in the space provided below. 
                          Your signature attests to the fact that you have read and understood the foregoing and that OAMPI Inc. is free from any liability, claim, or legal action for which you are involved or may involved.</p>
                          <p>&nbsp;</p><p>&nbsp;</p>

                      </td>
                    </tr>
                    <tr>
                      <td class="text-center" ><strong>Requested by:</strong> <br /><br/> <br /><p>&nbsp;</p><p>&nbsp;</p>

                        <div id="alert-requestedBy" style="margin-top:10px"></div>
                        <select name="requestedBy" id="requestedBy" class="required form-control text-center"style="width:70%; margin:0 auto">
                          <option value="0" class="text-center"> -- Select a leader --</option>
                          @foreach ($leaders as $leader)
                            <option @if ($movement->requestedBy == $leader['id']) selected="selected" @endif value="{{$leader['id']}}" data-position="{{$leader['position']}}" data-campaign="{{$leader['campaign']}}">{{$leader['lastname']}}, {{$leader['firstname']}} </option>
                          @endforeach
                        </select>
                        <br>
                        <em id="requestorPosition"></em></td>
                      <td  class="text-center"><strong>Approved by: <br /><br/><br/> <p>&nbsp;</p><p>&nbsp;</p>
                        Michael Chang</strong><br>
                        <em>Operations Manager</em></td>

                    </tr>

                    <tr>
                      <td class="text-center"><br /><br/><br/><br/><br/><br/>
                        <strong>{{$personnel->firstname}} {{$personnel->lastname}}</strong><br/>
                       Employee Signature / Date</td><p>&nbsp;</p><p>&nbsp;</p>
                      <td class="text-center"><strong>Noted by:</strong> <br /><br/><br/><p>&nbsp;</p><p>&nbsp;</p> 
                        <div id="alert-hrPersonnel" style="margin-top:10px"></div>
                        <select name="hrPersonnel" id="hrPersonnel" class="form-control text-center" style="width:45%; margin:0 auto" required>
                          <option value="0"> -- Select HR personnel --</option>
                          @foreach ($hrPersonnels as $leader)
                            <option @if ($movement->notedBy == $leader['id']) selected="selected" @endif class="text-center" value="{{$leader['id']}}" data-position="{{$leader['position']}}" data-campaign="{{$leader['campaign']}}">{{$leader['lastname']}}, {{$leader['firstname']}} </option>
                          @endforeach
                        </select><br>
                        <em id="personnelPosition"></em></td>

                    </tr>



                  </table><p>&nbsp;</p>

                 <!--  <p class="text-center"> <a href="#" class="btn btn-md btn-flat btn-primary" id="save"><i class="fa fa-save"></i> Save</a></p> -->
                  <p class="text-center"> 
                    <input type="submit" class="btn btn-lg btn-primary btn-flat" name='submit' value="Update" /> <a href="{{action('MovementController@changePersonnel', $movement->user_id)}}" class="btn btn-lg btn-default btn-flat"><i class="fa fa-reply"></i> Back </a>
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



    $("select[name='program']").on('change', function(){
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
            var htmlcode2 = "<br/><strong>Immediate Supervisor</strong><select name='newHead' id='newhead' class='form-control'>";
            htmlcode2 += "<option value='0'> -- Select Leader -- </option>";
            $.each(response, function(index) {

              htmlcode2 +="<option value='"+response[index].id+"'>"+response[index].lastname+", "+response[index].firstname+"</option>";


             

            
            }); //end each

            htmlcode2 +="</select><br/><div id='alert-newHead'></div>";
            $('#newTeam').html('');
            $('#newTeam').html(htmlcode2);



          }//end success

          }); //end ajax
        }//end if

      }); //end select on change

    $("select[name='position']").on('change', function(){
     var pos =  $(this).find(':selected').val();

     if (pos == "-1"){ //add new position
      var htmcode = "<input required type='text' class='form-control' name='newPosition' id='newPosition' placeholder='Enter new job position' value='' />";

        $('#newPosition').html(htmcode);
        console.log(htmcode);

     } else $('#newPosition').html('');
   });

   $('#changeNotice').on('submit', function(e) {

      var v1 = $('#requestDate').val();
      var v2 = $('#effectivityDate').val();
      var v3 = $('#requestedBy').val();
      var v4 = $('#hrPersonnel').val();


      var h1 = $('#alert-requestDate');
      var h2 = $('#alert-effectivityDate');
      var h3 = $('#alert-requestedBy');
      var h4 = $('#alert-hrPersonnel');

      //check sub selects

        var reason = $('input[name="reason" ]:checked').val();
        var withinProgram;

       

      if (!validateRequired(v3,h3,"0") || !validateRequired(v4,h4,"0") ) { 
        console.log('not valid, preventDefault'); e.preventDefault(); e.stopPropagation(); 

        

      } else {

        switch(reason){
          case '1': { 
                      var program = $('select[name="program"]').find(':selected').val();
                      var new_id = $('select[name="newHead"]').find(':selected').val();
                      var new_floor = $('select[name="newFloor"]').find(':selected').val();


                      if (program == {{$hisCampaign->id}}) withinProgram=true; else withinProgram=false;
                      console.log('id for program: '+new_id);
                      saveProgramMovement(program,new_id,new_floor, withinProgram,v2, false, v3, v1, v4, reason);
                      

                    }break;
          case '2': { 
                      //check field
                      var newPos = $('input[name="newPosition"]').val();
                      var hp = $('#alert-position');
                      var _token = "{{ csrf_token() }}";
                     
                      var new_id = $('select[name="position"]').find(':selected').val();

                      if (new_id == '-1'){

                        if (!validateRequired(newPos,hp,"Enter new job position")){
                              console.log('not valid PosValue: '+newPos); e.preventDefault(); e.stopPropagation(); return false; 
                        } else {

                                   withinProgram = true;  
                                  console.log('id for pos: '+new_id);

                                  //save first the new position
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
                                                                    saveMovement(posID, withinProgram,v2, false, v3, v1, v4, reason);

                                                                  }//end success

                                                      }); //end ajax

                        } 

                      } else{

                        saveMovement(new_id, withinProgram,v2, false, v3, v1, v4, reason);

                      }

                      

                    }break;
          case '3': { 
                      withinProgram = true; 
                      var new_id = $('select[name="status"]').find(':selected').val();
                      console.log('id for status: '+new_id);
                      saveMovement(new_id, withinProgram,v2, false, v3, v1, v4, reason);

                    }break;



        }//end switch

        //save movement
        

         

      }//!validateRequired(v1,h1,null) || !validateRequired(v2,h2,null) ||
     

    

      
      e.preventDefault(); e.stopPropagation();
      return false;

   });

   $( ".datepicker" ).datepicker();



   $("select[name='requestedBy']").on('change', function(){
     var pos =  $(this).find(':selected').attr('data-position');
     var camp =  $(this).find(':selected').attr('data-campaign');
     $('#requestorPosition').html('');
     $('#requestorPosition').html(pos +', <strong>'+camp+'</strong>');

   });

   $("select[name='hrPersonnel']").on('change', function(){
     var pos =  $(this).find(':selected').attr('data-position');
     var camp =  $(this).find(':selected').attr('data-campaign');
     $('#personnelPosition').html('');
     $('#personnelPosition').html(pos +', <strong>'+camp+'</strong>');

   });

   
   

    

   });


function saveProgramMovement(campaign,new_id,new_floor, withinProgram,v2, isApproved, v3, v1, v4, reason){

  var _token = "{{ csrf_token() }}";
        $.ajax({
                      url:"{{action('MovementController@update', $movement->id)}} ",
                      type:'PUT',

                      data:{
                        'campaign_id': campaign,
                        'id': "{{$movement->id}}",
                        'new_id': new_id,
                        'newFloor': new_floor,
                        'withinProgram': withinProgram,
                        
                        'effectivity': v2,
                        'isApproved': isApproved,
                        'requestedBy': v3,
                        'dateRequested': v1,
                        'notedBy': v4,
                        'personnelChange_id': reason,
                        _token:_token},

                      error: function(response)
                      {
                        console.log(response);
                        return false;
                      },
                      success: function(response)
                      {

                        $('input[name="submit"').fadeOut();
                      var htmcode = "<span class=\"success\"> <i class=\"fa fa-save\"></i> Employee movement data updated. <br />";
                      htmcode += "Movement_id: "+response.id;
                     
                     
                      htmcode += "<br/><br/>";
                      
                      $('#alert-submit').addClass('alert alert-success').fadeIn().html(htmcode); 
                      console.log(response);




                      }//end success

          }); //end ajax

}

function saveMovement(new_id, withinProgram,v2, isApproved, v3, v1, v4, reason){

  var _token = "{{ csrf_token() }}";
        $.ajax({
                      url:"{{action('MovementController@update', $movement->id)}} ",
                      type:'PUT',

                      data:{
                        'id': "{{$movement->id}}",
                        'new_id': new_id,
                        'withinProgram': withinProgram,
                        
                        'effectivity': v2,
                        'isApproved': isApproved,
                        'requestedBy': v3,
                        'dateRequested': v1,
                        'notedBy': v4,
                        'personnelChange_id': reason,
                        _token:_token},

                      error: function(response)
                      {
                        console.log(response);
                        return false;
                      },
                      success: function(response)
                      {

                        $('input[name="submit"').fadeOut();
                      var htmcode = "<span class=\"success\"> <i class=\"fa fa-save\"></i> Employee movement data updated. <br />";
                      htmcode += "Movement_id: "+response.id;
                     
                     
                      
                      $('#alert-submit').addClass('alert alert-success').fadeIn().html(htmcode); 
                      console.log(response);




                      }//end success

          }); //end ajax

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