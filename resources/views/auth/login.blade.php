@extends('layouts.single')

@section('metatags')
<title>Login to OAMPI Evaluation System</title>
@endsection

@section('bodyClasses')
 skin-green login-page
@stop



@section('content')<br/><br/>
<div class="login-box">
  
  <!-- /.login-logo -->
  <div class="login-box-body"><div class="login-logo"><a href="{{ action('HomeController@index')}} ">
    <img src="{{ asset('public/img/eval-login-logo.png')}}" style="margin: 0 auto;" /><br/></a>
  </div><br/><br/><br/>
    <p class="login-box-msg">Sign in to your <strong> OAMPI</strong> account <br/>
   </p>
    <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Zimbra E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-btn fa-sign-in"></i> Login
                                </button>
                                <div class="clearfix"><br/><br/></div>

                                <small class="text-center"><a  style="color:#e9d37c" href="{{ url('/password/reset') }}">Forgot Your Password?</a> &nbsp;|&nbsp; 
                                <a style="color:#e9d37c" href="{{ url('/register') }}">Register Now</a></small>
                            </div>
                        </div>
                    </form>

     
    

    

  </div>
  <!-- /.login-box-body -->
 
</div>



@endsection
