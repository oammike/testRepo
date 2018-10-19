<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



Route::auth();
Route::group( [ 'middleware' => ['auth'] ], function () 
  { 
  	Route::get('/', 'HomeController@index');
    //Route::get('/', ['UserController@show',Auth::]);
    Route::get('/home', 'HomeController@index');

  	
  	Route::resource('immediateHead', 'ImmediateHeadController');
    Route::resource('immediateHeadCampaign', 'ImmediateHeadCampaignController');
  	Route::resource('evalForm', 'EvalFormController');
  	Route::resource('evalSetting', 'EvalSettingController');
  	Route::resource('campaign', 'CampaignController');
  	Route::resource('page', 'HomeController');
    Route::resource('user','UserController');
    Route::resource('userType','UserTypeController');
    //Route::resource('userType_role','UserTypeRController');
    Route::resource('movement','MovementController');
    Route::resource('position','PositionController');
    Route::resource('role','RoleController');
    Route::resource('notification','NotificationController');
    
    Route::resource('schedule','ScheduleController');
    Route::resource('cutoffs','CutoffsController');
    Route::resource('biometrics','BiometricsController');
    Route::resource('tempUpload','TempUploadController');
    Route::resource('logs','LogsController');
    Route::resource('user_dtr','DTRController');
    Route::resource('user_cws','UserCWSController');
    Route::resource('user_ot','UserOTController');
    Route::resource('fixedSchedule','FixedScheduleController');
    Route::resource('monthlySchedule','MonthlyScheduleController');
    Route::resource('taxstatus','TaxstatusController');



    Route::get('/module', array(
      'as'=> 'page.module',
      'uses'=>'HomeController@module') );

    Route::post('/saveDailyUserLogs', array(
      'as'=> 'logs.saveDailyUserLogs',
      'uses'=>'LogsController@saveDailyUserLogs') );

    Route::get('/view-raw-biometrics-data/{id}', array(
      'as'=> 'logs.saveDailyUserLogs',
      'uses'=>'LogsController@viewRawBiometricsData') );

    Route::get('/myDTR', array(
      'as'=> 'user_dtr.myDTR',
      'uses'=>'DTRController@myDTR') );

  	
    Route::post('/tempUpload/purge', array(
      'as'=> 'tempUpload.purge',
      'uses'=>'TempUploadController@purge') );

    Route::post('/tempUpload/purgeThis', array(
      'as'=> 'tempUpload.purgeThis',
      'uses'=>'TempUploadController@purgeThis') );

    Route::post('/biometrics/upload', array(
      'as'=> 'biometrics.upload',
      'uses'=>'BiometricsController@upload') );
    Route::get('/setupBiometricUserLogs', array(
      'as'=> 'biometrics.setupBiometricUserLogs',
      'uses'=>'BiometricsController@setupBiometricUserLogs') );
    

    Route::get('/evalForm/new/{user_id}/{evalType_id}', array(
			'as'=> 'newEvaluation',
			'uses'=>'EvalFormController@newEvaluation') );

    Route::post('/evalForm/grabAllWhosUpFor', array(
      'as'=> 'evalForm.grabAllWhosUpFor',
      'uses'=>'EvalFormController@grabAllWhosUpFor') );

     Route::get('/evalForm/review/{id}', array(
      'as'=> 'evalForm.review',
      'uses'=>'EvalFormController@review') );

      Route::post('/evalForm/updateReview/{id}', array(
      'as'=> 'evalForm.updateReview',
      'uses'=>'EvalFormController@updateReview') );

      Route::post('/immediateHeadCampaign/disable/{id}', array(
      'as'=> 'immediateHeadCampaign.disable',
      'uses'=>'ImmediateHeadCampaignController@disable') );


    Route::get('/download', array(
      'as'=> 'downloadReport',
      'uses'=>'EvalFormController@downloadReport') );

     Route::get('/downloadAllUsers', array(
      'as'=> 'downloadAllUsers',
      'uses'=>'UserController@downloadAllUsers') );

    Route::get('/evalForm/blank/{id}', array(
      'as'=> 'printBlankEval',
      'uses'=>'EvalFormController@printBlankEval') );

    Route::get('/evalForm/blankEmployee/{id}', array(
      'as'=> 'printBlankEmployee',
      'uses'=>'EvalFormController@printBlankEmployee') );



    Route::get('/evalForm/print/{id}', array(
      'as'=> 'printEval',
      'uses'=>'EvalFormController@printEval') );


    Route::get('/immediateHead/{id}/members', array(
          'as'=> 'getMembers',
          'uses'=>'ImmediateHeadController@getMembers') );

    Route::get('/campaign/{id}/leaders', array(
          'as'=> 'getAllLeaders',
          'uses'=>'CampaignController@getAllLeaders') );

    

     Route::get('/getOtherTeams', array(
          'as'=> 'getOtherTeams',
          'uses'=>'ImmediateHeadController@getOtherTeams') );

      Route::get('/getAllCampaigns', array(
          'as'=> 'getAllCampaigns',
          'uses'=>'CampaignController@getAllCampaigns') );

     Route::post('/updateMovement', array(
          'as'=> 'updateMovement',
          'uses'=>'MovementController@updateMovement') );

     
    Route::get('/movement/createNew/{id}', array(
          'as'=> 'createNew',
          'uses'=>'MovementController@createNew') );

    Route::post('/movement/approve/{id}', array(
          'as'=> 'movement.approve',
          'uses'=>'MovementController@approve') );

     Route::post('/movement/noted/{id}', array(
          'as'=> 'noted',
          'uses'=>'MovementController@noted') );

      Route::get('/movement/printPDF/{id}', array(
      'as'=> 'printPDF',
      'uses'=>'MovementController@printPDF') );



    Route::get('/getAllMovements', array(
          'as'=> 'getAllMovements',
          'uses'=>'MovementController@getAllMovements') );


     Route::get('/movement/changePersonnel/{id}', array(
          'as'=> 'changePersonnel',
          'uses'=>'MovementController@changePersonnel') );


     Route::post('/movement/findInstances', array(
          'as'=> 'findInstances',
          'uses'=>'MovementController@findInstances') );


     Route::post('/moveToTeam', array(
          'as'=> 'toTeam',
          'uses'=>'MovementController@toTeam') );

     Route::post('/employeeMovement', array(
          'as'=> 'moveToTeam',
          'uses'=>'UserController@moveToTeam') );

     Route::get('/user/{id}/createSchedule/', array(
          'as'=> 'createSchedule',
          'uses'=>'UserController@createSchedule') );

      Route::get('/user/{id}/getWorkSched/', array(
          'as'=> 'getWorkSched',
          'uses'=>'UserController@getWorkSched') );



     Route::get('/immediateHead/{id}/members', array(
          'as'=> 'getMembers',
          'uses'=>'ImmediateHeadController@getMembers') );

     Route::get('/getAllUsers', array(
      'as'=> 'getAllUsers',
      'uses'=>'UserController@getAllUsers') );

     Route::get('/getAllActiveUsers', array(
      'as'=> 'getAllActiveUsers',
      'uses'=>'UserController@getAllActiveUsers') );

      Route::get('/getAllInactiveUsers', array(
      'as'=> 'getAllInactiveUsers',
      'uses'=>'UserController@getAllInactiveUsers') );

     Route::get('/editUser/{id}', array(
      'as'=> 'editUser',
      'uses'=>'UserController@editUser') );

     Route::get('/editSchedule/{id}', array(
      'as'=> 'editSchedule',
      'uses'=>'UserController@editSchedule') );

      Route::get('/editContact/{id}', array(
      'as'=> 'editContact',
      'uses'=>'UserController@editContact') );

       Route::post('/updateContact/{id}', array(
      'as'=> 'updateContact',
      'uses'=>'UserController@updateContact') );

       Route::post('/updateSchedule/{id}', array(
      'as'=> 'user.updateSchedule',
      'uses'=>'UserController@updateSchedule') );


       Route::post('/updateCoverPhoto', array(
      'as'=> 'user.updateCoverPhoto',
      'uses'=>'UserController@updateCoverPhoto') );


     Route::get('/myProfile', array(
      'as'=> 'myProfile',
      'uses'=>'UserController@myProfile') );

     Route::get('/myEvals', array(
      'as'=> 'myEvals',
      'uses'=>'UserController@myEvals') );

     Route::get('/mySubordinates', array(
      'as'=> 'mySubordinates',
      'uses'=>'UserController@mySubordinates') );

     Route::get('/changePassword', array(
      'as'=> 'changePassword',
      'uses'=>'UserController@changePassword') );

     Route::post('/checkCurrentPassword', array(
      'as'=> 'user.checkCurrentPassword',
      'uses'=>'UserController@checkCurrentPassword') );

     Route::post('/updatePassword', array(
      'as'=> 'user.updatePassword',
      'uses'=>'UserController@updatePassword') );

     Route::post('/updateContact/{id}', array(
      'as'=> 'user.updateContact',
      'uses'=>'UserController@updateContact') );

    



  });


