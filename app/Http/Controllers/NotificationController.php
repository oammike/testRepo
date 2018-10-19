<?php

namespace OAMPI_Eval\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;
use \Mail;
use Carbon\Carbon;

use OAMPI_Eval\Http\Requests;
use OAMPI_Eval\User;
use OAMPI_Eval\User_Notification;
use OAMPI_Eval\Notification;
use OAMPI_Eval\UserType;
use OAMPI_Eval\NotifType;
use OAMPI_Eval\Team;
use OAMPI_Eval\Floor;
use OAMPI_Eval\ImmediateHead;
use OAMPI_Eval\ImmediateHead_Campaign;
use OAMPI_Eval\EvalType;
use OAMPI_Eval\RatingScale;
use OAMPI_Eval\EvalSetting;
use OAMPI_Eval\EvalForm;
use OAMPI_Eval\EvalDetail;
use OAMPI_Eval\Competency;
use OAMPI_Eval\Competency__Attribute;
use OAMPI_Eval\Attribute;
use OAMPI_Eval\Summary;
use OAMPI_Eval\PerformanceSummary;
use OAMPI_Eval\Movement;
use OAMPI_Eval\Campaign;
use OAMPI_Eval\Position;
use OAMPI_Eval\Status;
use OAMPI_Eval\PersonnelChange;
use OAMPI_Eval\Movement_ImmediateHead;
use OAMPI_Eval\Movement_Positions;
use OAMPI_Eval\Movement_Status;

class NotificationController extends Controller
{
    protected $user;
    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->middleware('auth');
        $this->user =  User::find(Auth::user()->id);
        $this->notification = $notification;
    }

     public function index()
    {
        //get all user's notif
        $yourNotif = $this->user->notifications->sortByDesc('id');

        $yourNotifs = new Collection;
        $coll = new Collection;

        foreach($yourNotif as $notif){

        	if($notif->detail->from !== null)
        	{ // ****** if notif has an actual requestor

            	if ($notif->detail->type == 5){ //if New Regularization eval, use ImmediateHeadCamp_id
                    //$fromData =ImmediateHead::find(ImmediateHead_Campaign::find($notif->detail->from)->immediateHead_id);
                    $fromData = User::find(EvalForm::find($notif->detail->relatedModelID)->user_id);
                    $from = $fromData->firstname." ".$fromData->lastname;
                    $position = $fromData->position->name;
                    $campaign = Campaign::find(Team::where('user_id', $fromData->id)->first()->campaign_id)->name;

              }else if ($notif->detail->type == 6 || $notif->detail->type == 7){ //if CWS request
                    //$fromData =ImmediateHead::find(ImmediateHead_Campaign::find($notif->detail->from)->immediateHead_id);
                    $fromData = User::find($notif->detail->from);
                    $from = $fromData->firstname." ".$fromData->lastname;
                    $position = $fromData->position->name;
                    $campaign = Campaign::find(Team::where('user_id', $fromData->id)->first()->campaign_id)->name;
              }else{
                    $fromData = ImmediateHead::find($notif->detail->from);
                    $from =  $fromData->firstname." ".$fromData->lastname;
                    $position = Position::find($fromData->userData->position_id)->name;

                    $camp = $fromData->campaigns;
                
                    $campaign =" ";

                    if(count($camp) > 1){
                            foreach ($camp as $c) {
                                $campaign .= $c->name.", ";
                            }
                            
                    } else $campaign = $camp->first()->name;

                     // TL requestor
                         if ( file_exists('public/img/employees/'.$fromData->userData->id.'.jpg') )
                         {
                            $img = asset('public/img/employees/'.$fromData->userData->id.'.jpg');
                            $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                         }
                         else{
                            $img = asset('public/img/useravatar.png');
                            $fromImage ='<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30" /> ';
                         }
                } 

  


            	
                
               

           

            } else { $from=null; $position=null; $campaign=null; $tlimage=null; } // **** notif is system generated, no requestor

            

        	switch($notif->detail->type)
            {
                case 1: { $actionlink = action('UserController@changePassword'); 
                			$message = "Kindly update your default password for security purposes. Thank you."; 

                			$fromImage =null;

                			break; 


                			} //change of password

                case 2: {
			                 // CHANGE OF PROGRAM DEPT
                   
			            	 //$tlConcerned =  ImmediateHead_Campaign::find(Movement::find($notif->detail->relatedModelID)->immediateHead_details->imHeadCampID_new);
                     //$personConcerned = ImmediateHead::find($tlConcerned->immediateHead_id);
                        $mvtDeets = Movement_ImmediateHead::where('movement_id',Movement::find($notif->detail->relatedModelID)->id)->get(); //->immediateHead_details; 

                        $coll->push($mvtDeets);
                        //$tlConcerned = ImmediateHead_Campaign::find($mvtDeets->imHeadCampID_new);
                        $personConcerned =  null; //ImmediateHead::find($mvtDeets->immediateHead_id);
			            	 
			            	 if($this->user->employeeNumber == $personConcerned)//->employeeNumber)
			            	 {

			            	 	$message = " has been transfered to your team. Click on the link above to learn more. ";
			            	 	$transfered = User::find(Movement::find($notif->detail->relatedModelID)->user_id);
			            	 	$from = $transfered->firstname." ".$transfered->lastname;
			            	 	$position = $transfered->position->name;
			            	 	if ( file_exists('public/img/employees/'.$transfered->id.'.jpg') )
					            	 {
					            	 	$img = asset('public/img/employees/'.$transfered->id.'.jpg');
					            	 	$fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
					            	 }
					            	 else{
					            	 	$img = asset('public/img/useravatar.png');
					            	 	$fromImage ='<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30" /> ';
					            	 }


			            	 } else $message = "requested a new employee movement. Click on the link above to learn more. ";

                             //check if interdepartment movement or not;
                             // if not then needs approval first

                             $canApprove = UserType::find($this->user->userType_id)->roles->where('label','APPROVE_MOVEMENTS')->first();

                             
                             //if( !Movement::find($notif->detail->relatedModelID)->withinProgram && $canApprove )
                                //$actionlink = action('MovementController@approve',['id' => $notif->detail->relatedModelID, 'notif'=>$notif->detail->id, 'seen' => true]);
                                // route('movement.approve', array('id' => $notif->detail->relatedModelID, 'notif'=>$notif->detail->id, 'seen' => true )); 
                                 $actionlink = route('movement.show', array('id' => $notif->detail->relatedModelID, 'notif'=>$notif->detail->id, 'seen' => true )); 
                            // else 
                            //     $actionlink = route('movement.show', array('id' => $notif->detail->relatedModelID, 'notif'=>$notif->detail->id, 'seen' => true )); 
                			
                			break; 

                			} //movement

                case 3: { $actionlink = action('MovementController@show',$notif->detail->relatedModelID ); 
                            $mvtDeets = Movement::find($notif->detail->relatedModelID); 
                            $requestor = ImmediateHead::find($mvtDeets->requestedBy); 

                            if($this->user->id == $mvtDeets->user_id) //if it's the employee concerned
                             {

                                $message = " has updated your position/job title";
                                $transfered = User::find(Movement::find($notif->detail->relatedModelID)->notedBy);

                                $from = $transfered->firstname." ".$transfered->lastname;
                                $position = $transfered->position->name;
                                if ( file_exists('public/img/employees/'.$transfered->id.'.jpg') )
                                     {
                                        $img = asset('public/img/employees/'.$transfered->id.'.jpg');
                                        $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                                     }
                                     else{
                                        $img = asset('public/img/useravatar.png');
                                        $fromImage ='<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30" /> ';
                                     }


                            } else if ( $this->user->employeeNumber == $requestor->employeeNumber ){ //if it's the TL who did the request

                                $message = " has approved your submitted PCN ";
                                $transfered = User::find(Movement::find($notif->detail->relatedModelID)->notedBy);

                                $from = $transfered->firstname." ".$transfered->lastname;
                                $position = $transfered->position->name;
                                if ( file_exists('public/img/employees/'.$transfered->id.'.jpg') )
                                     {
                                        $img = asset('public/img/employees/'.$transfered->id.'.jpg');
                                        $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                                     }
                                     else{
                                        $img = asset('public/img/useravatar.png');
                                        $fromImage ='<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30" /> ';
                                     }

                            } else $message = "requested a change in job title. Click on the link above to learn more. "; // notif for HR

                        break; 

                        } //change position
                case 4: {   $actionlink = action('MovementController@show',$notif->detail->id );
                            $mvtDeets = Movement::find($notif->detail->relatedModelID); 
                            $requestor = ImmediateHead::find($mvtDeets->requestedBy); 

                            if($this->user->id == $mvtDeets->user_id) //if it's the employee concerned
                             {

                                $message = " has updated your employement status ";
                                $transfered = User::find(Movement::find($notif->detail->relatedModelID)->notedBy);

                                $from = $transfered->firstname." ".$transfered->lastname;
                                $position = $transfered->position->name;
                                if ( file_exists('public/img/employees/'.$transfered->id.'.jpg') )
                                     {
                                        $img = asset('public/img/employees/'.$transfered->id.'.jpg');
                                        $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                                     }
                                     else{
                                        $img = asset('public/img/useravatar.png');
                                        $fromImage ='<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30" /> ';
                                     }


                            } else if ( $this->user->employeeNumber == $requestor->employeeNumber ){ //if it's the TL who did the request

                                $message = " has approved your submitted PCN ";
                                $transfered = User::find(Movement::find($notif->detail->relatedModelID)->notedBy);

                                $from = $transfered->firstname." ".$transfered->lastname;
                                $position = $transfered->position->name;
                                if ( file_exists('public/img/employees/'.$transfered->id.'.jpg') )
                                     {
                                        $img = asset('public/img/employees/'.$transfered->id.'.jpg');
                                        $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                                     }
                                     else{
                                        $img = asset('public/img/useravatar.png');
                                        $fromImage ='<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30" /> ';
                                     }

                            } else $message = "requested a new employee movement. Click on the link above to learn more. "; // notif for HR
                            break; 
                          }
                case 5: {   $actionlink = action('EvalFormController@show',['id'=>$notif->detail->relatedModelID, 'evaluatedBy'=>EvalForm::find($notif->detail->relatedModelID)->evaluatedBy, 'updateStatus'=>'true' ] ); //new Regularization eval
                            
                            $img = asset('public/img/employees/'.$fromData->id.'.jpg');
                            $fromImage =  $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                            $message = " is up for Employment Status update.";

                            break; 
                          }

                case 6: {   $actionlink = action('UserCWSController@show',['id'=>$notif->detail->relatedModelID, 'notif'=>$notif->detail->id, 'seen'=>'true' ] ); //new CWS
                            
                            $img = asset('public/img/employees/'.$fromData->id.'.jpg');
                            $fromImage =  $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                            $message = " requested a change in work schedule";

                            break; 
                          }

                case 7: {   $actionlink = action('UserOTController@show',['id'=>$notif->detail->relatedModelID, 'notif'=>$notif->detail->id, 'seen'=>'true' ] ); //new overtime
                            
                            $img = asset('public/img/employees/'.$fromData->id.'.jpg');
                            $fromImage =  $fromImage = '<img src="'.$img.'" class="user-image img-circle" alt="User Image" width="30"/> ';
                            $message = " filed a new Overtime";

                            break; 
                          }

            }

            

            $yourNotifs->push([ 'seen'=> $notif->seen, 
            	'title'=> NotifType::find($notif->detail->type)->title, 
            	'icon' =>NotifType::find($notif->detail->type)->icon, 
            	'from'=> $from,
            	'fromImage'=>$fromImage,
            	'message' =>$message,
            	'position'=>$position,  
            	'campaign'=> $campaign,
            	'created_at'=>$notif->created_at,
            	'ago'=>Carbon::now()->diffForHumans($notif->created_at, true), 
            	'actionlink'=>$actionlink]);


        }

        return $coll;

        return view('people.notification-index', compact('yourNotifs'));
    }
}
