  <header class="main-header">

    <!-- Logo -->
    <a href="{{ action('HomeController@index') }}" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="{{ asset('public/img/logo-transparent.png')}}" width="50" style="margin: 0 auto;" /></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="{{ asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}" /><small> <strong> Evaluation</strong> System</small></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <li class="dropdown messages-menu">
            <!-- Menu toggle button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-danger">1</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have a message</li>
              <li>
                <!-- inner menu: contains the messages -->
                <ul class="menu">
                  <li><!-- start message -->
                    <a href="#"  data-toggle="modal" data-target="#message">
                      <div class="pull-left">
                        <!-- User Image -->
                        <img src="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}" class="img-circle" alt="User Image">
                      </div>
                      <!-- Message title and timestamp -->
                      <h4>
                       Welcome!
                        <small><i class="fa fa-clock-o"></i> 5 mins</small>
                      </h4>
                      <!-- The message -->
                      <p>Need help using this system?</p>
                    </a>
                  </li>
                  <!-- end message -->
                </ul>
                <!-- /.menu -->
              </li>
              <li class="footer"><a href="#">See All Messages</a></li>
            </ul>
          </li>
          <!-- /.messages-menu -->

          <!-- Notifications Menu -->
          <li class="dropdown notifications-menu">
            <!-- Menu toggle button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <?php $notifications = OAMPI_Eval\User::find(Auth::user()->id)->notifications;
                    $unseenNotifs = OAMPI_Eval\User_Notification::where('user_id',Auth::user()->id)->where('seen',false)->orderBy('created_at','DESC')->get(); ?>

               @if (!Auth::user()->updatedPass)

                      @if ( count($unseenNotifs) > 0 )
                    <span class="label label-danger"><span class="notifyCount">{{count($unseenNotifs)+1}} </span> </span>

                    @else
                     <span class="label label-danger"><span class="notifyCount">1 </span> </span>
                    @endif

               @else
               @if (count($unseenNotifs)!==0)<span class="label label-danger"><span class="notifyCount">{{count($unseenNotifs)}} </span> </span>@endif
               @endif

              
             
              
            </a>
            <ul class="dropdown-menu">
              <?php if ( count($unseenNotifs) > 0 ){ ?>
              <li class="header">You have notifications</li>
              <li>
                <!-- Inner Menu: contains the notifications -->
                <ul class="menu">



                    <?php foreach( $unseenNotifs as $notif){ 
                      $detail = $notif->detail; // OAMPI_Eval\Notification::find($notif->notification_id); ?>
                     
                    <li class="bg-warning"><!-- start notification -->

                      <?php switch($detail->type){
                        case 1: { $actionlink = action('UserController@changePassword'); break; } //change of password
                        case 2: { $actionlink = route('movement.show', array('id' => $detail->relatedModelID, 'notif'=>$detail->id, 'seen' => true )); break; } //movement
                        case 3: { $actionlink = action('MovementController@show',['id'=>$detail->relatedModelID,'notif'=>$detail->id,'seen'=>true]); break; } //change status
                        case 4: { $actionlink =route('movement.show', array('id' => $detail->relatedModelID, 'notif'=>$detail->id, 'seen' => true )); break; } //change position
                        case 5: { $actionlink = action('EvalFormController@show',['id'=>$detail->relatedModelID, 'notif'=>$detail->id, 'seen'=>true, 'updateStatus'=>'true']); break; } //new regularization eval
                        case 6: { $actionlink = action('UserCWSController@show',['id'=>$detail->relatedModelID, 'notif'=>$detail->id, 'seen'=>true, 'updateStatus'=>'true']); break; } //change position
                        case 7: { $actionlink = action('UserOTController@show',['id'=>$detail->relatedModelID, 'notif'=>$detail->id, 'seen'=>true, 'updateStatus'=>'true']); break; } //change position
                     
                      }?>
                      <a href="{{$actionlink}} ">
                        <i class="<?php echo $detail->info->icon?>"></i>
                        <?php echo $detail->info->title?> <br/><small> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $detail->info->detail; ?> <em><?php echo Carbon\Carbon::now()->diffForHumans($detail->created_at, true); ?> ago</em> </small>
                      </a>
                    </li>



                   <?php  } ?>


                  @if (!Auth::user()->updatedPass)

                  <li class="bg-warning"><!-- start notification -->
                      <a href="{{action('UserController@changePassword')}} ">
                        <i class="fa fa-key"></i>
                        Change your default password <br/><small> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; for security purposes <em><?php echo Carbon\Carbon::now()->diffForHumans(Auth::user()->created_at, true); ?> ago</em> </small>
                      </a>
                    </li>

                  @endif

                  

                    
                  

                  
                  <!-- end notification -->
                </ul>
              </li>
              <li class="footer"><a href="{{action('NotificationController@index')}} ">View all</a></li>
             <?php } else { ?>

             @if(!Auth::user()->updatedPass)
             <li class="header">1 new reminder</li>
              <li>
                <!-- Inner Menu: contains the notifications -->
                <ul class="menu">

                 
                 <li class="bg-warning"><!-- start notification -->
                      <a href="{{action('UserController@changePassword')}} ">
                        <i class="fa fa-key"></i>
                        Change your default password <br/><small> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; for security purposes. <em><?php echo Carbon\Carbon::now()->diffForHumans(Auth::user()->created_at, true); ?> ago</em> </small>
                      </a>
                    </li>
                  
                  <!-- end notification -->
                </ul>
              </li>
              <li class="footer"><a href="{{action('NotificationController@index')}} ">View Past Notifications</a></li>

              

             @else

              <li class="header">No new notifications</li>
              <li>
                <!-- Inner Menu: contains the notifications -->
                <ul class="menu">

                 
                  <li><!-- start notification -->
                    
                  </li>
                  
                  <!-- end notification -->
                </ul>
              </li>
              <li class="footer"><a href="{{action('NotificationController@index')}} ">View Past Notifications</a></li>


             @endif

             
             


              <?php } ?>
            </ul>
          </li>
         
          <!-- User Account Menu -->
          <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <!-- The user image in the navbar-->

               @if ( file_exists('public/img/employees/'.Auth::user()->id.'.jpg') )
              <img src="{{asset('public/img/employees/'.Auth::user()->id.'.jpg')}}" class="user-image" alt="User Image">
              @else
                <img src="{{asset('public/img/useravatar.png')}}" class="user-image" alt="User Image">

              @endif



             
              <!-- hidden-xs hides the username on small devices so only the image appears. -->
              <span class="hidden-xs"> {{Auth::user()->name}} </span>
            </a>
            <ul class="dropdown-menu">
              <!-- The user image in the menu -->
              <li class="user-header">

                 @if ( file_exists('public/img/employees/'.Auth::user()->id.'.jpg') )
              <img src="{{asset('public/img/employees/'.Auth::user()->id.'.jpg')}}" class="img-circle" alt="User Image">
              @else
                <img src="{{asset('public/img/oam_favicon1-55027f4ev1_site_icon-32x32.png')}}" class="img-circle" alt="User Image">

                @endif
                

                <p>
                 <strong> {{ Auth::user()->firstname }} {{ Auth::user()->lastname }} </strong> <br/><small><em> {{  Auth::user()->position->name }}</em><br/>
                  @if ( count(Auth::user()->campaign) > 1)

                    @foreach( Auth::user()->campaign as $camp)
                    <strong>{{$camp->name}} , </strong></small>
                    @endforeach
                 @else
                 <strong> {{  Auth::user()->campaign->first()->name }}</strong></small>

                 @endif
                  
                </p>
              </li>
              <!-- Menu Body -->
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="http://192.168.10.251/csl/login" target="_blank">Biometrics</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a target="_blank" href="http://192.168.0.51/accessone/login" target="_blank"> HRIS</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="http://oampayroll.openaccessbpo.com" target="_blank">Payroll</a>
                  </div>
                </div>
                <!-- /.row -->
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="{{action('UserController@show',Auth::user()->id)}}" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="{{ url('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>




  <div class="modal fade" id="message" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h2 class="modal-title" id="myModalLabel"> Welcome to OAMPI Evaluation System</h2>
        
      </div>
      <div class="modal-body">
        
        <p>Before anything else, please make sure you've already changed your default password for security purposes.</p>
        <p>Read thoroughly the guidelines and instructions when evaluating an employee.<br/> If you need help using the system, see the Quick User Guide: <br /><br/>
        <p class="text-center">  <a href="http://192.168.0.2/coffeebreak/wp-content/uploads/2017/01/Quick-User-Guide-OES.pdf" target="_blank" class="btn btn-sm btn-default btn-flat"><i class="fa fa-files-o"></i> View Quick User Guide</a>
        </p></p>
        <p><strong><br/><br/>For suggestions, system bugs, and/or technical concerns, </strong> please send an e-mail to: <a href="malto:mpamero@oampi.com" >mpamero@oampi.com</a></p>
      </div>
      <div class="modal-footer no-border">
            
         
        
        <button type="button" class="btn btn-primary" data-dismiss="modal">Got it!</button>
      </div>
    </div>
  </div>
</div>