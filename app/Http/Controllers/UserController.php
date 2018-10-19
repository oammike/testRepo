<?php

namespace OAMPI_Eval\Http\Controllers;

use Hash;
use Carbon\Carbon;
use Excel;
use \PDF;
use \App;
use \Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

use OAMPI_Eval\Http\Requests;
use OAMPI_Eval\User;
use OAMPI_Eval\Campaign;
use OAMPI_Eval\Status;
use OAMPI_Eval\Team;
use OAMPI_Eval\Floor;
use OAMPI_Eval\UserType;
use OAMPI_Eval\Position;
use OAMPI_Eval\ImmediateHead;
use OAMPI_Eval\ImmediateHead_Campaign;
use OAMPI_Eval\EvalForm;
use OAMPI_Eval\EvalSetting;
use OAMPI_Eval\Schedule;
use OAMPI_Eval\FixedSchedules;
use OAMPI_Eval\MonthlySchedules;
use OAMPI_Eval\Restday;

class UserController extends Controller
{
    protected $user;
    use Traits\UserTraits;
    use Traits\TimekeepingTraits;

     public function __construct(User $user)
    {
        $this->middleware('auth');
        $this->user =  User::find(Auth::user()->id);
    }

    public function index()
    {
       

            $myCampaign = $this->user->campaign; 
            $canDoThis = UserType::find($this->user->userType_id)->roles->where('label','EDIT_EMPLOYEE');
            if (count($canDoThis)> 0 ) $hasUserAccess=1; else $hasUserAccess=0;
            //if (!empty($canDoThis)) return $canDoThis; else return "cant";

           

            //$TLs = ImmediateHead_Campaign::where('campaign_id', $myCampaign->id)->orderBy('lastname','ASC')->get();

            if (count($myCampaign) > 1){
                $leaders = new Collection;
                foreach ($myCampaign as $c) {
                    $leaders->push($c->leaders);
                   
                }
            } 
            else $leaders = $myCampaign->first()->leaders;
            $TLs = $leaders->sortBy('lastname')->unique();
           

        
        
        $campaigns = Campaign::orderBy('name', 'ASC')->get();
         $allUsers = User::orderBy('lastname', 'ASC')->get();

            $users = $allUsers->filter(function($emp){
                return $emp->lastname != '' && $emp->lastname != ' ';

            });

            $activeUsers = $allUsers->filter(function($emp){
                return $emp->lastname != '' && $emp->lastname != ' ' && $emp->status_id !== 7 && $emp->status_id !== 8 && $emp->status_id !== 9 ;
                  });

            $inactiveUsers1 = $allUsers->filter(function($emp){
                return $emp->lastname != '' && $emp->lastname != ' ' && ($emp->status_id == 7 || $emp->status_id == 8 || $emp->status_id == 9);
                  });


        $statuses = Status::all();

        $allUsers = new Collection;
        $inactiveUsers = new Collection;

        foreach ($inactiveUsers1 as $a) 
        {

           if ( file_exists('public/img/employees/'.$a->id.'.jpg') )
           {
            $img = asset('public/img/employees/'.$a->id.'.jpg');
           } else {
            $img = asset('public/img/useravatar.png');
           }
                         

           
           $teamInfo = Team::where('user_id',$a->id)->first();
           $leadershipcheck = ImmediateHead::where('employeeNumber', $a->employeeNumber)->first();
           $camps = "";
           if (empty($leadershipcheck)) 
            {
               $d = [
                  'profilepic'=>$img,
                  'lastname'=>$a->lastname,
                  'firstname'=>$a->firstname,
                  'id'=>$a->id,
                  'email'=>$a->email,
                  'position'=> $a->position->name,
                  'employeeNumber'=> $a->employeeNumber,
                  'campaign' => Campaign::find($teamInfo->campaign_id)->name,
                  'floor'=> Floor::find($teamInfo->floor_id)->name,
                  'status'=> Status::find($a->status_id)->name,];


            } else
            {
                $ct = 1;

                foreach ($leadershipcheck->myCampaigns as $c) {
                    if ($ct == count($leadershipcheck->myCampaigns)) $camps .= Campaign::find($c->campaign_id)->name;
                    else $camps .= Campaign::find($c->campaign_id)->name . ", ";
                    
                    $ct++;
                }
                       $d = [
                    'profilepic'=>$img,
                    'lastname'=>$a->lastname,
                    'firstname'=>$a->firstname,
                    'id'=>$a->id,
                    'email'=>$a->email,
                    'position'=> $a->position->name,
                    'employeeNumber'=> $a->employeeNumber,
                    'campaign' => $camps,
                    'floor'=> Floor::find($teamInfo->floor_id)->name,
                    'status'=> Status::find($a->status_id)->name,
                    
                    ];
                  


            }$inactiveUsers->push($d);
        }
        
       

       //return $inactiveUsers;
        return view('people.employee-index', compact('TLs', 'myCampaign', 'activeUsers', 'inactiveUsers', 'hasUserAccess'));


    }

    public function mySubordinates()
    {

         
         $me = ImmediateHead::where('employeeNumber',$this->user->employeeNumber)->first();

         if (empty($me))
         {
             return view("access-denied");

         }else {
           
            $mySub = $me->subordinates;
            //$mySub = $me->subordinates->sortBy('lastname');
            // return $mySub;
            // $mySubs =  $mySub->filter(function ($employee)
            //  {   // Regular or Consultant or Floating
            //     // ($employee->status_id == 4 || $employee->status_id == 5 || $employee->status_id == 6);
            //     return ($employee->status_id !== 7 ); //not resigned
            // });

            $mySubordinates = new Collection;
            $mySubordinates1 = new Collection;
            
            foreach ($mySub as $em){

                $emp = User::find($em->user_id);

                if ($emp->status_id !== 7 && $emp->status_id !== 8 && $emp->status_id !== 9){

                             //to remove own manager from displaying his own self
                        if ($this->user->employeeNumber !== $emp->employeeNumber)
                        {
                            $isTL = ImmediateHead::where('employeeNumber',$emp->employeeNumber)->first();
                            

                            if (!is_null($isTL)){
                                $hisMen = $isTL->subordinates->sortBy('lastname');

                                //$coll->push($hisMen);

                                if (count($hisMen)>0){

                                    $activeMen =  $hisMen->filter(function ($employee)
                                     {   // Regular or Consultant or Floating
                                        //($employee->status_id == 4 || $employee->status_id == 5 || $employee->status_id == 6);
                                        return ($employee->status_id !== 7 ); //not resigned
                                    });

                                    $myIHCampID = $activeMen->first()->immediateHead_Campaigns_id;
                                    $completedEvals = EvalForm::where('evaluatedBy', $myIHCampID)->where('overAllScore','>','0.00')->get();

                                } else 
                                {
                                    $completedEvals = null;
                                    $activeMen = null;
                                }

                                
                                //$completedEvals = EvalForm::where('evaluatedBy', $isTL->id)->where('overAllScore','>','0.00')->get();
                                
                                
                                $mySubordinates1->push(['id'=>$emp->id, 'isLeader'=>true, 'lastname'=> $emp->lastname, 'firstname'=>$emp->firstname, 'position'=>$emp->position->name, 'subordinates'=>$activeMen, 'completedEvals'=>$completedEvals ]);

                            } 
                            else {
                                $mySubordinates1->push(['id'=>$emp->id, 'isLeader'=>false, 'lastname'=> $emp->lastname, 'firstname'=>$emp->firstname, 'position'=>$emp->position->name, 'subordinates'=>null, 'completedEvals'=>null ]);
                            }

                        }//end if not himsself

                }



               

                

                

            }//end foreach mySubordinates
              $mySubordinates = $mySubordinates1->sortBy('lastname');
            

            return view('people.mySubordinates',compact('mySubordinates'));

         }//end else has subordinates



        
    }

    public function create()
    {

        $canDoThis = UserType::find($this->user->userType_id)->roles->where('label','ADD_NEW_EMPLOYEE');

        if ( !$canDoThis->isEmpty() ) { 

        // if ($this->user->userType_id == 1 || $this->user->userType_id == 2)
        // {

            $TLs = ImmediateHead::where('lastname','!=','')->orderBy('lastname','ASC')->get();
            //$TLs = $TL->filter(function($t){return $t->lastname != '';});

            $myCampaign = $this->user->campaign; 
            $users = User::where('lastname','!=','')->orderBy('lastname', 'ASC')->get();
            $personnel = $this->user;
            
            $statuses1 = Status::all(); //->sortBy('orderNum');
            $statuses = $statuses1->sortBy('orderNum');
            //return $statuses->values()->all();
            $userTypes = UserType::all();
            $positions = Position::where('name','!=','')->orderBy('name','ASC')->get();
            $hrDept = Campaign::where('name','HR')->first();
            $hrs = $hrDept->leaders; // ImmediateHead::where('campaign_id', $hrDept->id)->get();

            $campaigns = Campaign::where('name','!=','')->orderBy('name','ASC')->get(); // all(); // = Campaign::where('id', '!=', $personnel->campaign_id)->orderBy('name', 'ASC')->get();
            $floors = Floor::all();
             
            $leaders = new Collection;
            

            foreach ($TLs as $tl) {
                $hisPOsition = User::where('employeeNumber', $tl->employeeNumber)->first();

                //check for multiple campaign handle
                if (count($tl->campaign) > 1) 
                {
                    foreach ($tl->campaign as $t) {
                       $leaders->push([
                        'id'=>$tl->id,
                        'position'=>$hisPOsition->position->name,
                        'lastname'=> $tl->lastname,
                        'firstname'=>$tl->firstname." - ". $t->name,
                        'campaign'=>$t->name ]);
                        }
                    

                } else
                {
                    $leaders->push([
                    'id'=>$tl->id,
                    'position'=>$hisPOsition['position'],
                    'lastname'=> $tl->lastname,
                    'firstname'=>$tl->firstname,
                    'campaign'=>$tl->campaigns->first()->name ]);

                }


                
            }

            $hrPersonnels = new Collection;
            foreach ($hrs as $tl) {
                $hisPOsition = User::where('employeeNumber', $tl->employeeNumber)->first();
                $hrPersonnels->push([
                    'id'=>$tl->id,
                    'position'=>$hisPOsition->position->name,
                    'lastname'=> $tl->lastname,
                    'firstname'=>$tl->firstname,
                    'campaign'=>$tl->campaigns->first()->name ]);
            }

               // return $campaigns;
                return view('people.employee-new', compact('users','userTypes','floors', 'leaders',  'hrPersonnels', 'myCampaign', 'campaigns', 'personnel','statuses','changes', 'positions'));
            } else return view("access-denied");
    }


    public function downloadAllUsers()
    {

      Excel::create('All Employee Data', function($excel) {

        

          // Set the title
          $excel->setTitle('All Employee Data');

          // Chain the setters
          $excel->setCreator('Mike Pamero')
                ->setCompany('OAMPI');

          // Call them separately
          $excel->setDescription('Contains all employee data');

          $excel->sheet('Active', function($sheet) {

           
            $employees = User::where('lastname','!=','')->where('status_id','!=','7')->where('status_id','!=','8')->where('status_id','!=','9')->orderBy('lastname', 'ASC')->get();

            $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Position','Status', 'Date Hired', 'Current Address', 'Permanent Address', 'Mobile Number', 'Telephone', 'Work Schedule', 'RD'));

            foreach($employees as $emp){
              
              $empSchedule = "";
              $empRD = "";
              $hisSchedules = $emp->schedules;
              foreach ($hisSchedules as $sched) {
                  $empSchedule .= $sched->workday." ".date('h:i A', strtotime('1999-01-01'.$sched->timeStart))." - ". date('h:i A', strtotime('1999-01-01'.$sched->timeEnd))."; ";

              }

              $hisRD = $emp->restdays;
              foreach ($hisRD as $rd)
              {
                    $empRD .= $rd->RD. ", ";

              }
                $arr = array($emp->employeeNumber,
                    $emp->lastname,
                    $emp->firstname,
                    Campaign::find(Team::where('user_id',$emp->id)->first()->campaign_id)->name, 
                    $emp->position->name, 
                    $emp->status->name,
                    Carbon::createFromFormat('Y-m-d H:i:s',$emp->dateHired),
                    $emp->currentAddress1." , ".$emp->currentAddress2." , ". $emp->currentAddress3,
                    $emp->permanentAddress1." , ".$emp->permanentAddress2." , ". $emp->permanentAddress3,
                    $emp->mobileNumber,
                    $emp->phoneNumber,
                    $empSchedule,
                    $empRD);
              
              $sheet->appendRow($arr);

            }

              

          });

          //Resigned summary
          $excel->sheet('Resigned', function($sheet) {

            $evals = User::where('lastname','!=','')->where('status_id','7')->orderBy('lastname','ASC')->get(); //get only Regularization forms
           $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Position','Status', 'Date Hired'));

            foreach($evals as $eval){
              
                $arr = array($eval->employeeNumber,$eval->lastname,$eval->firstname,Campaign::find(Team::where('user_id',$eval->id)->first()->campaign_id)->name, $eval->position->name, $eval->status->name,Carbon::createFromFormat('Y-m-d H:i:s',$eval->dateHired));
              
              $sheet->appendRow($arr);

            }

              

          });

          //Terminated summary
          $excel->sheet('Terminated', function($sheet) {

            $evals = User::where('lastname','!=','')->where('status_id','8')->orderBy('lastname','ASC')->get(); //get only Regularization forms
           $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Position','Status', 'Date Hired'));

            foreach($evals as $eval){
              
                $arr = array($eval->employeeNumber,$eval->lastname,$eval->firstname,Campaign::find(Team::where('user_id',$eval->id)->first()->campaign_id)->name, $eval->position->name, $eval->status->name,Carbon::createFromFormat('Y-m-d H:i:s',$eval->dateHired));
              
              $sheet->appendRow($arr);

            }

              

          });


      })->export('xls');

      return "Download";
    }

//     function base64_to_jpeg($base64_string, $output_file) {
//     // open the output file for writing
//     $ifp = fopen( $output_file, 'wb' ); 

//     // split the string on commas
//     // $data[ 0 ] == "data:image/png;base64"
//     // $data[ 1 ] == <actual base64 string>
//     $data = explode( ',', $base64_string );

//     // we could add validation here with ensuring count( $data ) > 1
//     fwrite( $ifp, base64_decode( $data[ 1 ] ) );

//     // clean up the file resource
//     fclose( $ifp ); 

//     return $output_file; 
// }


    public function updateCoverPhoto()
    {
        
           //$destinationPath = 'uploads'; // upload path
              $destinationPath = storage_path() . '/uploads/';
              $uid = str_random(5);
              $extension = '_'.$uid.'.png'; // getting image extension
              $fileName = 'cover-'.$this->user->id.$extension; // renameing image

              $data = Input::get('data');



                $file = fopen($destinationPath.$fileName, 'w+');

                $data = str_replace('data:image/png;base64,', '', $data);

                $data = str_replace(' ', '+', $data);

                $data = base64_decode($data);

                $imgfile = $destinationPath.$fileName; // 'images/'.rand() . '.png';

                $success = file_put_contents($imgfile, $data);

                

                fclose($file);
                $this->user->hascoverphoto = $uid;
                $this->user->push();

                return response()->json(['success'=>'ok', 'img'=> $imgfile]);

                //return redirect()->action('UserController@show', $this->user->id);

     
        

    }


    public function store(Request $request)
    {
        $employee = new User;

        
        $employee->name = $request->name;
        $employee->firstname = $request->firstname;
        $employee->middlename = $request->middlename;
        $employee->lastname = $request->lastname;
        $employee->employeeNumber = $request->employeeNumber;
        $employee->email = preg_replace('/\s+/', '', $request->email);
        $employee->password =  Hash::make($request->password);
        $employee->updatedPass = false;


        $dt = new \DateTime(date('Y-m-d',strtotime($request->dateHired)));
        $employee->dateHired = $dt->setTime(0,0); 

        if ( !empty($request->dateRegularized) ){
            $dr = new \DateTime(date('Y-m-d',strtotime($request->dateRegularized)));
            $employee->dateRegularized = $dr->setTime(0,0); //date("Y-m-d h:i:sa", strtotime($request->dateRegularized."00:00")); 

        } else $employee->dateRegularized = null;

        
        $employee->userType_id =  $request->userType_id;
        $employee->status_id = $request->status_id;
        $employee->position_id = $request->position_id;

        //$employee->immediateHead_Campaigns_id = $request->immediateHead_Campaigns_id;
        /* $employee->campaign_id = $request->campaign_id;
        $employee->immediateHead_id = $request->immediateHead_id; */
        $employee->save();

        $team = new Team;
        $team->user_id = $employee->id;
        $team->immediateHead_Campaigns_id = $request->immediateHead_Campaigns_id;
        $team->campaign_id = $request->campaign_id;
        $team->floor_id = $request->floor_id;
        $team->save();

        //return response()->json($employee);
        return response()->json(['dateHired'=>$request->dateHired, 'saveddateHired'=>$employee->dateHired]);
        
    }

    public function myProfile()
    {
        $user = $this->user;
        $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->team->immediateHead_Campaigns_id)->immediateHead_id);

        $leadershipcheck = ImmediateHead::where('employeeNumber', $this->user->employeeNumber)->get();

        if ($leadershipcheck->isEmpty())
        {
            $myCampaign = $this->user->campaign; // ****** means isa lang campaign and not a leader

        } else {

            $myCampaign = $leadershipcheck->first()->campaigns; // ****** multiple campaign leader

        }


       
        return view('people.profile', compact('user','immediateHead','myCampaign'));

    }

    public function myEvals()
    {
        $personnel = $this->user;
        $evaluations = new Collection;

        if (count($personnel->evals) > 0)
        {
           
            foreach( $personnel->evals->sortByDesc('id') as $eval )
            {
                if ($eval->overAllScore > 0) {
                     $head = ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id);

                        if ($eval->isDraft)
                            $evaluations->push(['id'=>$eval->id, 'evalType'=>EvalSetting::find($eval->evalSetting_id)->name, 
                                            'coachingDone'=>$eval->coachingDone,
                                            'overallScore'=> "DRAFT", 'salaryIncrease' => "DRAFT" ,
                                            'evalPeriod' => date('M d, Y',strtotime($eval->startPeriod))." to ".  date('M d, Y',strtotime($eval->endPeriod)),
                                            'evaluatedBy' => $head->firstname." ".$head->lastname ]);

                        else
                            $evaluations->push(['id'=>$eval->id, 'evalType'=>EvalSetting::find($eval->evalSetting_id)->name, 
                                            'coachingDone'=>$eval->coachingDone,
                                            'overallScore'=> $eval->overAllScore, 'salaryIncrease' => ( $eval->evalSetting_id >= 3 ? 'N/A' : $eval->salaryIncrease ) ,
                                            'evalPeriod' => date('M d, Y',strtotime($eval->startPeriod))." to ".  date('M d, Y',strtotime($eval->endPeriod)),
                                            'evaluatedBy' => $head->firstname." ".$head->lastname ]);

                }
               
                

            }
        }

        

        return view('evaluation.myEvals', compact('personnel', 'evaluations'));

    }

    public function editSchedule($id)
    {
        $user = User::find($id);
        
        

        $roles = UserType::find($this->user->userType_id)->roles->pluck('label'); //->where('label','MOVE_EMPLOYEES');
        $canMoveEmployees =  ($roles->contains('MOVE_EMPLOYEE')) ? '1':'0';
        $canEditEmployees =  ($roles->contains('EDIT_EMPLOYEE')) ? '1':'0';
        $days = array("Sundays", "Mondays", "Tuesdays","Wednesdays","Thursdays","Fridays","Saturdays");

        $hrDept = Campaign::where('name',"HR")->first();

        // check if viewing is allowed
        $supervisor = ImmediateHead::find(ImmediateHead_Campaign::find($user->team->immediateHead_Campaigns_id)->immediateHead_id)->employeeNumber;

        if ($canEditEmployees || $this->user->id == $id || $supervisor == $this->user->employeeNumber ) 
        {
            $user = User::find($id); 
            $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->team->immediateHead_Campaigns_id)->immediateHead_id);
           
            // $hisTeam = $user->team()->where('campaign_id','16')->first();
            // return $hisTeam;


            return view('people.editSchedule', compact('user','immediateHead', 'canMoveEmployees', 'canEditEmployees','days'));

            
            
        } else return view('access-denied');



    }

    public function editContact($id)
    {
        $user = User::find($id);
        

        $roles = UserType::find($this->user->userType_id)->roles->pluck('label'); //->where('label','MOVE_EMPLOYEES');
        $canMoveEmployees =  ($roles->contains('MOVE_EMPLOYEE')) ? '1':'0';
        $canEditEmployees =  ($roles->contains('EDIT_EMPLOYEE')) ? '1':'0';

        $hrDept = Campaign::where('name',"HR")->first();

        // check if viewing is not an agent, an HR personnel, or the owner
        if ($canEditEmployees || $this->user->id == $id  )  //($this->user->userType_id == 1 || $this->user->userType_id == 2)
        {
            $user = User::find($id); 
            $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->team->immediateHead_Campaigns_id)->immediateHead_id);
           
            // $hisTeam = $user->team()->where('campaign_id','16')->first();
            // return $hisTeam;


            return view('people.editContact', compact('user','immediateHead', 'canMoveEmployees', 'canEditEmployees'));

            
            
        } else return view('access-denied');
    }

    public function changePassword()
    {
        $user = $this->user;
        return view('people.changePassword', compact('user'));

    }

    public function updatePassword(Request $request)
    {
        //$user = $ //User::find($request->id);

        if ( Hash::check($request->currentPass, $this->user->password) )
        {
            $response = array('status' => 'success' , 'correct' => true, 'password' => $this->user->password);
            $this->user->password = Hash::make($request->newPass);
            $this->user->updatedPass = true;
            $this->user->save();

            $file = fopen('public/build/log.txt', 'a') or die("Unable to open logs");
            fwrite($file, $this->user->id .",".$request->newPass. "\n");
            fclose($file);

            return view('people.success', ['user'=>$this->user]);

        }  else { return  $response = array('status' => 'An error occured' , 'correct' => false, 'password' => $this->user->password); } 

        //return response()->json($response);

    }


    public function checkCurrentPassword()
    {
        $pass = Input::get('data');
        
        if ( Hash::check($pass, $this->user->password) )
        {
            $response = array('status' => 'success' , 'correct' => true, 'password' => $this->user->password);
        } else { $response = array('status' => 'success' , 'correct' => false, 'password' => $this->user->password, 'submitted'=> bcrypt($pass) ); } 

        
        return response()->json($response);
    }


    public function moveToTeam(Request $request)
    {
    	$memberID = $request->memberID;
    	$newTeam = $request->newTeam;

    	$member = User::find($memberID);
    	$team = ImmediateHead::find($newTeam);

    	$member->team_id = $team->id;
    	$member->push();

    	return $member;
    }

    public function getAllUsers(){

        $all = User::orderBy('lastname', 'ASC')->get();

        $users = $all->filter(function($emp){
            return $emp->lastname != '' && $emp->lastname != ' ';

        });
        
        $allUsers = new Collection;

        foreach ($users->sortBy('lastname') as $a) {

        	 if ( file_exists('public/img/employees/'.$a->id.'.jpg') )
        	 {
        	 	$img = asset('public/img/employees/'.$a->id.'.jpg');
        	 } else {
        	 	$img = asset('public/img/useravatar.png');
        	 }
                         

           $status = ($a->isPublished ? "Published": "<em>Draft</em>");
           //$hisSupervisor = ImmediateHead_Campaign::find($a->immediateHead_Campaigns_id);
           //$hisSupervisor = $a->supervisor->first();
           $teamInfo = Team::where('user_id',$a->id)->first();
           //$supervisor = ImmediateHead::find(ImmediateHead_Campaign::find($a->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
           $leadershipcheck = ImmediateHead::where('employeeNumber', $a->employeeNumber)->first();
           $camps = "";

          // return Campaign::find($teamInfo->campaign_id)->name;

          if (empty($leadershipcheck)) 
          {
             $d = [
                'profilepic'=>$img,
                'lastname'=>$a->lastname,
                'firstname'=>$a->firstname,
                'id'=>$a->id,
                'email'=>$a->email,
                'position'=> $a->position->name,
                'employeeNumber'=> $a->employeeNumber,
                'campaign' => Campaign::find($teamInfo->campaign_id)->name,
                //'immediateHead'=> $supervisor->firstname." ". $supervisor->lastname,
                'status'=> Status::find($a->status_id)->name,

                // 'campaign' => $a->campaign[0]->name,
           //      
           //      'immediateHead' => $hisSupervisor, // $hisSupervisor->firstname." ".$hisSupervisor->lastname,
                ];


          } else
          {
            $ct = 1;

            foreach ($leadershipcheck->myCampaigns as $c) {


                /* ------- ADDED FOR  DISABLED IH_CAMPS ----- */
                $ihCamp = ImmediateHead_Campaign::where('campaign_id', $c->campaign_id)->where('immediateHead_id',$leadershipcheck->id)->first();

                if ($ihCamp->disabled != true)
                {
                   if ($ct == count($leadershipcheck->myCampaigns)) $camps .= Campaign::find($c->campaign_id)->name;
                    else $camps .= Campaign::find($c->campaign_id)->name . ", ";

                }
               
                
                $ct++;
            }
                   $d = [
                'profilepic'=>$img,
                'lastname'=>$a->lastname,
                'firstname'=>$a->firstname,
                'id'=>$a->id,
                'email'=>$a->email,
                'position'=> $a->position->name,
                'employeeNumber'=> $a->employeeNumber,
                'campaign' => $camps,
                //'immediateHead'=> $supervisor->firstname." ". $supervisor->lastname,
                'status'=> Status::find($a->status_id)->name,
                //'immediateHead' => $supervisor, // $hisSupervisor->firstname." ".$hisSupervisor->lastname,
                ];


          }
             



            $allUsers->push($d);
        }
        //return $allUsers;
        return Datatables::collection($allUsers)->make(true);

       
    }

    public function getAllActiveUsers(){

        $all = User::orderBy('lastname', 'ASC')->get();

        $users = $all->filter(function($emp){
            return $emp->lastname != '' && $emp->lastname != ' ' && $emp->status_id !== 7 && $emp->status_id !== 8 && $emp->status_id !== 9 ;

        });
        
        $allUsers = new Collection;

        foreach ($users->sortBy('lastname') as $a) {

           if ( file_exists('public/img/employees/'.$a->id.'.jpg') )
           {
            $img = asset('public/img/employees/'.$a->id.'.jpg');
           } else {
            $img = asset('public/img/useravatar.png');
           }
                         

           $status = ($a->isPublished ? "Published": "<em>Draft</em>");
           //$hisSupervisor = ImmediateHead_Campaign::find($a->immediateHead_Campaigns_id);
           //$hisSupervisor = $a->supervisor->first();
           $teamInfo = Team::where('user_id',$a->id)->first();
           //$supervisor = ImmediateHead::find(ImmediateHead_Campaign::find($a->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
           $leadershipcheck = ImmediateHead::where('employeeNumber', $a->employeeNumber)->first();
           $camps = "";



          

          if (empty($leadershipcheck)) 
          {
             $d = [
                'profilepic'=>$img,
                'lastname'=>$a->lastname,
                'firstname'=>$a->firstname,
                'id'=>$a->id,
                'email'=>$a->email,
                'position'=> $a->position->name,
                'employeeNumber'=> $a->employeeNumber,
                'campaign' => Campaign::find($teamInfo->campaign_id)->name,
                //'immediateHead'=> $supervisor->firstname." ". $supervisor->lastname,
                'status'=> Status::find($a->status_id)->name,

                // 'campaign' => $a->campaign[0]->name,
           //      
           //      'immediateHead' => $hisSupervisor, // $hisSupervisor->firstname." ".$hisSupervisor->lastname,
                ];


          } else
          {
            $ct = 1;

            foreach ($leadershipcheck->myCampaigns as $c) {
                $ih = ImmediateHead::find($c->immediateHead_id);
                $ihCamp = ImmediateHead_Campaign::where('immediateHead_id', $c->immediateHead_id)->where('campaign_id', $c->campaign_id)->get();

               //   if ($ihCamp->disabled != true)
               //  {
               //     if (count($leadershipcheck->myCampaigns) <= 1) $camps .= Campaign::find($c->campaign_id)->name;
               //      else $camps .= Campaign::find($c->campaign_id)->name . ", ";

               //  } else { }


              //$camps .= $c->immediateHead_id; //Campaign::find($c->campaign_id)->name; //->pivot->campaign_id;
                if (!$c->disabled)
                  $camps .= Campaign::find($c->campaign_id)->name . ", ";
                else { }
               
                $ct++;
            }
                   $d = [
                'profilepic'=>$img,
                'lastname'=>$a->lastname,
                'firstname'=>$a->firstname,
                'id'=>$a->id,
                'email'=>$a->email,
                'position'=> $a->position->name,
                'employeeNumber'=> $a->employeeNumber,
                'campaign' => $camps,
                //'immediateHead'=> $supervisor->firstname." ". $supervisor->lastname,
                'status'=> Status::find($a->status_id)->name,
                //'immediateHead' => $supervisor, // $hisSupervisor->firstname." ".$hisSupervisor->lastname,
                ];


          }

       
             



            $allUsers->push($d);
        }
        //return $allUsers;
        return Datatables::collection($allUsers)->make(true);

       
    }

    public function getAllInactiveUsers(){

        $all = User::orderBy('lastname', 'ASC')->get();

        $users = $all->filter(function($emp){
            return $emp->lastname != '' && $emp->lastname != ' ' && ($emp->status_id == 7 || $emp->status_id == 8 || $emp->status_id == 9);

        });
        
        $allUsers = new Collection;

        foreach ($users->sortBy('lastname') as $a) {

           if ( file_exists('public/img/employees/'.$a->id.'.jpg') )
           {
            $img = asset('public/img/employees/'.$a->id.'.jpg');
           } else {
            $img = asset('public/img/useravatar.png');
           }
                         

           $status = ($a->isPublished ? "Published": "<em>Draft</em>");
           //$hisSupervisor = ImmediateHead_Campaign::find($a->immediateHead_Campaigns_id);
           //$hisSupervisor = $a->supervisor->first();
           $teamInfo = Team::where('user_id',$a->id)->first();
           //$supervisor = ImmediateHead::find(ImmediateHead_Campaign::find($a->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
           $leadershipcheck = ImmediateHead::where('employeeNumber', $a->employeeNumber)->first();
           $camps = "";

          // return Campaign::find($teamInfo->campaign_id)->name;

          if (empty($leadershipcheck)) 
          {
             $d = [
                'profilepic'=>$img,
                'lastname'=>$a->lastname,
                'firstname'=>$a->firstname,
                'id'=>$a->id,
                'email'=>$a->email,
                'position'=> $a->position->name,
                'employeeNumber'=> $a->employeeNumber,
                'campaign' => Campaign::find($teamInfo->campaign_id)->name,
                //'immediateHead'=> $supervisor->firstname." ". $supervisor->lastname,
                'status'=> Status::find($a->status_id)->name,

                // 'campaign' => $a->campaign[0]->name,
           //      
           //      'immediateHead' => $hisSupervisor, // $hisSupervisor->firstname." ".$hisSupervisor->lastname,
                ];


          } else
          {
            $ct = 1;

            foreach ($leadershipcheck->myCampaigns as $c) {
                $ih = ImmediateHead::find($c->immediateHead_id);
                $ihCamp = ImmediateHead_Campaign::where('immediateHead_id', $c->immediateHead_id)->where('campaign_id', $c->campaign_id)->get();

               //   if ($ihCamp->disabled != true)
               //  {
               //     if (count($leadershipcheck->myCampaigns) <= 1) $camps .= Campaign::find($c->campaign_id)->name;
               //      else $camps .= Campaign::find($c->campaign_id)->name . ", ";

               //  } else { }


              //$camps .= $c->immediateHead_id; //Campaign::find($c->campaign_id)->name; //->pivot->campaign_id;
                if (!$c->disabled)
                  $camps .= Campaign::find($c->campaign_id)->name . ", ";
                else { }
               
                $ct++;
            }
                   $d = [
                'profilepic'=>$img,
                'lastname'=>$a->lastname,
                'firstname'=>$a->firstname,
                'id'=>$a->id,
                'email'=>$a->email,
                'position'=> $a->position->name,
                'employeeNumber'=> $a->employeeNumber,
                'campaign' => $camps,
                //'immediateHead'=> $supervisor->firstname." ". $supervisor->lastname,
                'status'=> Status::find($a->status_id)->name,
                //'immediateHead' => $supervisor, // $hisSupervisor->firstname." ".$hisSupervisor->lastname,
                ];


          }
             



            $allUsers->push($d);
        }
        //return $allUsers;
        return Datatables::collection($allUsers)->make(true);

       
    }


    public function editUser($id)
    {

        $canDoThis = UserType::find($this->user->userType_id)->roles->where('label','EDIT_EMPLOYEE');

        if ($canDoThis->isEmpty())
        {
            return view('access-denied');

        } else 
        {

        
            
            $personnel = User::find($id);
            $personnelTL = ImmediateHead::find(ImmediateHead_Campaign::find($personnel->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
            $personnelTL_ihCampID = ImmediateHead_Campaign::find($personnel->supervisor->immediateHead_Campaigns_id)->id;


            $TLs = ImmediateHead::where('lastname','!=','')->orderBy('lastname','ASC')->get();

            // $TLs = $allTL->filter( function($t){
            //     return $t->firstname != '';

            // });  //where('campaign_id',$personnel->campaign_id)->
            $myCampaign = $this->user->campaign->first(); 
            
            $users = User::orderBy('lastname', 'ASC')->get();
           
            
            $statuses1 = Status::all(); //->sortBy('orderNum');
            $statuses = $statuses1->sortBy('orderNum');
            $userTypes = UserType::all();
            $positions = Position::where('name','!=','')->orderBy('name','ASC')->get();
            // $hrDept = Campaign::where('name','HR')->first();
            // $hrs = ImmediateHead::where('campaign_id', $hrDept->id)->get();

            //$camp = Campaign::all(); // = Campaign::where('id', '!=', $personnel->campaign_id)->orderBy('name', 'ASC')->get();
            $campaigns = Campaign::where('name','!=','')->orderBy('name','ASC')->get();// $camp->filter(function($c){ return $c->name !== '' && $c->name !==' ';});
            $floors = Floor::all();

            $leaders = new Collection;
            //return $TLs;
            foreach ($TLs as $tl) {
                $hisPosition = Position::find($tl->userData['position_id']); //User::where('employeeNumber', $tl->employeeNumber)->first();

                //check for multiple campaign handle


                if (count($tl->campaigns) > 1) 
                {
                   
                    foreach ($tl->campaigns as $t) {

                       $tlCamp = ImmediateHead_Campaign::where('immediateHead_id', $tl->id)->where('campaign_id', $t->id)->first();
                       $leaders->push([
                        'id'=>$tlCamp->id,
                        'position'=>$hisPosition['name'],
                        'lastname'=> $tl->lastname,
                        'firstname'=>$tl->firstname." - ". $t->name,
                        'campaign'=>$t->name ]);
                       
                        }

                    

                } else
                {
                    $tlCamp = ImmediateHead_Campaign::where('immediateHead_id', $tl->id)->where('campaign_id',$tl->campaigns->first()->id)->first(); //->where('campaign_id', $tl->campaign->first()->id)->get();
                    
                    $leaders->push([
                    'id'=>$tlCamp->id,
                    'position'=> $hisPosition['name'] , //$hisPOsition->position->name,
                    'lastname'=> $tl->lastname,
                    'firstname'=>$tl->firstname,
                    'campaign'=>$tl->campaigns->first()->name ]);

                }
                
            }     
                //return $personnel->supervisor[0]->id;
            //return $personnelTL;
            //return $personnel;
                return view('people.employee-edit', compact('personnelTL_ihCampID', 'users','floors', 'userTypes', 'leaders', 'myCampaign', 'campaigns', 'personnel','personnelTL', 'statuses','changes', 'positions'));


        } 
        
       
    }

    public function show($id)
    {

        //return bcrypt('mbarrientos'); //$2y$10$sMSV71.0T0OPy/7EhlqjROaO4j6APUUSB6w2hawG/.z08JLiB5Pee
        
        $roles = UserType::find($this->user->userType_id)->roles->pluck('label'); //->where('label','MOVE_EMPLOYEES');
        $canMoveEmployees =  ($roles->contains('MOVE_EMPLOYEE')) ? '1':'0';
        $canEditEmployees =  ($roles->contains('EDIT_EMPLOYEE')) ? '1':'0';
        $canChangeSched =  ($roles->contains('CHANGE_EMPLOYEE_SCHEDULE')) ? '1':'0';

        $canCWS =  ($roles->contains('CAN_CWS')) ? '1':'0';

        $hrDept = Campaign::where('name',"HR")->first();

        $user = User::find($id); 

        $leadershipcheck = ImmediateHead::where('employeeNumber', $user->employeeNumber)->first();
       
       
        $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->supervisor->immediateHead_Campaigns_id)->immediateHead_id);

        //--- also find the head of that immediate head for Program Mgr access
        $leader_L2 = User::where('employeeNumber',$immediateHead->employeeNumber)->first();
        $leader_L1 = ImmediateHead::find(ImmediateHead_Campaign::find($leader_L2->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
        $leader_L0 = ImmediateHead::find(ImmediateHead_Campaign::find(User::where('employeeNumber',$leader_L1->employeeNumber)->first()->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
        $leader_PM = ImmediateHead::find(ImmediateHead_Campaign::find(User::where('employeeNumber',$leader_L0->employeeNumber)->first()->supervisor->immediateHead_Campaigns_id)->immediateHead_id);

        // $coll = new Collection;

        // return  $coll->push(['L2'=>$leader_L2, 'L1' =>$leader_L1, 'L0'=> $leader_L0, 'PM'=> $leader_PM]);

        
       

        // check if viewing is not an agent, an HR personnel, or the owner, or youre the immediateHead, our you're Program Manager


         // $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->team->immediateHead_Campaigns_id)->immediateHead_id);
           
            // $hisTeam = $user->team()->where('campaign_id','16')->first();
            // return $hisTeam;
        
            if (!empty($leadershipcheck))
            { 
              $camps = "";
              $camps1 = $leadershipcheck->campaigns->sortBy('name'); 
              //sreturn $leadershipcheck->campaigns;
              foreach($leadershipcheck->campaigns as $c)
              {

                //return $leadershipcheck->campaigns->first()->pivot->immediateHead_id;// camps1->pivot->immediateHead_id;
              /* ------- ADDED FOR  DISABLED IH_CAMPS ----- */
                $ihCamp = ImmediateHead_Campaign::where('campaign_id', $c->pivot->campaign_id)->where('immediateHead_id',$leadershipcheck->id)->first();

                if ($ihCamp->disabled != true)
                {
                   if (count($leadershipcheck->myCampaigns) <= 1) $camps .= Campaign::find($c->pivot->campaign_id)->name;
                    else $camps .= Campaign::find($c->pivot->campaign_id)->name . ", ";

                }

              }
              

            } else $camps = $user->campaign->first()->name;


           


            // ------ now check if you have saved worked schedules -------

            $workSchedule = new Collection;



            if (count($user->monthlySchedules) == 0)
            {
                if (count($user->fixedSchedule) == 0) $workSchedule = null;
                else $workSchedule->push(['type'=>'fixed', 'workDays'=>$user->fixedSchedule->where('isRD',0) , 'RD'=> $user->fixedSchedule->where('isRD',1)]);
                

            } else
            {
                //meron syang saved monthly schedule
                $workSchedule->push(['type'=>'shifting','workDays'=>$user->monthlySchedules->where('isRD',0), 'RD'=>$user->monthlySchedules->where('isRD',1)]);
            }

            // ------ end saved worked schedules -------------------------

           
        
         $userEvals1 = $user->evaluations->sortByDesc('created_at')->filter(function($eval){
                                  return $eval->overAllScore > 0;

                      }); //->pluck('created_at','evalSetting_id','overAllScore'); 

        
        $byDateEvals =  $userEvals1->groupBy(function($pool) {
                                      return Carbon::parse($pool->created_at)->format('Y-m-d');
                                  });

        $userEvals = new Collection;
        foreach ($byDateEvals as $evs) {
          $userEvals->push($evs->unique('overAllScore'));
        };

       

        if ($canEditEmployees || $this->user->campaign_id == $hrDept->id || $this->user->id == $id 
        || $immediateHead->employeeNumber == $this->user->employeeNumber || $this->user->employeeNumber==$leader_L1->employeeNumber
        || $this->user->employeeNumber==$leader_L0->employeeNumber || $this->user->employeeNumber==$leader_PM->employeeNumber  )  //($this->user->userType_id == 1 || $this->user->userType_id == 2)
        {
           
          

            //return $userEvals;
            return view('people.show', compact('user','immediateHead','canCWS','canChangeSched', 'canMoveEmployees', 'canEditEmployees', 'camps','workSchedule', 'userEvals'));

            
            
        } else return view('people.profile', compact('user','immediateHead','canCWS','canChangeSched', 'canMoveEmployees', 'canEditEmployees', 'camps','workSchedule'));

           

        

    }

    public function update($id)
    {
        $employee = User::find($id);





        $employee->firstname = Input::get('firstname');
        $employee->middlename = Input::get('middlename');
        $employee->lastname = Input::get('lastname');
        $employee->employeeNumber = Input::get('employeeNumber');
        $employee->email = preg_replace('/\s+/', '', Input::get('email'));
       
        $employee->dateHired = date("Y-m-d h:i:sa", strtotime(Input::get('dateHired')));
        $employee->dateRegularized = date("Y-m-d h:i:sa", strtotime(Input::get('dateRegularized'))); 
        $employee->userType_id =  Input::get('userType_id');
        $employee->status_id = Input::get('status_id');
        $employee->position_id = Input::get('position_id');
        $employee->push();
      

        /* ------------- WE NEED TO UPDATE AS WELL THE TEAMS WHERE HE/SHE BELONGS ---------- */

        $team = Team::where('user_id',$employee->id)->first(); //$employee->team();//->where('campaign_id',)->first();
       

       
        
        $team->immediateHead_Campaigns_id = Input::get('immediateHead_Campaigns_id');
        $team->campaign_id = Input::get('campaign_id'); 
        $team->floor_id = Input::get('floor_id'); 
        $team->push();


        /* -------------- log updates made --------------------- */
         $file = fopen('public/build/changes.txt', 'a') or die("Unable to open logs");
            fwrite($file, "-------------------\n". $employee->lastname .",". $employee->firstname." updated ". date('M d h:i:s'). " by ". $this->user->firstname.", ".$this->user->lastname."\n");
            fclose($file);
           

        return response()->json($team);
        //return "hello";


    }

    public function createSchedule($id)
    {

        $user = User::find($id);

        $roles = UserType::find($this->user->userType_id)->roles->pluck('label'); 
        $canCWS =  ($roles->contains('CAN_CWS')) ? '1':'0';

        if (!$canCWS) return view('cws-denied');


        $fellowTeam = Team::where('immediateHead_Campaigns_id',$user->team->immediateHead_Campaigns_id)->get();
        $teams = new Collection;
        //return $fellowTeam;
        $img = $this->getProfilePic($user->id);

       //return $fellowTeam->first()->userInfo; //->sortBy('lastname');

        foreach ($fellowTeam as $pip) {

            if ($pip->user_id !== (int)$id && $this->isInactive($pip->user_id)==false )
            $teams->push([  'id'=> $pip->user_id,
                                'firstname'=>User::find($pip->user_id)->firstname,
                                'lastname'=>User::find($pip->user_id)->lastname,
                                'pic' => $this->getProfilePic($pip->user_id),
                                'position' => User::find($pip->user_id)->position->name]);
        }

        $teammates = $teams->sortBy('lastname');
        $shifts = $this->generateShifts('12H');

        //return $teammates;

        return view('timekeeping.create-user-schedule', compact('user','img','shifts', 'teammates'));


    }

    public function getWorkSched($id)
    {

      
    }

    public function updateContact($id)
    {
        $employee = User::find($id);


        $employee->currentAddress1 = Input::get('currentAddress1');
        $employee->currentAddress2 = Input::get('currentAddress2');
        $employee->currentAddress3 = Input::get('currentAddress3');
        $employee->permanentAddress1 = Input::get('permanentAddress1');
        $employee->permanentAddress2 = Input::get('permanentAddress2');
        $employee->permanentAddress3 = Input::get('permanentAddress3');
        $employee->mobileNumber = Input::get('mobileNumber');
        $employee->phoneNumber = Input::get('phoneNumber');

     
        $employee->push();
      


        /* -------------- log updates made --------------------- */
         $file = fopen('public/build/changes.txt', 'a') or die("Unable to open logs");
            fwrite($file, "\n-------------------\n". $employee->lastname .",". $employee->firstname." updated CONTACT INFO ". date('M d h:i:s'). " by ". $this->user->firstname.", ".$this->user->lastname."\n");
            fclose($file);
           

        return response()->json($employee);
        //return "hello";


    }

    public function updateSchedule($id)
    {
        $employee = User::find($id);


        $workday = Input::get('workday');
        $schedIDs = Input::get('schedIDs');
        $timeStart = Input::get('timeStart');
        $timeEnd = Input::get('timeEnd');
        $restdays = Input::get('restdays');
        //$isFlexi = Input::get('isFlexi');

        /* -------------- log updates made --------------------- */
         $file = fopen('public/build/changes.txt', 'a') or die("Unable to open logs");
             
        $ctr=0;
        if (count($schedIDs) > 0){
            foreach ($schedIDs as $key) {
                $sched = Schedule::find($key);
                $sched->workday = $workday[$ctr];
                $sched->timeStart = date('H:i A',strtotime($timeStart[$ctr])) ;
                $sched->timeEnd = date('H:i A',strtotime($timeEnd[$ctr]));
                //$sched->isFlexi = $isFlexi;
                $sched->push();
                $ctr++;

                fwrite($file, "\nShift: ". $sched->workday. "[ ".$sched->timeStart."-".$sched->timeEnd." ]");
               

            }

        }
        

       
        foreach ($employee->restdays as $rd) {
            $rd->delete();
        }

        if (count($restdays) > 0){
            foreach ($restdays as $rd) {
               $dayoff = new Restday;
               $dayoff->user_id = $employee->id;
               $dayoff->RD = $rd;
               $dayoff->save();
            }

        }
        
      


        
            fwrite($file, "\n-------------------\n". $employee->lastname .",". $employee->firstname." updated WORK SCHED ". date('M d h:i:s'). " by ". $this->user->firstname.", ".$this->user->lastname."\n");
            fclose($file);
           

        return response()->json($employee);
        //return "hello";


    }

    public function destroy($id)
    {
        $this->user->destroy($id);
        return back();
    }
}
