<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar user panel (optional) -->
      <div class="user-panel">
        <div class="pull-left image">
          <a href="{{action('UserController@show',Auth::user()->id)}}" class="user-image" >
           @if ( file_exists('public/img/employees/'.Auth::user()->id.'.jpg') )
              <img src="{{asset('public/img/employees/'.Auth::user()->id.'.jpg')}}" class="user-image" alt="User Image" width="50">
              @else
                <img src="{{asset('public/img/useravatar.png')}}" class="user-image" alt="User Image" width="40">

                @endif

              </a>

         
        </div>
        <div class="pull-left info">
          <small><strong>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</strong></small><br/>
          <!-- Status -->
          
            
              <small class="text-success"><i class="fa fa-circle text-success"></i> Online</small> 
            
          
        </div>
      </div>

      <!-- search form (Optional) -->
      <!-- <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form> -->

      <p>&nbsp;</p>
      <!-- /.search form -->

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">
        <li class="header">MY TOOLS</li>
       
        <!-- Optionally, you can add icons to the links -->
        <li class="@if (Request::is('page')) active @endif"><a href="{{ action('HomeController@index') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

        <li @if (Request::is('myEvals')) class="active" @endif><a href="{{action('UserController@myEvals')}}" ><i class="fa fa-file-o"></i> <span>My Evals</span></a></li>
        <li @if (Request::is('myProfile')) class="active" @endif><a href="{{action('UserController@show',Auth::user()->id)}}" ><i class="fa fa-user"></i> <span>My Profile</span></a></li>

        @if( Auth::user()->isAleader )
        <li @if (Request::is('mySubordinates')) class="active" @endif><a href="{{action('UserController@mySubordinates')}}" ><i class="fa fa-users"></i> <span>My Team</span></a></li>
        @endif



        <li class="header">OAMPI SYSTEM</li>
        <li><a href="http://172.17.0.2/coffeebreak/" target="_blank"><i class="fa fa-coffee" ></i> <img src="http://172.17.0.2/coffeebreak/wp-content/uploads/2016/02/logo.png" width="100" /> <span></span></a></li>
       

        <li class="treeview  @if ( Request::is('user_*') ) active @endif ">
          <a href="#">
            <i class="fa fa-clock-o"></i> <span>Timekeeping</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
         
            <li style="padding-left:20px" @if ( Request::is('user_*') ) class="active" @endif ><a  @if ( Request::is('user_*') ) class="active" @endif href="{{action('DTRController@show',Auth::user()->id)}}"><i class="fa fa-calendar"></i> My DTR</a></li>
           <!--  <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-bed"></i> Leaves</a></li>
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-tachometer"></i> OT / UT</a></li> --><!-- 
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-pencil"></i> CWS</a></li>
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-calendar-times-o"></i> File DTRP</a></li> -->
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-stack-overflow"></i> Timekeeping Forms</a></li>
            <hr /><!--  --> 
            <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('UPLOAD_BIOMETRICS') ){ ?> 
            <li style="padding-left:20px"><a href="#" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal_upload"><i class="fa fa-upload"></i>Upload Biometrics</a></li>

            <?php } ?><br/>
          </ul>
        </li>




         <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('ACCESS_SETTINGS') ){ ?> 
        <li class="treeview @if (Request::is('evalSetting') || Request::is('evalForm*')) active @endif">
          <a href="#">
            <i class="fa fa-check-square-o"></i> <span>Evaluation</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
          <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('VIEW_ALL_EVALS') ){ ?> 
            <li style="padding-left:20px"><a href="{{action('EvalFormController@downloadReport')}} "><i class="fa fa-download"></i> Download Summary</a></li>
            <li  @if (Request::is('evalForm')) class="active" @endif  style="padding-left:20px"><a href="{{action('EvalFormController@index')}} "><i class="fa fa-file-o"></i> View All</a></li>
             <?php }  ?>

            
            <li style="padding-left:20px" @if (Request::is('evalSetting')) class="active" @endif ><a href="{{action('EvalSettingController@index')}} "><i class="fa fa-gears"></i> Settings</a></li>
            

            
             <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('CREATE_EVALS') ){ ?> 
            <!-- <li style="padding-left:20px"><a href="#"><i class="fa fa-plus"></i> Create New </a></li> -->
             <?php }  ?>

            <!-- <li style="padding-left:20px"><a href="{{action('EvalFormController@printBlankEval', Auth::user()->id)}} "><i class="fa fa-plus"></i> Print Blank </a></li> -->
            
            

           
          </ul>
        </li>  <?php }  ?>


       

         <li class="treeview @if (Request::is('campaign*')) active @endif">
          <a href="#"><i class="fa fa-sitemap"></i> <span>Departments / Programs</span> <i class="fa fa-angle-left pull-right"></i></a>
          <ul class="treeview-menu">
           <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('ADD_NEW_PROGRAM') ){ ?> 
            <li @if (Request::is('campaign/create')) class="active" @endif  style="padding-left:20px"><a href="{{action('CampaignController@create')}} "><i class="fa fa-plus"></i> Add New </a></li>
            <?php }  ?>

            <li style="padding-left:20px" @if (Request::is('campaign')) class="active" @endif ><a href="{{action('CampaignController@index')}} "><i class="fa fa-users"></i> View All</a></li>
          </ul>
        </li>

        <li class="treeview @if ( Request::is('user*') || Request::is('movement') || Request::is('editUser*') ) active @endif">
          <a href="#"><i class="fa fa-users"></i> <span>Employees</span> <i class="fa fa-angle-left pull-right"></i></a>
          <ul class="treeview-menu">
           
            <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('ADD_NEW_EMPLOYEE') ){ ?> 
            <li @if (Request::is('user/create')) class="active" @endif style="padding-left:20px"><a href="{{action('UserController@create')}} "><i class="fa fa-plus"></i> Add New </a></li>
            <?php }  ?>

            <li style="padding-left:20px"@if (Request::is('user')) class="active" @endif><a href="{{action('UserController@index')}} "><i class="fa fa-users"></i> View All</a></li>

            <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('VIEW_ALL_EVALS') ){ ?> 
            <li style="padding-left:20px"><a href="{{action('UserController@downloadAllUsers')}} "><i class="fa fa-download"></i> Download Masterlist</a></li>
             <?php }  ?>


             
             <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('MOVE_EMPLOYEE') ){ ?> 
            <li style="padding-left:20px" @if (Request::is('movement*')) class="active" @endif><a href="{{action('MovementController@index')}}"><i class="fa fa-exchange"></i> <span>Movements</span></a></li> 
          <?php }else if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('MANAGE_TEAM_DISTRIBUTION') ) {?>
          <li style="padding-left:20px" @if (Request::is('movement*')) class="active" @endif><a href="{{action('MovementController@index')}}"><i class="fa fa-exchange"></i> <span>Team Distribution</span></a></li> 
          <?php }  ?>
            <!-- <li style="padding-left:20px"@if (Request::is('movement*')) class="active" @endif ><a href="{{action('MovementController@index')}}"><i class="fa fa-users"></i> <span>Personnel Change Notice</span></a></li> -->

          </ul>
        </li>

        <li class="treeview @if (Request::is('immediateHead*')) active @endif">
          <a href="#"><i class="fa fa-street-view"></i> <span>Leaders</span> <i class="fa fa-angle-left pull-right"></i></a>
          <ul class="treeview-menu">
            
             <?php if ( OAMPI_Eval\UserType::find(Auth::user()->userType_id)->roles->pluck('label')->contains('ADD_LEADER') ){ ?> 
            <li @if (Request::is('immediateHead/create')) class="active" @endif  style="padding-left:20px"><a href="{{action('ImmediateHeadController@create')}} "><i class="fa fa-plus"></i> Add New Leader</a></li>
             <?php }  ?>
            <li style="padding-left:20px" @if (Request::is('immediateHead')) class="active" @endif><a href="{{action('ImmediateHeadController@index')}} "><i class="fa fa-users"></i> View All</a></li>
          </ul>
        </li>

       

       
        
        

        

        <li><a href="http://172.17.0.51/accessone/login" target="_blank"><i class="fa fa-file"></i> <span>HRIS</span></a></li>
       <!--  <li><a href="http://oampayroll.openaccessbpo.com/" target="_blank"><i class="fa fa-usd"></i> <span>Payroll</span></a></li> -->
       
        
      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

   @include('layouts.modals-upload', [
                                'modelRoute'=>'biometrics.store',
                                'modelID' => '_upload', 
                                'modelName'=>"Biometrics file ", 
                                'modalTitle'=>'Upload', 
                                'modalMessage'=>'Select biometrics file to upload (*.csv):', 
                                'formID'=>'uploadBio',
                                'icon'=>'glyphicon-up' ])