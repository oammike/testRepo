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
      <!-- /.search form -->
      <p>&nbsp;</p>

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">
        <li class="header">MY TOOLS</li>
        <!-- Optionally, you can add icons to the links -->
        <!-- <li class="@if (Request::is('page')) active @endif"><a href="{{ action('HomeController@index') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li> -->

        <li @if (Request::is('myEvals')) class="active" @endif><a href="{{action('UserController@myEvals')}}" ><i class="fa fa-file-o"></i> <span>My Evals</span></a></li>
        <li @if (Request::is('myProfile')) class="active" @endif><a href="{{action('UserController@show',Auth::user()->id)}}" ><i class="fa fa-user"></i> <span>My Profile</span></a></li>
        
        <!-- <li class="treeview @if (Request::is('evalSetting')) active @endif">
          <a href="#">
            <i class="fa fa-check-square-o"></i> <span>Evaluation</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
           
            <li><a href="{{action('EvalFormController@index')}} "><i class="fa fa-file"></i>  View All Forms</a></li>
            

           
          </ul>
        </li>
         -->

        <li class="header">OAMPI SYSTEM</li>
         <li><a href="http://172.17.0.2/coffeebreak/" target="_blank"><i class="fa fa-coffee" ></i> <img src="http://172.17.0.2/coffeebreak/wp-content/uploads/2016/02/logo.png" width="100" /> <span></span></a></li>
       
         <li class="treeview @if (Request::is('user_dtr')) active @endif">
          <a href="#">
            <i class="fa fa-clock-o"></i> <span>Timekeeping</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
         
            <li style="padding-left:20px" @if ( Request::is('user_*') ) class="active" @endif ><a  @if ( Request::is('user_*') ) class="active" @endif href="{{action('DTRController@show',Auth::user()->id)}}"><i class="fa fa-calendar"></i> My DTR</a></li>
           <!--  <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-bed"></i> Leaves</a></li>
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-tachometer"></i> OT / UT</a></li>
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-calendar-times-o"></i> File DTRP</a></li> -->
            <li style="padding-left:20px"><a href="{{action('HomeController@module')}}"><i class="fa fa-stack-overflow"></i> Timekeeping Forms</a></li>
          </ul>
        </li>
        <li @if (Request::is('campaign')) class="active" @endif><a href="{{action('CampaignController@index')}} "><i class="fa fa-sitemap"></i> <span>Departments / Programs</span></a>
        <li @if (Request::is('user')) class="active" @endif><a href="{{action('UserController@index')}} "><i class="fa fa-users"></i> Employees</a></li>
        <li><a href="http://172.17.0.51/accessone/login" target="_blank"><i class="fa fa-file"></i> <span>HRIS</span></a></li>
        <li><a href="http://oampayroll.openaccessbpo.com/" target="_blank"><i class="fa fa-usd"></i> <span>Payroll</span></a></li>

        
      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>