@extends('layouts.main')

@section('metatags')
<title>Employees | OAMPI Evaluation System</title>

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
        Employee Movement
        <small>Manage all employee movement </small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{action('MovementController@index')}}"> All Employee Movements</a></li>
       
      </ol>
    </section>

     <section class="content">
     

          <div class="row" style="margin-top:50px">
           
            

            <div class="col-lg-12"> <!-- ******************* LEFT COLUMN ***************** -->

              <!-- TABLE: LEFT -->
              <div class="box-body">
                  <div class="table">
                    <table class="table no-margin" id="teams" >
                      <?php /*
                      <thead>
                      <tr>
                        <th></th>
                        
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email Address</th>
                        <th>Program / Campaign</th>
                        <th>Action</th>
                        
                      </tr>
                      </thead>
                      <tbody>
                        @foreach ($users as $member)
                      <tr id="row{{$member->id}}">
                         @if ( file_exists('public/img/employees/'.$member->id.'.jpg') )
                         <td class="text-center "><img src="{{asset('public/img/employees/'.$member->id.'.jpg')}}" width='60' /> </td>
                         @else
                         <td class="text-center "><img src="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}" class="img-circle" alt="User Image"> </td>
                         @endif
                        
                        
                        <td>{{$member->lastname}}</td>
                        <td>{{$member->firstname}}</a></td>
                        <td>{{$member->email}}</td>
                        <td>{{$member->campaign[0]->name}}</td>
                        <td>
                          
                            <a href="#" id="teamMovement{{$member->id}}" memberID="{{$member->id}}" class="teamMovement btn btn-sm btn-flat btn-default" style="margin-bottom:5px"><i class="fa fa-exchange"></i> Team Movement</a> <br/><div class="clearfix"></div>
                            <div id="teamOption{{$member->id}}" class="teamOption">
                              <label>Move to New Team</label>
                              <select name="team" id="team" class="form-control" style="margin-bottom:5px">
                                <option value="0" memberID="{{$member->id}}">Select new program / campaign</option>
                                @foreach ($campaigns as $team)
                                <option value="{{$team->id}}" memberID="{{$member->id}}">{{$team->name}} </option>

                                @endforeach

                              </select>
                              <a class="saveBtn btn btn-sm btn-flat btn-success" id="save{{$member->id}}" memberID="{{$member->id}}"><i class="fa fa-save"></i> Save </a>
                              <div class="clearfix"></div>
                            </div>

                           <!--  <a href="#" class="btn btn-sm btn-flat btn-default"style="margin-bottom:5px"><i class="fa fa-pencil"></i> Edit</a> <div class="clearfix"></div>
                            <a href="#" class="btn btn-sm btn-flat btn-default"style="margin-bottom:5px"><i class="fa fa-trash"></i> Delete</a> <div class="clearfix"></div> -->

                        

                        </td>
                        
                      </tr>
                      @endforeach
                      </tbody>

                      */ ?>
                    </table>
                  </div>
                  <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix" style="background:none">
                 
                  
                </div>
                <!-- /.box-footer -->
              <!-- /.box -->
            </div><!--end left -->

 @foreach ($movements as $movement)
            @include('layouts.modals', [
                          'modelRoute'=>'movement.destroy',
                          'modelID' => $movement['id'], 
                          'modelName'=>$movement['movementType'], 
                          'modalTitle'=>'Delete', 
                          'modalMessage'=>'Are you sure you want to delete this?', 
                          'formID'=>'deleteMovement',
                          'icon'=>'glyphicon-trash' ])

                           @endforeach


           


            
           

          
          </div><!-- end row -->

       
     </section>
          



@endsection


@section('footer-scripts')

<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>



<!-- Page script -->
<script>
  //<a style="margin-top:5px" class="btn btn-xs btn-default" data-toggle="modal" data-target="#myModal'+full.id+'"><i class="fa fa-trash"></i> Delete </a>
  $(function () {
   'use strict';
   $("#teams").DataTable({
                  "ajax": "{{ action('MovementController@getAllMovements') }}",
                  "processing":true,

                    "columns": [
                        { title: " ", data:'profilePic', width:'90', class:'text-center', sorting:false, render: function ( data, type, full, meta ) {return '<a href="user/'+full.user_id+'"><img src="'+full.profilePic+'" class="img-circle" alt="User Image" width="60" /></a> ';}},
                        { title: "Last name", defaultContent: "<i>none</i>" , data:'lastname', width:'150'},  
                        { title: "First name", defaultContent: " ", data:'firstname',width:'120'}, // 1
                        
                        { title: "Program / Campaign " ,defaultContent: "<i>empty</i>", data:'campaign',width:'180' }, // 2
                        { title: "Movement Type", defaultContent: " ", data:'type',width:'220'}, // 1
                        { title: "Effectivity", defaultContent: " ", data:'effectivity',width:'220'}, // 1
                        { title: "Actions", data:'id', class:'text-center', sorting:false, 
                        render: 
                        function ( data, type, full, meta ) {
                          

                          return '<a href="movement/'+full.id+'" id="teamMovement'+data+'" memberID="'
                          +full.user_id+'" class="teamMovement btn btn-sm btn-flat btn-default" style="margin-top:5px"><i class="fa fa-eye"></i> View Details </a> <a href="movement/'
                          +data+'/edit" id="teamMovement'+data+'" memberID="'
                          +full.user_id+'" class="teamMovement btn btn-sm btn-default" style="margin-top:5px"><i class="fa fa-pencil"></i> Edit </a> '
                        }}
                        

                    ],
                   

                  "responsive":true,
                  "scrollX":true,
                  "dom": '<"col-xs-1"f><"col-xs-11 text-right"l><"clearfix">rt<"bottom"ip><"clear">',
                  "order": [[ 0, "ASC" ]],
                  "lengthChange": true,
                  "oLanguage": {
                     "sSearch": "<strong>Refine Results</strong> <br/>To re-order entries, click on the sort icon to the right of column headers. <br/>To filter out results, just type in the search box anything you want to look for:",
                     "class": "pull-left"
                   },

            
    });









<?php /*

   $('#teams').DataTable({
    "scrollX": false,
    "iDisplayLength": 25,
    "responsive": false,
    "columnDefs": [
            
            { "width": "60",  "targets": [ 0 ], "sorting":false },
            // { "width": "150",  "targets": [ 1 ] },
            // { "width": "150",  "targets": [ 2 ] },
            // { "width": "180",  "targets": [ 3 ] },
            // { "width": "150",  "targets": [ 4 ] },
            {"sorting": false, "targets": [5], "width":"200"}
        ],

    // "columns": [
    //             { "searchable": false },
    //             { "searchable": true },
    //             { "searchable": true },
    //             { "searchable": true },
    //             { "searchable": true },
                
    //           ]

   }); */ ?>

   $('.teamOption, .saveBtn').hide();


   $('.teamMovement').on('click', function(e) {
      e.preventDefault();
      var memberID = $(this).attr('memberID');
      var holder = "#teamOption";
      $(this).fadeOut();
      $(holder+memberID).fadeIn();
   });

   $('select[name="team"]').change(function(){    

    var memberID = $(this).find(':selected').attr('memberID'); // $(this).val();
    var newTeam = $(this).find(':selected').val();
    var saveBtn = $('#save'+memberID).fadeIn();


    
  });

   $(".saveBtn").on("click", function(){
    var memberID = $(this).attr('memberID');
    var newTeam = $("#teamOption"+memberID+" select[name=team]").find(':selected').val(); // $(this).val();
     var _token = "{{ csrf_token() }}";

    $.ajax({
                      url:"{{action('UserController@moveToTeam')}} ",
                      type:'POST',
                      data:{memberID:memberID, newTeam:newTeam, _token:_token},
                      error: function(response)
                      {
                          $("#teamOption"+memberID).fadeOut();
                        $("#teamMovement"+memberID).fadeIn();
                        
                        console.log("Error moving: "+newTeam);

                          
                          return false;
                      },
                      success: function(response)
                      {

                        $("#teamOption"+memberID).fadeOut();
                        $("#teamMovement"+memberID).fadeIn();
                        $("#row"+memberID).delay(1000).fadeOut('slow');
                        console.log("Moved to: "+newTeam);
                        console.log(response);

                          return true;
                      }
                  });




    

   });

   


      
      
   });

   

 
</script>
<!-- end Page script -->

<!-- <script type="text/javascript" src="{{asset('public/js/dashboard.js')}}"></script> -->

@stop