
<h2><img src="{{asset('public/img/logo-transparent.png')}} " width="70"/><img src="{{asset('public/img/eval-login-logo.png')}} "/> <br/>
	<br/> Personnel Change Notice</h2>


<p>New employee has been moved to your team: <br/><br/>
   ------------------------------------------------------------<br/>

	Employee: <strong> {{$employee->lastname}}, {{$employee->firstname}} </strong><br/>
	Effectivity: <strong><?php echo date("M d, Y", strtotime($movement->effectivity)); ?></strong> <br/>
	 ------------------------------------------------------------<br/><br/><br/>

Please log in to  OAMPI Evaluation System for details.</p>
<p><a href="{{url('/')}}/movement/{{$movement->id}}?notif={{$notification->id}}&seen=1">{{action('MovementController@show',$movement->id)}}</a></p>