@extends('layouts.main')

@section('metatags')
<title>All Employees | OAMPI Evaluation System</title>

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

@section('bodyClasses')
sidebar-collapse
@endsection

@section('content')


  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Employees
        <small>Manage all OAMPI employees </small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{action('HomeController@index')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        
        <li class="active">Employees</li>
      </ol>
    </section>

     <section class="content">
     

          <div class="row" style="margin-top:50px">
           
            

            <div class="col-lg-12"> <!-- ******************* LEFT COLUMN ***************** -->

              <!-- TABLE: LEFT -->
              <div class="box-body">

                <!-- Custom Tabs -->
                                              <div class="nav-tabs-custom">
                                                <ul class="nav nav-tabs">
                                                  <li class="active"><a href="#tab_1" data-toggle="tab"><strong class="text-primary ">ACTIVE EMPLOYEES ({{count($activeUsers)}})</strong></a></li>
                                                  <li><a href="#tab_2" data-toggle="tab"><strong class="text-primary">INACTIVE EMPLOYEES ({{count($inactiveUsers)}})</strong></a></li>
                                                   @if ($hasUserAccess) 
                                                    <a href="{{action('UserController@create')}} " class="btn btn-sm btn-primary  pull-right"><i class="fa fa-plus"></i> Add New Employee</a>
                                                   
                                                    @endif


                                                </ul>
                                                

                                                <div class="tab-content">
                                                  <div class="tab-pane active" id="tab_1"> <!-- ACTIVE EMPLOYEES -->
                                                    <div class="row" style="margin-top:50px">

                                                       
                                                          <table class="table no-margin table-bordered table-striped" id="active" >
                                                            
                                                          </table>

                                                          @foreach ($activeUsers as $user)
                                                           @include('layouts.modals', [
                                                                'modelRoute'=>'user.destroy',
                                                                'modelID' => $user->id, 
                                                                'modelName'=>$user->firstname." ". $user->lastname, 
                                                                'modalTitle'=>'Delete', 
                                                                'modalMessage'=>'Are you sure you want to delete this?', 
                                                                'formID'=>'deleteEmployee',
                                                                'icon'=>'glyphicon-trash' ])

                                                          @endforeach

                                                          
                                                       
                                                    </div>
                                                      <!-- /.row -->
                                                    

                                                  </div><!--end pane1 -->
                                                  <!-- /.tab-pane -->



                                                  <div class="tab-pane" id="tab_2">

                                                    <div class="row" style="margin-top:50px">

                                                       <div class="table">
                                                          <table class="table no-margin table-bordered table-striped" id="inactive" >
                                                          </table>

                                                          @foreach($inactiveUsers as $user)
                                                          @include('layouts.modals', [
                                                                'modelRoute'=>'user.destroy',
                                                                'modelID' => $user['id'], 
                                                                'modelName'=>$user['firstname']." ". $user['lastname'], 
                                                                'modalTitle'=>'Delete', 
                                                                'modalMessage'=>'Are you sure you want to delete this?', 
                                                                'formID'=>'deleteEmployee',
                                                                'icon'=>'glyphicon-trash' ])

                                                          @endforeach

                                                          
                                                        </div>
                                                        <!-- /.table-responsive -->

                                                    </div>
                                                      <!-- /.row -->
                                                    
                                                    

                                                    
                                                   

                                                  </div>
                                                  <!-- /.tab-pane -->



                                                 

                                                 


                                                </div>
                                                <!-- /.tab-content -->
                                              </div>
                                              <!-- nav-tabs-custom -->


                 
              </div>
                <!-- /.box-body -->
              <div class="box-footer clearfix" style="background:none">
               
                
              </div>
                <!-- /.box-footer -->
              <!-- /.box -->
            </div><!--end left -->


           


            
           

          
          </div><!-- end row -->

       
     </section>
          



@endsection


@section('footer-scripts')

<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>



<!-- Page script <small> '+full.employeeNumber+'</small> -->

 <?php if($hasUserAccess) { ?> 
 <script type="text/javascript">

 $(function () {
   'use strict';
  


    $("#active").DataTable({
                      "ajax": "{{ action('UserController@getAllActiveUsers') }}",
                      "processing":true,
                      "stateSave": true,
                      "lengthMenu": [20, 100, 500],//[5, 20, 50, -1], 

                        "columns": [
                            { title: " ", data:'profilepic', width:'90', class:'text-center', sorting:false, search:true, render: function ( data, type, full, meta ) {return '<img src="'+data+'" class="img-circle" alt="User Image" width="60" height="60" /><br/><small> '+full.employeeNumber+'</small>';}},
                            { title: "Last name", defaultContent: "<i>none</i>" , data:'lastname', width:'180'},  
                            { title: "First name", defaultContent: " ", data:'firstname',width:'180'}, // 1
                            { title: "Position", defaultContent: " ", data:'position',width:'210'}, // 1
                             { title: "Status " ,defaultContent: "<i>empty</i>", data:'status',width:'100' }, // 2
                            { title: "Program / Campaign " ,defaultContent: "<i>empty</i>", data:'campaign',width:'180' }, // 2
                            //{ title: "Immediate Head", defaultContent: " ", data:'immediateHead',width:'180'}, // 1
                            
                           
                            
                            { title: "Actions", data:'id', class:'text-center', sorting:false, render: function ( data, type, full, meta ) {return '<a style="margin-top:5px" href="user/'+data+'" class="btn btn-xs btn-flat btn-default"><i class="fa fa-user"></i> View Profile</a> <a href="editUser/'+data+'"   style="margin-top:5px" class="btn btn-xs btn-flat btn-default"><i class="fa fa-pencil"></i> Edit</a> <br/> <a href="movement/changePersonnel/'+data+'" id="teamMovement'+data+'" memberID="'+data+'" class="teamMovement btn btn-xs btn-flat btn-default" style="margin-top:5px"><i class="fa fa-exchange"></i> Movement</a><br/><div class="clearfix"></div><a target="_blank" href="user_dtr/'+data+'"   style="margin-top:5px" class="btn btn-xs btn-flat btn-success"><i class="fa fa-calendar"></i> Daily Time Record [DTR]</a>';}}
                            

                        ],
                       

                      "responsive":true,
                      //"scrollX":false,
                      "dom": '<"col-xs-1"f><"col-xs-11 text-right"l><"clearfix">rt<"bottom"ip><"clear">',
                      "order": [[ 1, 'ASC' ]],
                      "lengthChange": true,
                      "oLanguage": {
                         "sSearch": "<strong>Refine Results</strong> <br/>To re-order entries, click on the sort icon to the right of column headers. <br/>To filter out results, just type in the search box anything you want to look for:",
                         "class": "pull-left"
                       },

                
        });



    $("#inactive").DataTable({
                      "ajax": "{{ action('UserController@getAllInactiveUsers') }}",
                      "processing":true,
                      "stateSave": true,
                      "lengthMenu": [20, 100, 500],//[5, 20, 50, -1], 

                        "columns": [
                            { title: " ", data:'profilepic', width:'90', class:'text-center', sorting:false, search:true, render: function ( data, type, full, meta ) {return '<img src="'+data+'" class="img-circle" alt="User Image" width="60" height="60" /><br/><small> '+full.employeeNumber+'</small>';}},
                            { title: "Last name", defaultContent: "<i>none</i>" , data:'lastname', width:'180'},  
                            { title: "First name", defaultContent: " ", data:'firstname',width:'180'}, // 1
                            { title: "Position", defaultContent: " ", data:'position',width:'210'}, // 1
                             { title: "Status " ,defaultContent: "<i>empty</i>", data:'status',width:'100' }, // 2
                            { title: "Program / Campaign " ,defaultContent: "<i>empty</i>", data:'campaign',width:'180' }, // 2
                            //{ title: "Immediate Head", defaultContent: " ", data:'immediateHead',width:'180'}, // 1
                            
                           
                            
                            { title: "Actions", data:'id', class:'text-center', sorting:false, render: function ( data, type, full, meta ) {return '<a style="margin-top:5px" href="user/'+data+'" class="btn btn-xs btn-flat btn-default"><i class="fa fa-user"></i> View Profile</a> <a href="editUser/'+data+'"   style="margin-top:5px" class="btn btn-xs btn-flat btn-default"><i class="fa fa-pencil"></i> Edit</a> <br/> <a href="movement/changePersonnel/'+data+'" id="teamMovement'+data+'" memberID="'+data+'" class="teamMovement btn btn-xs btn-flat btn-default" style="margin-top:5px"><i class="fa fa-exchange"></i> Movement</a><br/><div class="clearfix"></div>';}}
                            

                        ],
                       

                      "responsive":true,
                      //"scrollX":false,
                      "dom": '<"col-xs-1"f><"col-xs-11 text-right"l><"clearfix">rt<"bottom"ip><"clear">',
                      "order": [[ 1, 'ASC' ]],
                      "lengthChange": true,
                      "oLanguage": {
                         "sSearch": "<strong>Refine Results</strong> <br/>To re-order entries, click on the sort icon to the right of column headers. <br/>To filter out results, just type in the search box anything you wanna look for:",
                         "class": "pull-left"
                       },

                
        });
});

 </script>

<?php } else { ?>

  <script type="text/javascript">

    $(function () {
   'use strict';

   $("#active").DataTable({
                      "ajax": "{{ action('UserController@getAllActiveUsers') }}",
                      "processing":true,
                      "stateSave": true,
                      "lengthMenu": [20, 100, 500],//[5, 20, 50, -1], 

                        "columns": [
                            { title: " ", data:'profilepic', width:'90', class:'text-center', sorting:false, search:true, render: function ( data, type, full, meta ) {return '<img src="'+data+'" class="img-circle" alt="User Image" width="60" height="60" /><br/><small> '+full.employeeNumber+'</small>';}},
                            { title: "Last name", defaultContent: "<i>none</i>" , data:'lastname', width:'180'},  
                            { title: "First name", defaultContent: " ", data:'firstname',width:'180'}, // 1
                            { title: "Position", defaultContent: " ", data:'position',width:'210'}, // 1
                             { title: "Status " ,defaultContent: "<i>empty</i>", data:'status',width:'100' }, // 2
                            { title: "Program / Campaign " ,defaultContent: "<i>empty</i>", data:'campaign',width:'180' }, // 2
                            //{ title: "Immediate Head", defaultContent: " ", data:'immediateHead',width:'180'}, // 1
                            
                           
                            
                           { title: "Actions", data:'id', class:'text-center', sorting:false, render: function ( data, type, full, meta ) {return '<span class="text-success"><small><em>* Access Denied *</em></small></span>';}}
                         

                        ],
                       

                      "responsive":true,
                      //"scrollX":false,
                      "dom": '<"col-xs-1"f><"col-xs-11 text-right"l><"clearfix">rt<"bottom"ip><"clear">',
                      "order": [[ 1, 'ASC' ]],
                      "lengthChange": true,
                      "oLanguage": {
                         "sSearch": "<strong>Refine Results</strong> <br/>To re-order entries, click on the sort icon to the right of column headers. <br/>To filter out results, just type in the search box anything you want to look for:",
                         "class": "pull-left"
                       },

                
        });



    $("#inactive").DataTable({
                      "ajax": "{{ action('UserController@getAllInactiveUsers') }}",
                      "processing":true,
                      "stateSave": true,
                      "lengthMenu": [20, 100, 500],//[5, 20, 50, -1], 

                        "columns": [
                            { title: " ", data:'profilepic', width:'90', class:'text-center', sorting:false, search:true, render: function ( data, type, full, meta ) {return '<img src="'+data+'" class="img-circle" alt="User Image" width="60" height="60" /><br/><small> '+full.employeeNumber+'</small>';}},
                            { title: "Last name", defaultContent: "<i>none</i>" , data:'lastname', width:'180'},  
                            { title: "First name", defaultContent: " ", data:'firstname',width:'180'}, // 1
                            { title: "Position", defaultContent: " ", data:'position',width:'210'}, // 1
                             { title: "Status " ,defaultContent: "<i>empty</i>", data:'status',width:'100' }, // 2
                            { title: "Program / Campaign " ,defaultContent: "<i>empty</i>", data:'campaign',width:'180' }, // 2
                            //{ title: "Immediate Head", defaultContent: " ", data:'immediateHead',width:'180'}, // 1
                            
                           
                            
                            { title: "Actions", data:'id', class:'text-center', sorting:false, render: function ( data, type, full, meta ) {return '<span class="text-success"><small><em>* Access Denied *</em></small></span>';}}
                        

                        ],
                       

                      "responsive":true,
                      //"scrollX":false,
                      "dom": '<"col-xs-1"f><"col-xs-11 text-right"l><"clearfix">rt<"bottom"ip><"clear">',
                      "order": [[ 1, 'ASC' ]],
                      "lengthChange": true,
                      "oLanguage": {
                         "sSearch": "<strong>Refine Results</strong> <br/>To re-order entries, click on the sort icon to the right of column headers. <br/>To filter out results, just type in the search box anything you wanna look for:",
                         "class": "pull-left"
                       },

                
        });





});

 </script>

<?php } ?>
<script>
  
  $(function () {
   'use strict';
  


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