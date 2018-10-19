<?php

namespace OAMPI_Eval\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use Excel;
use \PDF;
use \App;
use \Response;
use Illuminate\Support\Facades\Redirect;

use OAMPI_Eval\Http\Requests;
use OAMPI_Eval\User;
use OAMPI_Eval\UserType;
use OAMPI_Eval\Campaign;
use OAMPI_Eval\Team;
use OAMPI_Eval\ImmediateHead;
use OAMPI_Eval\EvalType;
use OAMPI_Eval\Movement;
use OAMPI_Eval\RatingScale;
use OAMPI_Eval\EvalSetting;
use OAMPI_Eval\EvalForm;
use OAMPI_Eval\EvalDetail;
use OAMPI_Eval\Competency;
use OAMPI_Eval\Competency__Attribute;
use OAMPI_Eval\Attribute;
use OAMPI_Eval\Summary;
use OAMPI_Eval\PerformanceSummary;
use OAMPI_Eval\ImmediateHead_Campaign;
use OAMPI_Eval\Movement_ImmediateHead;

class EvalFormController extends Controller
{
    protected $user;
    protected $evalForm;

     public function __construct(EvalForm $evalForm)
    {
        $this->middleware('auth');
        $this->user =  User::find(Auth::user()->id);
        $this->evalForm = $evalForm;
    }



    public function index()
    {
        $allForms = EvalForm::where('overAllScore','!=','0')->orderBy('created_at','DESC')->get();
        $users = User::all();
        $campaigns = Campaign::all();
        $coll = new Collection;

        $evaluations = new Collection;

        foreach ($allForms as $eval) {
          if ( !$eval->details->isEmpty() )
            $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id);

          if (empty($evaluator)){

                if ($eval->evalSetting_id == 3) //regularization
                $evaluations->push(['id'=>$eval->id, 
                  'user_id'=>$eval->user_id, 
                  'increase'=> "N/A", 
                  'type'=>EvalSetting::find($eval->evalSetting_id)->name, 
                  'lastname'=> $eval->owner->lastname, 
                  'firstname'=> $eval->owner->firstname, 
                  'campaign'=> $eval->owner->campaign->first()->name, 
                  'head'=> null,
                  'score'=>$eval->overAllScore,
                  'dateEvaluated'=> $eval->created_at ]);

              else $evaluations->push(['id'=>$eval->id, 
                  'user_id'=>$eval->user_id, 
                  'increase'=> $eval->salaryIncrease, 
                  'type'=>EvalSetting::find($eval->evalSetting_id)->name, 
                  'lastname'=> $eval->owner->lastname, 
                  'firstname'=> $eval->owner->firstname, 
                  'campaign'=> $eval->owner->campaign->first()->name, 
                  'head'=> null,
                  'score'=>$eval->overAllScore,
                  'dateEvaluated'=> $eval->created_at ]);

          } else {
            //$forcampaign = Team::where('user_id', $eval->user_id)->where('immediateHead_Campaigns_id',$eval->evaluatedBy)->first();

            //(!empty($forcampaign)) ? $camp = Campaign::find($forcampaign->campaign_id)->name : $camp = null; //$eval->owner->campaign->first()->name;

            //$camp = Campaign::find(Team::where('user_id',$eval->user_id)->get(); //first()->campaign_id)->name;
            //$camp = Team::where('user_id',$eval->user_id)->get();
            //$coll->push(['user'=>User::find($eval->user_id)->lastname, 'camp'=>$camp]);

            if ($eval->evalSetting_id == 3) //REGULARIZATION
            {
              if ($eval->isDraft)
                $evaluations->push(['id'=>$eval->id, 
                  'user_id'=>$eval->user_id, 
                  'increase'=>"N/A", 
                  'type'=> date("Y", strtotime($eval->startPeriod)). " ". EvalSetting::find($eval->evalSetting_id)->name, 
                  'lastname'=> $eval->owner->lastname, 
                  'firstname'=> $eval->owner->firstname, 
                  'campaign'=> Campaign::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->campaign_id)->name, //$camp, //$eval->owner->campaign->first()->name, 
                  'head'=> $evaluator->firstname." ".$evaluator->lastname,
                  'score'=>"DRAFT",
                  'dateEvaluated'=> $eval->created_at]);
              
              else

                $evaluations->push(['id'=>$eval->id, 
                  'user_id'=>$eval->user_id, 
                  'increase'=>"N/A", 
                  'type'=>date("Y", strtotime($eval->startPeriod)). " ". EvalSetting::find($eval->evalSetting_id)->name, 
                  'lastname'=> $eval->owner->lastname, 
                  'firstname'=> $eval->owner->firstname, 
                  'campaign'=> Campaign::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->campaign_id)->name, //$camp, //$eval->owner->campaign->first()->name, 
                  'head'=> $evaluator->firstname." ".$evaluator->lastname,
                  'score'=>$eval->overAllScore,
                  'dateEvaluated'=> $eval->created_at ]);

            }  else {

                if ($eval->isDraft)
                  $evaluations->push(['id'=>$eval->id, 
                  'user_id'=>$eval->user_id, 
                  'increase'=>"DRAFT", 
                  'type'=>date("Y", strtotime($eval->startPeriod)). " ". EvalSetting::find($eval->evalSetting_id)->name, 
                  'lastname'=> $eval->owner->lastname, 
                  'firstname'=> $eval->owner->firstname, 
                  'campaign'=> Campaign::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->campaign_id)->name, //$eval->owner->campaign->first()->name, 
                  'head'=> $evaluator->firstname." ".$evaluator->lastname,
                  'score'=>"DRAFT",
                  'dateEvaluated'=>$eval->created_at ]);

                else
                  $evaluations->push(['id'=>$eval->id, 
                  'user_id'=>$eval->user_id, 
                  'increase'=>$eval->salaryIncrease, 
                  'type'=>date("Y", strtotime($eval->startPeriod)). " ". EvalSetting::find($eval->evalSetting_id)->name, 
                  'lastname'=> $eval->owner->lastname, 
                  'firstname'=> $eval->owner->firstname, 
                  'campaign'=> Campaign::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->campaign_id)->name, //$eval->owner->campaign->first()->name, 
                  'head'=> $evaluator->firstname." ".$evaluator->lastname,
                  'score'=>$eval->overAllScore,
                  'dateEvaluated'=> $eval->created_at ]);

              }
                

          }

            
        }
        //return $evaluations;
       // return $coll;
        return view('evaluation.index', compact('evaluations', 'campaigns'));
    }

    public function grabAllWhosUpFor(Request $request)
    {
        $me1 = ImmediateHead::where('employeeNumber',$this->user->employeeNumber)->first();
        $mc = ImmediateHead_Campaign::where('immediateHead_id',$me1->id)->get();
        $coll = new Collection;

        
          $me = $mc->first();



        $leadershipcheck = ImmediateHead::where('employeeNumber', $this->user->employeeNumber)->get();
        

        if ($this->user->userType_id !== 4 && !($leadershipcheck->isEmpty())) //if not AGENT, therefore may mga subordinates
        {
            $myActiveTeam = new Collection;

             

              $mySubs = ImmediateHead::find($me->immediateHead_id)->subordinates->sortBy('lastname');
               

               foreach ($mySubs as $k) {
                $emp = User::find($k->user_id);
                //7 - Resigned 8:Terminated; 9:Endo
                if ($emp->status_id !== 7 && $emp->status_id !== 8 && $emp->status_id !== 9 ) $myActiveTeam->push($emp);
               }

             
             
             
          
        } else { //else wala syang subordinates
            $employee = $this->user;
        }

        
        //return $mc;


        $mySubordinates = new Collection;
        $mySubordinates2 = new Collection;
       
         
        $evalTypes = EvalType::all();
        $evalSetting = EvalSetting::find($request->evalType_id);
        $doneEval = new Collection;




       /* -------- THIS IS A TEMPORARY SOLUTION TO HANDLE PERIODS ----- */

       

            switch ($request->evalType_id) {
                case 1: { //Jan-Jun semi-annual

                    $currentPeriod = Carbon::create((date("Y")), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
                    $endPeriod = Carbon::create((date("Y")), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');
                    //$me = ImmediateHead::where('employeeNumber',$this->user->employeeNumber)->first();

                     // $doneEval = new Collection;
                     
                      if ($this->user->userType_id !== 4 && !($leadershipcheck->isEmpty())) //if not AGENT
                      {
                            $mySubordinates1 =  $myActiveTeam->filter(function ($employee)
                                                                                {   // contractual or Regular or Consultant or Floating or Contractual- Extended
                                                                                    return ($employee->status_id == 1 || $employee->status_id == 4 || $employee->status_id == 5 || $employee->status_id == 6 || $employee->status_id == 10);
                                                                                });


                             foreach ($mySubordinates1->sortBy('lastname') as $emp) {

                               /* ------------

                                    We need to make sure emp is 6++ months already  */

                                    $hired = Carbon::createFromFormat('Y-m-d H:i:s', $emp->dateHired);

                                    $serviceLength = $hired->diffInMonths($endPeriod);

                                   

                                    if ($serviceLength >= 6) $mySubordinates2->push($emp);

                                    /* --------------- */
                            }

                            $coll = new Collection;
                            //return $mySubordinates2;
                            
                            foreach ($mySubordinates2->sortBy('lastname') as $emp) {


                                    /* ------------

                                    We need to check if this subordinate has just been moved to you

                                    ---------------*/

                                    //$checkMovement = User::find($emp->id)->movements;
                                    $checkMovement = Movement::where('user_id',$emp->id)->where('personnelChange_id','1')->where('isDone','1')->where('effectivity','>=',$currentPeriod->toDateString())->first();//->where('effectivity','<=',$endPeriod->toDateString())
                                   
                                    


                                    if (!empty($checkMovement)){
                                       //$existing = EvalForm::where('user_id', $emp->id)->where('startPeriod',$currentPeriod)->get();
                                        $effective = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');

                                        //pag yung movement from eh dateHired, meaning 1st time nya lang na-move..kunin mo yung effectivity start
                                        if ($checkMovement->fromPeriod == $emp->dateHired){
                                          $fr = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila'); 

                                        } 

                                        // **** GET THE EVAL RANGES, pag fromPeriod eh wayy past currentPeriod -- get current
                                        //else if($checkMovement->fromPeriod < $currentPeriod->toDateString()){
                                        else if ($checkMovement->fromPeriod < $currentPeriod->toDateString() ){ 

                                            //movement is within range pa, so kunin mo ung effectivity
                                            if ( ($checkMovement->effectivity <= $endPeriod->toDateString())  && ($checkMovement->effectivity >= $currentPeriod->toDateString()) ) 
                                              $fr =  Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila'); 
                                            else
                                             $fr = $currentPeriod->startOfDay();

                                        } else $fr = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');  //Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->fromPeriod, 'Asia/Manila'); 

                                        if($checkMovement->effectivity < $endPeriod->toDateString()){
                                            $to = $endPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                        } else $to = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');     

                                        /* ------------------ 

                                            check if hindi sakop ng eval period yung pagkakamove ni employee sa yo
                                            
                                            ------------------ */

                                            if ($checkMovement->effectivity > $endPeriod->startOfDay()){ //if effectivity ng movement eh hindi sakop

                                              $doNotInclude = true;

                                            } else { $doNotInclude=false; }
                                             //$coll->push(['doNotInclude'=> $doNotInclude]);



                                    } else { 
                                      $fr = $currentPeriod->startOfDay(); $to = $endPeriod->startOfDay(); $doNotInclude=false;

                                    }

                                    /* --OLD $existing = EvalForm::where('user_id', $emp->id)->where('evaluatedBy', $me->id)->where('endPeriod','<=',$to)->where('startPeriod','>=', $fr)->orderBy('id','DESC')->get(); */
                                    

                                    $evalBy = User::find($emp->id)->supervisor->immediateHead_Campaigns_id;
                                    $existing = EvalForm::where('user_id', $emp->id)->where('evaluatedBy', $evalBy)->where('endPeriod','<=',$to)->where('startPeriod','>=', $fr)->orderBy('id','DESC')->get();


                                 

                                    if (count($existing) == 0 ){

                                       if ($doNotInclude) { /* do nothing */}
                                        else { 
                                          $doneEval[$emp->id] = ['evaluated'=>0,'isDraft'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$fr->format('M d, Y'), 'endPeriod'=>$to->format('M d, Y')];
                                          $mySubordinates->push($emp);
                                        } 

                                    } 
                                    else {
                                        //$truegrade = 100-((100-(EvalForm::find( $existing->first()->id)->overAllScore))*0.5);
                                        $theeval = EvalForm::find( $existing->sortByDesc('id')->first()->id);
                                        $truegrade = $theeval->overAllScore;

                                        if ($theeval->isDraft) 
                                          $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                        else
                                        //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                        $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($existing->first()->startPeriod)), 'endPeriod'=>$endPeriod->format('M d, Y')];

                                        $mySubordinates->push($emp);
                                    } 


                            }//end foreach

                            

                           

                            /* ---------------------------------------------------------------- 

                                GET PAST MEMBERS moved to you

                            /* ---------------------------------------------------------------- */

                            
                          
                         
                              /*** OLD --- foreach ($me->myCampaigns as $m) { */
                                $changedImmediateHeads = new Collection;
                                $ctr = 0;
                                $doneMovedEvals = new Collection;
                                foreach($mc as $m){ 

                                  $moved = Movement_ImmediateHead::where('imHeadCampID_old',$m->id)->get();
                                      //$coll->push($moved);
                                      //$moved = Movement_ImmediateHead::where('imHeadCampID_old',$ihCamp->id)->get();
                                     

                                      $changedHeads = new Collection;
                                      $chIH = new Collection;

                                      //kunin mo muna info nung mga employees na namove from you
                                      foreach ($moved as $m) {
                                          $changedHeads->push($m->info);
                                      }
                                      
                                     
                                      //now check kung sakop ng cutoff period yung movement
                                      foreach ($changedHeads as $mvt) {
                                       
                                        $currentPeriod = Carbon::create((date("Y")), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
                                        $endPeriod = Carbon::create((date("Y")), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');
                                        $effective = Carbon::createFromFormat('Y-m-d H:i:s', $mvt->first()->effectivity, 'Asia/Manila');


                                        if  ($effective <= $endPeriod && $effective >= $currentPeriod && $mvt->isDone) 
                                          //$chIH->push($changedHeads->first());
                                          $chIH->push($mvt);
                                                                              
                                      }

                                      //$coll->push($chIH);


                                      // ** establish now the movement data
                                      foreach ($chIH as $emp) {
                                          $employ = User::find($emp->user_id);
                                          $hisTeam = $employ->team;
                                          $hisTL = ImmediateHead::find(Team::find($hisTeam->id)->leader->immediateHead_id);
                                          

                                          /* -------- we need to check first if YOU are the CURRENT TL. if yes,no need to be added to changedImmediateHeads  ----- */

                                          // if ($employ->supervisor->first()->immediateHead_id !== $me->id)
                                          // {
                                                  $changedImmediateHeads->push([
                                                                          'movement_id'=> $emp->id,
                                                                          'id'=>$employ->id, 
                                                                          'index'=> $ctr,
                                                                          'user_id'=>$employ->id, 
                                                                          'userType_id'=>$employ->userType_id, 
                                                                          'dateHired'=>$employ->dateHired, 
                                                                          'firstname'=> $employ->firstname, 
                                                                          'lastname'=>$employ->lastname, 
                                                                          'position'=> $employ->position->name, 
                                                                          'status'=>$employ->status->name]);

                                                
                                                  
                                                  
                                                  $effective = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila');

                                                  // **** GET THE EVAL RANGES, pag fromPeriod eh wayy past currentPeriod -- get current
                                                  if($emp->fromPeriod < $currentPeriod->startOfDay()){
                                                      $fr = $currentPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                                  } else $fr = Carbon::createFromFormat('Y-m-d H:i:s', $emp->fromPeriod, 'Asia/Manila'); 
                                                  $to = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila'); //


                                                  /* ## OLD: $evaluated = EvalForm::where('user_id', $emp->user_id)->where('evaluatedBy', $me->id)->where('startPeriod',$fr)->where('endPeriod',$to)->orderBy('id','DESC')->get(); //where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('id','DESC')->get();
                                                  
                                                  */
                                                  $evalBy = $me->id;  
                                                 //$coll->push(['from: '=>$fr, 'to: '=>$to->startOfDay()]);

                                                  $evaluated = EvalForm::where('user_id', $emp->user_id)->where('evaluatedBy', $evalBy)->where('startPeriod','>=',$fr)->get(); //->where('endPeriod','<=', $to->startOfDay())->get(); //->get(); //where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('id','DESC')->get();
                                                  
                                                  
                                               

                                                  if ( count($evaluated) == 0)
                                                  {
                                                      $doneMovedEvals[$ctr] = ['user_id'=>$emp->user_id,'evaluated'=>0,'isDraft'=>0, 'coachingDone'=>false, 'evalForm_id'=> null, 'score'=>null,'startPeriod'=>$fr->format('M d, Y'), 'endPeriod'=>$to->format('M d, Y')];
                                                      //$coll->push("waley");
                                                      //$coll->push($doneMovedEvals[$ctr]);
                                                      
                                                  } else {

                                                      $theeval = EvalForm::find( $evaluated->sortByDesc('id')->first()->id);
                                                      $truegrade = $theeval->overAllScore;

                                                      if ($theeval->isDraft) 
                                                        $doneMovedEvals[$ctr] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $evaluated->first()->id, 'score'=>$truegrade, 'startPeriod'=>$theeval->startPeriod, 'endPeriod'=>$theeval->endPeriod];
                                                      else
                                                      //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                                      $doneMovedEvals[$ctr] = ['evaluated'=>1, 'isDraft'=>0, 'evalForm_id'=> $theeval->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($theeval->startPeriod)), 'endPeriod'=>date('M d,Y',strtotime($theeval->endPeriod))];

                                                      //$col->push("meron");
                                                      //$coll->push($doneMovedEvals[$ctr]);


                                                      
                                                  }




                                          // }// end if you're not the current immediateHead

                                          
                                            $ctr++;
                                         
                                      }//end foreach chIH


                                  

                                      //$changedImmediateHeads->push($changedImmediateHeads1);
                                }//end foreach campaign
                             


                           //return $coll;
                           //return $changedImmediateHeads;
                         
                            return view('showThoseUpFor', compact('mySubordinates', 'evalTypes', 'evalSetting', 'doneEval','doneMovedEvals','changedImmediateHeads','currentPeriod','endPeriod'));

                      } else {

                            $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod->startOfDay())->get();
                            if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                            else {
                                //$truegrade = 100-((100-(EvalForm::find( $existing->first()->id)->overAllScore))*0.5);
                                $truegrade = EvalForm::find( $existing->sortByDesc('id')->first()->id)->overAllScore;
                                if ($truegrade == 0) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                else
                                $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                            }
                            return view('agentView-showThoseUpFor', compact('employee', 'myCampaign', 'evalTypes', 'evalSetting', 'doneEval')); 


                      }//end else not an agent

                   
                      

                        } break;

                case 2: { //Jul-Dec semi-annual

                      //check first if it's too early to show. If yes, year-1. If July-Dec na, show current year

                      if (date('m') >= 7 && date('m')<= 12)
                      {
                        $currentPeriod = Carbon::create((date("Y")), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
                        $endPeriod = Carbon::create((date("Y")), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');

                      } else
                      {
                        $currentPeriod = Carbon::create((date("Y")-1), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
                        $endPeriod = Carbon::create((date("Y")-1), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');

                      }

                      
                      //$me = ImmediateHead::where('employeeNumber',$this->user->employeeNumber)->first();
                      $coll = new Collection;

                      if ($this->user->userType_id !== 4 && !($leadershipcheck->isEmpty())) //if not AGENT
                      {
                            $mySubordinates1 =  $myActiveTeam->filter(function ($employee)
                                                {   // Contrctual || Regular or Consultant or Floating or Contractual extended
                                                    return ($employee->status_id == 1 || $employee->status_id == 4 || $employee->status_id == 5 || $employee->status_id == 6 || $employee->status_id == 10 );
                                                });
                           
                            
                            foreach ($mySubordinates1->sortBy('lastname') as $emp) {

                               /* ------------

                                    We need to make sure emp is 6++ months already  */

                                    $hired = Carbon::createFromFormat('Y-m-d H:i:s', $emp->dateHired);

                                    $serviceLength = $hired->diffInMonths($endPeriod);

                                    if ($serviceLength >= 6) $mySubordinates2->push($emp);

                                    /* --------------- */
                            }



                            foreach ($mySubordinates2->sortBy('lastname') as $emp) {


                                    /* ------------

                                    We need to check if this subordinate has just been moved to you

                                    ---------------*/

                                    //$checkMovement = User::find($emp->id)->movements;
                                    //$checkMovement = Movement::where('user_id',$emp->id)->where('personnelChange_id','1')->where('isDone',true)->where('effectivity','>=',$currentPeriod->format('Y-m-d H:i:s'))->where('effectivity','<=',$endPeriod->format('Y-m-d H:i:s'))->first();
                                    $checkMovement = Movement::where('user_id',$emp->id)->where('personnelChange_id','1')->where('isDone','1')->where('effectivity','>=',$currentPeriod->toDateString())->first(); //where('effectivity','<=',$endPeriod->toDateString())->first(); //
                                   //$coll->push(['emp'=>$emp->lastname, 'check'=>$checkMovement, 'currentPeriod'=>$currentPeriod->toDateString(), 'endPeriod'=>$endPeriod->toDateString()]);

                                    
                                    if (!empty($checkMovement)){
                                       //$existing = EvalForm::where('user_id', $emp->id)->where('startPeriod',$currentPeriod)->get();
                                        $effective = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');

                                        // **** GET THE EVAL RANGES, pag fromPeriod eh wayy past currentPeriod -- get current
                                        if($checkMovement->fromPeriod <= $currentPeriod){
                                            $fr = $currentPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                        } else $fr = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila'); 

                                        if($checkMovement->effectivity <= $endPeriod){
                                            $to = $endPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                        } else $to = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');    

                                         /* ------------------ 

                                            check if hindi sakop ng eval period yung pagkakamove ni employee sa yo
                                            
                                            ------------------ */

                                            if ($checkMovement->effectivity > $endPeriod){ //if effectivity ng movement eh hindi sakop

                                              $doNotInclude = true;

                                            } else { $doNotInclude=false; }

                                           $coll->push(['doNotInclude'=> $doNotInclude]);
 

                                    } else { 
                                      $fr = $currentPeriod->startOfDay(); $to = $endPeriod->startOfDay(); $doNotInclude=false; //$coll->push(['effectivity'=> null, 'endPeriod'=>$endPeriod]);

                                    }
                                    //$coll->push(['from'=>$fr, 'to'=>$to]);

                                    $evalBy = User::find($emp->id)->supervisor->immediateHead_Campaigns_id;
                                    $existing = EvalForm::where('user_id', $emp->id)->where('evaluatedBy', $evalBy)->where('endPeriod','<=',$to)->where('startPeriod','>=', $fr)->orderBy('id','DESC')->get();
                                    


                                    if (count($existing) == 0 ){

                                       if ($doNotInclude) { /* do nothing */}
                                        else { 
                                          $doneEval[$emp->id] = ['evaluated'=>0,'isDraft'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$fr->format('M d, Y'), 'endPeriod'=>$to->format('M d, Y')];
                                          $mySubordinates->push($emp);
                                        } 

                                    } 
                                    else {
                                        //$truegrade = 100-((100-(EvalForm::find( $existing->first()->id)->overAllScore))*0.5);
                                        $theeval = EvalForm::find( $existing->sortByDesc('id')->first()->id);
                                        $truegrade = $theeval->overAllScore;

                                        if ($theeval->isDraft == 1) 
                                          $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->startOfDay()->format('M d, Y'), 'endPeriod'=>$endPeriod->startOfDay()->format('M d, Y')];
                                        else
                                        //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                        $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($existing->first()->startPeriod)), 'endPeriod'=>$endPeriod->format('M d, Y')];

                                        $mySubordinates->push($emp);
                                    } 



                            }//end foreach
                          

                           /* ---------------------------------------------------------------- 

                                GET PAST MEMBERS moved to you

                            /* ---------------------------------------------------------------- */

                            
                          
                          
                              /*** OLD --- foreach ($me->myCampaigns as $m) { */
                                $changedImmediateHeads = new Collection;
                                $doneMovedEvals = new Collection;
                                $ctr = 0;

                                foreach($mc as $m){ 

                                

                                $moved = Movement_ImmediateHead::where('imHeadCampID_old',$m->id)->get();
                                //$coll->push($moved);
                                //$moved = Movement_ImmediateHead::where('imHeadCampID_old',$ihCamp->id)->get();
                               

                                $changedHeads = new Collection;
                                $chIH = new Collection;

                                foreach ($moved as $m) {
                                    $changedHeads->push($m->info);
                                }


                               
                                foreach ($changedHeads as $mvt) {
                                  //$evalTypes = EvalType::all();
                                  //$evalSetting = EvalSetting::find(2);
                                  $currentPeriod = Carbon::create((date("Y")), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
                                  $endPeriod = Carbon::create((date("Y")), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');
                                  $effective = Carbon::createFromFormat('Y-m-d H:i:s', $mvt->first()->effectivity, 'Asia/Manila');


                                  if  ($effective <= $endPeriod && $effective >= $currentPeriod && $mvt->isDone) 
                                    //$chIH->push($changedHeads->first());
                                    $chIH->push($mvt);
                                                                        
                                }


                                foreach ($chIH as $emp) {
                                    $employ = User::find($emp->user_id);
                                    $hisTeam = $employ->team;
                                    $hisTL = ImmediateHead::find(Team::find($hisTeam->id)->leader->immediateHead_id);
                                    

                                    /* -------- we need to check first if YOU are the CURRENT TL. if yes,no need to be added to changedImmediateHeads  ----- */

                                    // if ($employ->supervisor->first()->immediateHead_id !== $me->id)
                                    // {
                                            $changedImmediateHeads->push([
                                                                    'movement_id'=> $emp->id,
                                                                    'id'=>$employ->id, 
                                                                    'index'=> $ctr,
                                                                    'user_id'=>$employ->id, 
                                                                    'userType_id'=>$employ->userType_id, 
                                                                    'dateHired'=>$employ->dateHired, 
                                                                    'firstname'=> $employ->firstname, 
                                                                    'lastname'=>$employ->lastname, 
                                                                    'position'=> $employ->position->name, 
                                                                    'status'=>$employ->status->name]);

                                          
                                            
                                            
                                            $effective = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila');

                                            // **** GET THE EVAL RANGES, pag fromPeriod eh wayy past currentPeriod -- get current
                                            if($emp->fromPeriod < $currentPeriod->startOfDay()){
                                                $fr = $currentPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                            } else $fr = Carbon::createFromFormat('Y-m-d H:i:s', $emp->fromPeriod, 'Asia/Manila'); 
                                            $to = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila'); //


                                            /* ## OLD: $evaluated = EvalForm::where('user_id', $emp->user_id)->where('evaluatedBy', $me->id)->where('startPeriod',$fr)->where('endPeriod',$to)->orderBy('id','DESC')->get(); //where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('id','DESC')->get();
                                            
                                            */
                                            $evalBy = $me->id;  
                                            //$coll->push(['from: '=>$fr, 'to: '=>$to->startOfDay()]);

                                            $evaluated = EvalForm::where('user_id', $emp->user_id)->where('evaluatedBy', $evalBy)->where('startPeriod','>=',$fr)->get(); //->where('endPeriod','<=', $to->startOfDay())->get(); //->get(); //where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('id','DESC')->get();
                                            
                                        
                                         

                                            if ( count($evaluated) == 0)
                                            {
                                                $doneMovedEvals[$ctr] = ['user_id'=>$emp->user_id,'evaluated'=>0,'isDraft'=>0, 'coachingDone'=>false, 'evalForm_id'=> null, 'score'=>null,'startPeriod'=>$fr->format('M d, Y'), 'endPeriod'=>$to->format('M d, Y')];

                                                
                                            } else {

                                                $theeval = EvalForm::find( $evaluated->sortByDesc('id')->first()->id);
                                                $truegrade = $theeval->overAllScore;

                                                if ($theeval->isDraft) 
                                                  $doneMovedEvals[$ctr] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $evaluated->first()->id, 'score'=>$truegrade, 'startPeriod'=>$theeval->startPeriod, 'endPeriod'=>$theeval->endPeriod];
                                                else
                                                //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                                $doneMovedEvals[$ctr] = ['evaluated'=>1, 'isDraft'=>0, 'evalForm_id'=> $theeval->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($theeval->startPeriod)), 'endPeriod'=>date('M d,Y',strtotime($theeval->endPeriod))];



                                                
                                            }




                                    // }// end if you're not the current immediateHead

                                    
                                      $ctr++;
                                   
                                }//end foreach chIH


                            


                              }//end foreach campaign
                             


                            
                         
                            return view('showThoseUpFor', compact('mySubordinates', 'evalTypes', 'evalSetting', 'doneEval','doneMovedEvals','changedImmediateHeads','currentPeriod','endPeriod'));

                      } else {

                            $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod->startOfDay())->get();


                            if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                            else {
                                //$truegrade = 100-((100-(EvalForm::find( $existing->first()->id)->overAllScore))*0.5);

                                        $theeval = EvalForm::find( $existing->sortByDesc('id')->first()->id);
                                        $truegrade = $theeval->overAllScore;

                                        if ($theeval->isDraft) 
                                          $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->startOfDay()->format('M d, Y'), 'endPeriod'=>$endPeriod->startOfDay()->format('M d, Y')];
                                        else
                                        //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                        $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($existing->first()->startPeriod)), 'endPeriod'=>$endPeriod->format('M d, Y')];



                                //$truegrade = EvalForm::find( $existing->first()->id)->overAllScore;
                                //if ($truegrade == 0) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                //else
                                //$doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                            }
                            return view('agentView-showThoseUpFor', compact('employee', 'myCampaign', 'evalTypes', 'evalSetting', 'doneEval')); 


                      }//end else not an agent


                        } break;

                case 3: { //Regularization
                          $changedImmediateHeads=null; $doneMovedEvals=null;
                          if ($this->user->userType_id !== 4 && !($leadershipcheck->isEmpty())) //if not AGENT
                         {
                            $mySubordinates = $myActiveTeam->filter(
                              function ($employee) {
                              return ($employee->status_id == 1 || $employee->status_id == 2 || $employee->status_id == 3 || $employee->status_id == 5 || $employee->status_id == 6); 
                            }); //filter out regular employees

                             $coll= new Collection;
                             
                             foreach ($mySubordinates as $emp) {
                               /* ------------

                                    We need to check if this subordinate has just been moved to you

                                    ---------------*/

                                    $currentPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$emp->dateHired,'Asia/Manila');
                                    $cPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$emp->dateHired,'Asia/Manila');
                                    $endPeriod = $cPeriod->addMonths(6);

                                    //$checkMovement = User::find($emp->id)->movements;
                                    $checkMovement = Movement::where('user_id',$emp->id)->where('personnelChange_id','1')->where('isDone',true)->where('effectivity','>=',$currentPeriod->startOfDay())->where('effectivity','<=',$endPeriod->startOfDay())->first();
                                   
                                    //$coll->push($checkMovement);

                                    if (!empty($checkMovement)){
                                       //$existing = EvalForm::where('user_id', $emp->id)->where('startPeriod',$currentPeriod)->get();
                                        $effective = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');

                                        //pag yung movement from eh dateHired, meaning 1st time nya lang na-move..kunin mo yung effectivity start
                                        if ($checkMovement->fromPeriod == $emp->dateHired){
                                          $fr = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila'); 

                                        } 

                                        // **** GET THE EVAL RANGES, pag fromPeriod eh wayy past currentPeriod -- get current
                                        else if($checkMovement->fromPeriod < $currentPeriod){
                                            $fr = $currentPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                        } else $fr = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->fromPeriod, 'Asia/Manila'); 

                                        if($checkMovement->effectivity < $endPeriod){
                                            $to = $endPeriod->startOfDay(); // Carbon::createFromFormat('Y-m-d H:i:s', $tillWhen->first()->fromPeriod, 'Asia/Manila'); 

                                        } else $to = Carbon::createFromFormat('Y-m-d H:i:s', $checkMovement->effectivity, 'Asia/Manila');     

                                    
                                  } else { 
                                      $fr = $currentPeriod->startOfDay(); $to = $endPeriod->startOfDay();

                                    }

                                    $evalBy = User::find($emp->id)->supervisor->immediateHead_Campaigns_id;
                                    $existing = EvalForm::where('user_id', $emp->id)->where('evaluatedBy', $evalBy)->where('endPeriod','<=',$to)->where('startPeriod','>=', $fr)->orderBy('id','DESC')->get();
                            
                                    //$existing = EvalForm::where('user_id', $emp->id)->where('evaluatedBy', $me->id)->where('endPeriod','<=',$to)->where('startPeriod','>=',$fr)->orderBy('id','DESC')->get();
                                    //$coll->push(['user_id'=>$emp->id, 'endPeriod'=>$to, 'startPeriod'=>$fr, 'existing'=> $existing, 'me'=>$me]);

                                   
                                                                        
                                    


                                    /*if (count($existing) == 0) $doneEval[$emp->id] = ['evaluated'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')]; */

                                    if (count($existing) == 0) $doneEval[$emp->id] = ['evaluated'=>0, 'isDraft'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$fr->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                    
                                    else {
                                        //$truegrade = 100-((100-(EvalForm::find( $existing->first()->id)->overAllScore))*0.5);

                                       $theeval = EvalForm::find( $existing->sortByDesc('id')->first()->id);
                                        $truegrade = $theeval->overAllScore;


                                        if ($theeval->isDraft) 
                                          $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $existing->sortByDesc('id')->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->startOfDay()->format('M d, Y'), 'endPeriod'=>$endPeriod->startOfDay()->format('M d, Y')];
                                        else
                                        //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                        $doneEval[$emp->id] = ['evaluated'=>1, 'isDraft'=>0, 'evalForm_id'=> $existing->sortByDesc('id')->first()->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($existing->sortByDesc('id')->first()->startPeriod)), 'endPeriod'=>$endPeriod->format('M d, Y')];



                                        //$truegrade = EvalForm::find( $existing->first()->id)->overAllScore;
                                        //if ($truegrade == 0) $doneEval[$emp->id] = ['evaluated'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                        //else
                                        //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                    } 



                            }//end foreach
                          
                            //return $coll;
                            // return $doneEval;

                            /* ---------------------------------------------------------------- 

                                GET PAST MEMBERS moved to you

                            /* ---------------------------------------------------------------- */

                          

                            $changedImmediateHeads = new Collection;
                            $doneMovedEvals = new Collection;
                            $ctr = 0;

                            foreach($mc as $m)
                            { 

                              $moved = Movement_ImmediateHead::where('imHeadCampID_old',$m->id)->get();
                              $changedHeads = new Collection;

                                foreach ($moved as $m2) {
                                    $changedHeads->push($m2->info);
                                }

                                
                               
                               foreach ($changedHeads as $emp) 
                               {
                                    $employ = User::find($emp->user_id);
                                    $hisTeam = $employ->team;
                                    $hisTL = ImmediateHead::find(Team::find($hisTeam->id)->leader->immediateHead_id);
                                    

                                    /* -------- we need to check first if he's fit for REGULARIZATION  ----- */

                                    if ($employ->status_id == 1 || $employ->status_id == 2 || $employ->status_id == 3|| $employ->status_id == 5 || $employ->status_id == 6)
                                    {
                                                  $changedImmediateHeads->push([
                                                                          'movement_id'=> $emp->id,
                                                                          'id'=>$employ->id, 
                                                                          'index'=> $ctr,
                                                                          'user_id'=>$employ->id, 
                                                                          'userType_id'=>$employ->userType_id, 
                                                                          'dateHired'=>$employ->dateHired, 
                                                                          'firstname'=> $employ->firstname, 
                                                                          'lastname'=>$employ->lastname, 
                                                                          'position'=> $employ->position->name, 
                                                                          'status'=>$employ->status->name]);

                                                
                                                  
                                                  
                                                  $effective = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila');

                                                  // **** GET THE EVAL RANGES, pag fromPeriod eh wayy past currentPeriod -- get current
                                                  $fr = Carbon::createFromFormat('Y-m-d H:i:s', $emp->fromPeriod, 'Asia/Manila'); 
                                                  $to = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila'); //



                                                  //$evaluated = EvalForm::where('user_id', $emp->user_id)->where('evaluatedBy', $me->id)->where('startPeriod',$fr->startOfDay())->where('endPeriod',$to->startOfDay())->get(); //where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('id','DESC')->get();
                                                  $$evalBy = $me->id;
                                                  $evaluated = EvalForm::where('user_id', $emp->user_id)->where('evaluatedBy', $evalBy)->where('startPeriod',$fr)->where('endPeriod',$to)->orderBy('id','DESC')->get(); //where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('id','DESC')->get();
                                                                                           
                                               

                                                  if ( count($evaluated) == 0)
                                                  {
                                                      $doneMovedEvals[$ctr] = ['user_id'=>$emp->user_id, 'isDraft'=>false,'coachingDone'=>false, 'evaluated'=>0,'coachingDone'=>false, 'evalForm_id'=> null, 'score'=>null,'startPeriod'=>$fr->format('M d, Y'), 'endPeriod'=>$to->format('M d, Y')];

                                                      
                                                  } else {

                                                      $theeval = EvalForm::find( $evaluated->sortByDesc('id')->first()->id);
                                                      $truegrade = $theeval->overAllScore;

                                                      if ($theeval->isDraft) 
                                                        $doneMovedEvals[$ctr] = ['evaluated'=>1, 'isDraft'=>1, 'evalForm_id'=> $evaluated->first()->id, 'score'=>$truegrade, 'startPeriod'=>$theeval->startPeriod, 'endPeriod'=>$theeval->endPeriod];
                                                      else
                                                      //$doneEval[$emp->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                                      $doneMovedEvals[$ctr] = ['evaluated'=>1, 'coachingDone'=>false, 'isDraft'=>0, 'evalForm_id'=> $theeval->id, 'score'=>$truegrade, 'startPeriod'=>date('M d, Y', strtotime($theeval->startPeriod)), 'endPeriod'=>date('M d,Y',strtotime($theeval->endPeriod))];


                                                      
                                                  }


                                          
                                            $ctr++;

                                    }
                                }

                              
                            }//end foreach campaign

                           
                            return view('showThoseUpFor', compact('mySubordinates', 'myCampaign', 'evalTypes', 'evalSetting', 'doneEval','doneMovedEvals','changedImmediateHeads','currentPeriod','endPeriod'));


                         } else {
                            $currentPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$employee->dateHired,'Asia/Manila');
                            $existing = EvalForm::where('user_id', $employee->id)->where('evalSetting_id',$evalSetting->id)->where('startPeriod',$currentPeriod->format('Y-m-d H:i:s'))->orderBy('id','DESC')->get();
                            if ($existing->isEmpty()) {
                                    
                                    $toPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$employee->dateHired,'Asia/Manila');
                                    $tPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$employee->dateHired,'Asia/Manila');
                                    $endPeriod = $tPeriod->addMonths(6);
                                    $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null, 'score'=>null, 'startPeriod'=>$toPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                } 
                                else {
                                    //$truegrade = 100-((100-(EvalForm::find( $existing->first()->id)->overAllScore))*0.5);
                                    $truegrade = EvalForm::find( $existing->first()->id)->overAllScore;

                                    if ($truegrade == 0){
                                        $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$existing->first()->endPeriod,'Asia/Manila');
                                        $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];
                                    }  
                                    else {
                                        $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$existing->first()->endPeriod,'Asia/Manila');
                                        $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id, 'score'=>$truegrade, 'startPeriod'=>$currentPeriod->format('M d, Y'), 'endPeriod'=>$endPeriod->format('M d, Y')];

                                    }
                                        
                                } 


                                return view('agentView-showThoseUpFor', compact('employee', 'myCampaign', 'evalTypes', 'evalSetting', 'doneEval'));
                         }// end else an agent

                             

                        } break;

                case 4: { //appraisal

                    //find first if there's already a previous appraisal, if not then begin from date of regularization
                    // if date of regularization==null, then from date hired

                    $previousAppraisal = EvalForm::where('user_id',$employee->id)->where('evalSetting_id', $evalSetting_id)->orderBy('id','DESC')->get();

                    if ( $previousAppraisal->isEmpty() ){

                        //check if employee has date of regularization

                        if( empty($employee->dateRegularized) ){
                             
                             $currentPeriod = $employee->dateHired;
                             $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                             $currentPeriod->setTime(0,0,0);
                             
                             $endPeriod = new \DateTime(date()); 
                             $endPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                             $endPeriod->setTime(0,0,0);


                        } else {

                            $currentPeriod = $employee->dateRegularized;
                            $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                             $currentPeriod->setTime(0,0,0);

                             $endPeriod = new \DateTime(date()); 
                             $endPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                             $endPeriod->setTime(0,0,0);


                        }
                        

                    } else {

                            $currentPeriod = $previousAppraisal->first()->startPeriod;
                             $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                             $currentPeriod->setTime(0,0,0);
                             
                             $endPeriod = new \DateTime(date()); 
                             $endPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                             $endPeriod->setTime(0,0,0);

                    }

                     
                        } break;
            
           
            }
            
            
            return view('showThoseUpFor', compact('mySubordinates', 'evalTypes', 'evalSetting', 'doneEval','changedImmediateHeads','doneMovedEvals', 'currentPeriod','endPeriod'));
          
    }

    public function downloadReport()
    {

      Excel::create('Evaluation Summary', function($excel) {

        

          // Set the title
          $excel->setTitle('Evaluation Summary Report');

          // Chain the setters
          $excel->setCreator('Mike Pamero')
                ->setCompany('OAMPI');

          // Call them separately
          $excel->setDescription('Contains summary of Semi-annual and Regularization Evaluations');

          $excel->sheet('2017 Jan-Jun Semi-annual', function($sheet) {

            $evalTypes = EvalType::all();
            $evalSetting = EvalSetting::find(1);
            $currentPeriod = Carbon::create((date("Y")), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
            $endPeriod = Carbon::create((date("Y")), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');

            $evals = EvalForm::where('evalSetting_id','1')->where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('user_id','ASC')->get(); //get only jul-dec semi annual

            $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Overall Score','Salary Increase', 'Evaluated By', 'Date Evaluated'));

            

            foreach($evals as $eval){

              if ( !$eval->details->isEmpty() )
              {
                $cmp = User::find($eval->owner->id)->campaign->first();
                if (empty($cmp))
                {
                      if ($eval->isDraft)
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      "none", 
                      "DRAFT", "DRAFT", 
                      //ImmediateHead::find($eval->evaluatedBy)->firstname." ".ImmediateHead::find($eval->evaluatedBy)->lastname, 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));
                    else
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      "none", 
                      $eval->overAllScore, $eval->salaryIncrease."%", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));

                } else 
                {

                      if ($eval->isDraft)
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      $cmp->name, 
                      "DRAFT", "DRAFT", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));
                    else
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      $cmp->name, 
                      $eval->overAllScore, $eval->salaryIncrease."%", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));

                }
               

                

              
              $sheet->appendRow($arr);

              }

              

            }

              

          });

          $excel->sheet('2016 Jul-Dec Semi-annual', function($sheet) {

            $evalTypes = EvalType::all();
            $evalSetting = EvalSetting::find(2);
            $currentPeriod = Carbon::create((date("Y")-1), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
            $endPeriod = Carbon::create((date("Y")-1), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');


            $evals = EvalForm::where('evalSetting_id','2')->where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->orderBy('user_id','ASC')->get(); //get only jul-dec semi annual
            //$evals = EvalForm::where('evalSetting_id','2')->orderBy('user_id','ASC')->get(); //get only jul-dec semi annual

            $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Overall Score','Salary Increase', 'Evaluated By', 'Date Evaluated'));

            

            foreach($evals as $eval){

              if ( !$eval->details->isEmpty() )
              {
                $cmp = User::find($eval->owner->id)->campaign->first();
                if (empty($cmp))
                {
                      if ($eval->isDraft)
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      "none", 
                      "DRAFT", "DRAFT", 
                      //ImmediateHead::find($eval->evaluatedBy)->firstname." ".ImmediateHead::find($eval->evaluatedBy)->lastname, 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));
                    else
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      "none", 
                      $eval->overAllScore, $eval->salaryIncrease."%", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));

                } else 
                {

                      if ($eval->isDraft)
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      $cmp->name, 
                      "DRAFT", "DRAFT", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));
                    else
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      $cmp->name, 
                      $eval->overAllScore, $eval->salaryIncrease."%", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));

                }
               

                

              
              $sheet->appendRow($arr);

              }

              

            }

              

          });

          $excel->sheet('Regularizations', function($sheet) {

            $evalTypes = EvalType::all();
            $evalSetting = EvalSetting::find(3);
            

            $evals = EvalForm::where('evalSetting_id','3')->orderBy('created_at','DESC')->get(); //get only jul-dec semi annual

            $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Overall Score', 'Evaluated By', 'Date Evaluated', 'Date Hired'));

            

            foreach($evals as $eval){

              if ( !$eval->details->isEmpty() )
              {
                $cmp = User::find($eval->owner->id)->campaign->first();
                if (empty($cmp))
                {
                      if ($eval->isDraft)
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      "none", 
                      "DRAFT", 
                      //ImmediateHead::find($eval->evaluatedBy)->firstname." ".ImmediateHead::find($eval->evaluatedBy)->lastname, 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at),
                      Carbon::createFromFormat('Y-m-d H:i:s',User::find($eval->owner->id)->dateHired));
                    else
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      "none", 
                      $eval->overAllScore, 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at),
                      Carbon::createFromFormat('Y-m-d H:i:s',User::find($eval->owner->id)->dateHired));

                } else 
                {

                      if ($eval->isDraft)
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      $cmp->name, 
                      "DRAFT", 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at),
                      Carbon::createFromFormat('Y-m-d H:i:s',User::find($eval->owner->id)->dateHired));

                    else
                      $arr = array($eval->owner->employeeNumber,
                      $eval->owner->lastname,
                      $eval->owner->firstname,
                      $cmp->name, 
                      $eval->overAllScore, 
                      ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->firstname." ".ImmediateHead::find(ImmediateHead_Campaign::find($eval->evaluatedBy)->immediateHead_id)->lastname, 
                      Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at),
                      Carbon::createFromFormat('Y-m-d H:i:s',User::find($eval->owner->id)->dateHired));

                }
               

                

              
              $sheet->appendRow($arr);

              }

              

            }

              

          });



          //Regularization summary
          // $excel->sheet('Regularizations', function($sheet) {

          //   $evals = EvalForm::where('evalSetting_id','3')->orderBy('user_id','ASC')->get(); //get only Regularization forms
          //   $sheet->appendRow(array('Employee Number', 'Lastname', 'Firstname', 'Dept/Program','Overall Score', 'Evaluated By', 'Date Evaluated'));

          //   foreach($evals as $eval){
          //    $arr = array($eval->owner->employeeNumber,$eval->owner->lastname,$eval->owner->firstname,Campaign::find($eval->owner->campaign_id)->name, $eval->overAllScore, ImmediateHead::find($eval->evaluatedBy)->firstname." ".ImmediateHead::find($eval->evaluatedBy)->lastname, Carbon::createFromFormat('Y-m-d H:i:s',$eval->updated_at));
               
          //     $sheet->appendRow($arr);

          //   }

              

          // });


      })->export('xls');

      return "Download";
    }


    public function printBlankEval($id)
    {
      $evalForm = EvalForm::find($id);

      if ( empty($evalForm) ) {
        return view('empty');

      } else
      {

        $employee = User::find($evalForm->user_id);
        $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();
        $allowed = false;

        //if (ImmediateHead::find($employee->immediateHead_Campaigns_id)->employeeNumber == $this->user->employeeNumber ){ $allowed = true; }
        $allowed=true;

        if( $this->user->userType_id == 1 ||  $allowed || $this->user->id == $employee->id )
        {

              $details = $evalForm->details;

              $evalType = EvalType::find($evalForm->setting->evalType_id);
              $evalSetting = EvalSetting::find($evalForm->evalSetting_id);

              
              $ratingScale = RatingScale::all();
              //setup now the evaluation form
              // !! but check first if it's existing already, if not -- create new one

              $currentPeriod = new \DateTime(date("Y")."-".$evalForm->setting->startMonth."-".$evalForm->setting->startDate." 00:00:00"); // " "2010-07-05T06:00:00Z"); date_create(date("Y").",timezone_open("Europe/Oslo"));
              $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
              $currentPeriod->setTime(0,0,0);

              $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod)->get();
              $doneEval = new Collection;
              if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null];
                  else $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id];

              

              $competencyAttributes = $evalForm->setting->competencyAttributes;
              $competencies = $competencyAttributes->groupBy('competency_id');
              $formEntries = new Collection;
              $maxScore = 0;

              $formEntries = new Collection;

              if ($details->isEmpty()){
                  
                  return $this->newEvaluation($employee->id,$evalType->id);

              } else
              {
                  foreach ($details as $detail) {
                      $comp = Competency__Attribute::find($detail->competency__Attribute_id)->competency;
                      $attr = Competency__Attribute::find($detail->competency__Attribute_id)->attribute;

                      $rating = RatingScale::find($detail->ratingScale_id); //->ratings; //->label;'ratings'=> $rating['label']


                       // --------------- generate form elements based on leader/agent competencies

                      if( $comp['acrossTheBoard'] ){

                        if ( $leadershipcheck->isEmpty()  ){ //agent

                       $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);
                         

                        } else {
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        }

                      } else { //else not acrossTheBoard

                        if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                         $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        }

                      }


                      // --------------- end generate form elements





                      
                  }

                  //get all Performance Summary values

                  $allSummaries = Summary::all();
                  $summaries = new Collection;

                  foreach ($allSummaries as $key ) {
                     if (!($key->columns->isEmpty()) ) 
                      {
                          $cols = $key->columns;

                          // foreach ($cols as $c) {
                          //     $colVal =  PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                          //     $colValues[] = 
                          // }
                      } else $cols=null;
                     if (!($key->rows->isEmpty()) )
                     { 
                          $rows = $key->rows;

                     }  else $rows = null;

                     $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                     $summaries->push(['summaryID'=>$key->id,'summaryValue'=>$summaryValue,  'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
                  }

                  $perfSum = PerformanceSummary::where('evalForm_id', $evalForm->id)->get();

                  if ($perfSum->isEmpty()){
                      $performanceSummary = null;

                  } else {
                      $performanceSummary = new Collection;
                      $idx = 0;
                      foreach ($perfSum as $ps) {
                          $performanceSummary[$idx] = $ps->value;
                          $idx++;
                      }

                  }
                  

                   $startPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->startPeriod);
                   $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->endPeriod);
                   //$evaluator = ImmediateHead::find($evalForm->evaluatedBy);
                   $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($evalForm->evaluatedBy)->immediateHead_id);


                   
                    /* -------  for maxscore -----*/

                   foreach ($competencies as $key ) {
                       
                        $comp = Competency::find($key[0]->competency_id);


                        if( $comp['acrossTheBoard'] )
                        {

                              if ( $leadershipcheck->isEmpty()  ){ //agent

                                 $maxScore += $comp->agentPercentage*5/100;
                               

                              } else {
                                $maxScore += $comp->percentage*5/100;

                              }

                       } else 
                            { //else not acrossTheBoard

                                if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                                    $maxScore += $comp->agentPercentage*5/100;

                                } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                                  $maxScore += $comp->percentage*5/100;

                                }

                        }

                      
                        
                       
                    }


                   

                 /* DMPDF */
                    //$pdf = PDF::loadView('evaluation.pdf', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                    $pdf = PDF::loadView('evaluation.pdf-blank', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                    

                   return $pdf->stream('eval_'.$employee->lastname."_".$employee->firstname.'.pdf');
                   
                   // $tempdir = sys_get_temp_dir();
                   //  $pdf = App::make('dompdf.wrapper');
                   //  //$pdf->setOptions(['defaultFont' => 'sans-serif', 'defaultPaperSize': "a4" ]);
                   //  $pdf->loadHTML('');
                   //  return $pdf->stream();

                    //                  $pdf = App::make('dompdf.wrapper');
                    // $pdf->loadHTML('<h1>Test</h1>');
                    // return $pdf->stream();

                                        /* ------- END DMPDF ---------*/


                                        /*-------- SNAPPY -----------*/
                    //                    $pdf = App::make('snappy.pdf.wrapper');
                    // $pdf->loadHTML('<h1>Test</h1>');
                    // return $pdf->inline();
                    /*-------- END SNAPPY ----------*/



                  //return view('evaluation.print', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
              
              } //end else not empty
         } else { return view('access-denied'); }//end if allowed

       }//end else not empty form
      


    }

     public function printBlankEmployee($id)
    {
      //$evalForm = EvalForm::find($id);

      $employee = User::find($id);
      $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();

      // check if leader or agent
      if ( count($leadershipcheck) != 0 ) //use leadership form
      {
        $evalForm = EvalForm::find(196);

      } else {
        $evalForm = EvalForm::find(71);

      }

      if ( empty($evalForm) ) {
        return view('empty');

      } else
      {

        //$employee = User::find($evalForm->user_id);

        
        $allowed = false;

        //if (ImmediateHead::find($employee->immediateHead_Campaigns_id)->employeeNumber == $this->user->employeeNumber ){ $allowed = true; }
        $allowed=true;

        if( $this->user->userType_id == 1 ||  $allowed || $this->user->id == $employee->id )
        {

              $details = $evalForm->details;

              $evalType = EvalType::find($evalForm->setting->evalType_id);
              $evalSetting = EvalSetting::find($evalForm->evalSetting_id);

              
              $ratingScale = RatingScale::all();
              //setup now the evaluation form
              // !! but check first if it's existing already, if not -- create new one

              $currentPeriod = new \DateTime(date("Y")."-".$evalForm->setting->startMonth."-".$evalForm->setting->startDate." 00:00:00"); // " "2010-07-05T06:00:00Z"); date_create(date("Y").",timezone_open("Europe/Oslo"));
              $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
              $currentPeriod->setTime(0,0,0);

              $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod)->get();
              $doneEval = new Collection;
              if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null];
                  else $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id];

              

              $competencyAttributes = $evalForm->setting->competencyAttributes;
              $competencies = $competencyAttributes->groupBy('competency_id');
              $formEntries = new Collection;
              $maxScore = 0;

              $formEntries = new Collection;

              if ($details->isEmpty()){
                  
                  return $this->newEvaluation($employee->id,$evalType->id);

              } else
              {
                  foreach ($details as $detail) {
                      $comp = Competency__Attribute::find($detail->competency__Attribute_id)->competency;
                      $attr = Competency__Attribute::find($detail->competency__Attribute_id)->attribute;

                      $rating = RatingScale::find($detail->ratingScale_id); //->ratings; //->label;'ratings'=> $rating['label']


                       // --------------- generate form elements based on leader/agent competencies

                      if( $comp['acrossTheBoard'] ){

                        if ( $leadershipcheck->isEmpty()  ){ //agent

                       $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);
                         

                        } else {
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        }

                      } else { //else not acrossTheBoard

                        if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                         $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        }

                      }


                      // --------------- end generate form elements





                      
                  }

                  //get all Performance Summary values

                  $allSummaries = Summary::all();
                  $summaries = new Collection;

                  foreach ($allSummaries as $key ) {
                     if (!($key->columns->isEmpty()) ) 
                      {
                          $cols = $key->columns;

                          // foreach ($cols as $c) {
                          //     $colVal =  PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                          //     $colValues[] = 
                          // }
                      } else $cols=null;
                     if (!($key->rows->isEmpty()) )
                     { 
                          $rows = $key->rows;

                     }  else $rows = null;

                     $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                     $summaries->push(['summaryID'=>$key->id,'summaryValue'=>$summaryValue,  'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
                  }

                  $perfSum = PerformanceSummary::where('evalForm_id', $evalForm->id)->get();

                  if ($perfSum->isEmpty()){
                      $performanceSummary = null;

                  } else {
                      $performanceSummary = new Collection;
                      $idx = 0;
                      foreach ($perfSum as $ps) {
                          $performanceSummary[$idx] = $ps->value;
                          $idx++;
                      }

                  }
                  

                   $startPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->startPeriod);
                   $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->endPeriod);
                   $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($employee->supervisor->immediateHead_Campaigns_id)->immediateHead_id); //ImmediateHead::find($evalForm->evaluatedBy);
                   


                   
                    /* -------  for maxscore -----*/

                   foreach ($competencies as $key ) {
                       
                        $comp = Competency::find($key[0]->competency_id);


                        if( $comp['acrossTheBoard'] )
                        {

                              if ( $leadershipcheck->isEmpty()  ){ //agent

                                 $maxScore += $comp->agentPercentage*5/100;
                               

                              } else {
                                $maxScore += $comp->percentage*5/100;

                              }

                       } else 
                            { //else not acrossTheBoard

                                if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                                    $maxScore += $comp->agentPercentage*5/100;

                                } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                                  $maxScore += $comp->percentage*5/100;

                                }

                        }

                      
                        
                       
                    }


                   

                 /* DMPDF */
                    //$pdf = PDF::loadView('evaluation.pdf', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                    $pdf = PDF::loadView('evaluation.pdf-blankEmployee', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                    

                   return $pdf->stream('eval_'.$employee->lastname."_".$employee->firstname.'.pdf');
                   
                   // $tempdir = sys_get_temp_dir();
                   //  $pdf = App::make('dompdf.wrapper');
                   //  //$pdf->setOptions(['defaultFont' => 'sans-serif', 'defaultPaperSize': "a4" ]);
                   //  $pdf->loadHTML('');
                   //  return $pdf->stream();

                    //                  $pdf = App::make('dompdf.wrapper');
                    // $pdf->loadHTML('<h1>Test</h1>');
                    // return $pdf->stream();

                                        /* ------- END DMPDF ---------*/


                                        /*-------- SNAPPY -----------*/
                    //                    $pdf = App::make('snappy.pdf.wrapper');
                    // $pdf->loadHTML('<h1>Test</h1>');
                    // return $pdf->inline();
                    /*-------- END SNAPPY ----------*/



                  //return view('evaluation.print', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
              
              } //end else not empty
         } else { return view('access-denied'); }//end if allowed

       }//end else not empty form
      


    }

    public function printEval($id)
    {
      $evalForm = EvalForm::find($id);

      if ( empty($evalForm) ) {
        return view('empty');

      } else
      {

        $employee = User::find($evalForm->user_id);
        $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();
        $allowed = false;

       

        //if (ImmediateHead::find(ImmediateHead_Campaign::find($employee->immediateHead_Campaigns_id)->immediateHead_id)->employeeNumber == $this->user->employeeNumber ){ $allowed = true; }
        $canDoThis = UserType::find($this->user->userType_id)->roles->where('label','VIEW_ALL_EVALS');

        if (!empty($canDoThis)) $allowed=true;

        if( $this->user->userType_id == 1 || $allowed  || $this->user->id == $employee->id )
        {

              $details = $evalForm->details;

              $evalType = EvalType::find($evalForm->setting->evalType_id);
              $evalSetting = EvalSetting::find($evalForm->evalSetting_id);

              
              $ratingScale = RatingScale::all();
              //setup now the evaluation form
              // !! but check first if it's existing already, if not -- create new one

              $currentPeriod = new \DateTime(date("Y")."-".$evalForm->setting->startMonth."-".$evalForm->setting->startDate." 00:00:00"); // " "2010-07-05T06:00:00Z"); date_create(date("Y").",timezone_open("Europe/Oslo"));
              $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
              $currentPeriod->setTime(0,0,0);

              $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod)->get();
              $doneEval = new Collection;
              if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null];
                  else $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id];

              

              $competencyAttributes = $evalForm->setting->competencyAttributes;
              $competencies = $competencyAttributes->groupBy('competency_id');
              $formEntries = new Collection;
              $maxScore = 0;

              $formEntries = new Collection;

              if ($details->isEmpty()){
                  
                  return $this->newEvaluation($employee->id,$evalType->id);

              } else
              {
                  foreach ($details as $detail) {
                      $comp = Competency__Attribute::find($detail->competency__Attribute_id)->competency;
                      $attr = Competency__Attribute::find($detail->competency__Attribute_id)->attribute;

                      $rating = RatingScale::find($detail->ratingScale_id); //->ratings; //->label;'ratings'=> $rating['label']


                       // --------------- generate form elements based on leader/agent competencies

                      if( $comp['acrossTheBoard'] ){

                        if ( $leadershipcheck->isEmpty()  ){ //agent

                       $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);
                         

                        } else {
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        }

                      } else { //else not acrossTheBoard

                        if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                         $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        }

                      }


                      // --------------- end generate form elements





                      
                  }

                  //get all Performance Summary values

                  $allSummaries = Summary::all();
                  $summaries = new Collection;

                  foreach ($allSummaries as $key ) {
                     if (!($key->columns->isEmpty()) ) 
                      {
                          $cols = $key->columns;

                          // foreach ($cols as $c) {
                          //     $colVal =  PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                          //     $colValues[] = 
                          // }
                      } else $cols=null;
                     if (!($key->rows->isEmpty()) )
                     { 
                          $rows = $key->rows;

                     }  else $rows = null;

                     $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                     $summaries->push(['summaryID'=>$key->id,'summaryValue'=>$summaryValue,  'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
                  }

                  $perfSum = PerformanceSummary::where('evalForm_id', $evalForm->id)->get();

                  if ($perfSum->isEmpty()){
                      $performanceSummary = null;

                  } else {
                      $performanceSummary = new Collection;
                      $idx = 0;
                      foreach ($perfSum as $ps) {
                          $performanceSummary[$idx] = $ps->value;
                          $idx++;
                      }

                  }
                  

                   $startPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->startPeriod);
                   $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->endPeriod);
                   //$evaluator = ImmediateHead::find($evalForm->evaluatedBy);
                   $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($evalForm->evaluatedBy)->immediateHead_id);
                   $evaluatorData = User::where('employeeNumber', $evaluator->employeeNumber)->first();


                   
/* -------  for maxscore -----*/

                   foreach ($competencies as $key ) {
                       
                        $comp = Competency::find($key[0]->competency_id);


                        if( $comp['acrossTheBoard'] )
                        {

                              if ( $leadershipcheck->isEmpty()  ){ //agent

                                 $maxScore += $comp->agentPercentage*5/100;
                               

                              } else {
                                $maxScore += $comp->percentage*5/100;

                              }

                       } else 
                            { //else not acrossTheBoard

                                if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                                    $maxScore += $comp->agentPercentage*5/100;

                                } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                                  $maxScore += $comp->percentage*5/100;

                                }

                        }

                      
                        
                       
                    }


                   

                 /* DMPDF */
                    //$pdf = PDF::loadView('evaluation.pdf', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                    $pdf = PDF::loadView('evaluation.pdf', compact('allowed', 'doneEval', 'evaluator', 'evaluatorData', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                    

                   return $pdf->stream('eval_'.$employee->lastname."_".$employee->firstname.'.pdf');
                   
                   // $tempdir = sys_get_temp_dir();
                   //  $pdf = App::make('dompdf.wrapper');
                   //  //$pdf->setOptions(['defaultFont' => 'sans-serif', 'defaultPaperSize': "a4" ]);
                   //  $pdf->loadHTML('');
                   //  return $pdf->stream();

                  //                  $pdf = App::make('dompdf.wrapper');
                  // $pdf->loadHTML('<h1>Test</h1>');
                  // return $pdf->stream();

                                      /* ------- END DMPDF ---------*/


                                      /*-------- SNAPPY -----------*/
                  //                    $pdf = App::make('snappy.pdf.wrapper');
                  // $pdf->loadHTML('<h1>Test</h1>');
                  // return $pdf->inline();
                    /*-------- END SNAPPY ----------*/



                  //return view('evaluation.print', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
              
              } //end else not empty
         } else {
            return view('access-denied');

         }//end if allowed

       }//end else not empty form
      


    }

    



    public function newEvaluation($user_id, $evalType_id)
    {
        $evalType = EvalType::find($evalType_id);
        $employee = User::find($user_id);

        $meLeader = $employee->supervisor->first();
        $ratingScale = RatingScale::all();
        $allSummaries = Summary::all();
        $summaries = new Collection;

        $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();

        foreach ($allSummaries as $key ) {
           if (!($key->columns->isEmpty()) ) 
            {
                $cols = $key->columns;
            } else $cols=null;
           if (!($key->rows->isEmpty()) )
           { 
                $rows = $key->rows;

           }  else $rows = null;
           $summaries->push(['header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
        }



        $evalSetting = EvalSetting::where('evalType_id',$evalType_id)->first();

        //$cp = Input::get('currentPeriod');
        //$ep = Input::get('endPeriod');
        $currentPeriod = new Carbon(Input::get('currentPeriod'));
        $endPeriod = new Carbon(Input::get('endPeriod'));

        $competencyAttributes = $evalSetting->competencyAttributes;
        $competencies = $competencyAttributes->groupBy('competency_id');
        $formEntries = new Collection;
        $maxScore = 0;

        foreach ($competencies as $key ) {
            $attributes = new Collection;

            foreach ($key as $k) {
                $attributes->push(Attribute::find($k->attribute_id)->name);
            }

            $comp = Competency::find($key[0]->competency_id);
            
            if ( $comp->acrossTheBoard == '0') //check if this competency is for all
            {

                  if (  empty($comp->percentage)   )
                  { //para sa agent sya
                     // ************************ !!! always check first kung may value ung agentPercentage, if not empty 'percentage'=> $comp->agentPercentage
                          // verify mo muna kung agent o leader
                      // $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();

                       if ($leadershipcheck->isEmpty()){ //agent sya

                            $formEntries->push([
                          'competency'=>$comp->name, 
                          'definitions'=> $comp->definitions, 
                          'percentage'=>$comp->agentPercentage,
                          'score'=>$comp->agentPercentage*5/100,
                          'id'=>$comp->id,
                          'attributes'=> $attributes]);
                           $maxScore += $comp->agentPercentage*5/100;


                       } else { } //deadma kasi leader sya
                          

                        

                  } else 
                  { 
                    // ---------- para sa leader sya
                    //verify first if employee is really a leader
                        
                          if  ($employee->userType_id !== 4 && !($leadershipcheck->isEmpty())){ //if employee is not an agent and exists in leaders table
                              $formEntries->push([
                                  'competency'=>$comp->name, 
                                  'definitions'=> $comp->definitions, 
                                  'percentage'=>$comp->percentage,
                                  'score'=>$comp->percentage*5/100,
                                  'id'=>$comp->id,
                                  'attributes'=> $attributes]);

                              $maxScore += $comp->percentage*5/100;
                              //var_dump($maxScore);
                               //var_dump("pasok ". $maxScore);
                          
                          } else { }//deadma kasi comp for agent
                    }//end else agentPercentage == null


              

            } else { //end else acrosstheboard sya

              // ************************ !!! always check first kung leader ba sya or agent
              if ($leadershipcheck->isEmpty() ){ //agent sya
                $formEntries->push([
                      'competency'=>$comp->name, 
                      'definitions'=> $comp->definitions, 
                      'percentage'=>$comp->agentPercentage,
                      'score'=>$comp->agentPercentage*5/100,
                      'id'=>$comp->id,
                      'attributes'=> $attributes]);

                  $maxScore += $comp->agentPercentage*5/100;
                 // var_dump($maxScore);

              } else { //leader sya
                 $formEntries->push([
                    'competency'=>$comp->name, 
                    'definitions'=> $comp->definitions, 
                    'percentage'=>$comp->percentage,
                    'score'=>$comp->percentage*5/100,
                    'id'=>$comp->id,
                    'attributes'=> $attributes]);

                $maxScore += $comp->percentage*5/100;
                //var_dump($maxScore);


              }                


            }//end else acrosstheboard

        }//end foreach competencies

        

        //$existingNaBa = EvalForm::where('evalSetting_id',$evalSetting->id)->where('user_id',$employee->id)->where('startPeriod',$currentPeriod)->get();
        //$existingNaBa = EvalForm::where('evalSetting_id',$evalSetting->id)->where('user_id',$employee->id)->where('startPeriod','>=',$currentPeriod)->where('endPeriod','<=',$endPeriod)->get();
        $existingNaBa = EvalForm::where('evalSetting_id',$evalSetting->id)->where('user_id',$employee->id)->where('evaluatedBy',$meLeader->id)->where('startPeriod','>=',$currentPeriod->startOfDay())->where('endPeriod','<=',$endPeriod->startOfDay())->get();

        if ($existingNaBa->isEmpty())
        {
            $evalForm = new EvalForm;
            $evalForm->coachingDone = false;
            $evalForm->coachingTimestamp = null;
            $evalForm->overallScore = 0;
            $evalForm->salaryIncrease = 0;
            $evalForm->startPeriod = $currentPeriod;
            $evalForm->endPeriod = $endPeriod;
            $evalForm->evalSetting_id = $evalSetting->id;
            $evalForm->user_id = $employee->id;

            // *** We need to check first if ikaw ung current immediate head
            // *** if not, then check kung may movement si employee within the current period
            
            $hisCurrentIH = ImmediateHead::find(ImmediateHead_Campaign::find(Team::where('user_id',$employee->id)->first()->immediateHead_Campaigns_id)->immediateHead_id);

            if ( $hisCurrentIH->employeeNumber == $this->user->employeeNumber ) $evalForm->evaluatedBy = $employee->supervisor->immediateHead_Campaigns_id;
            else
            {
              $hasIHmovement = Movement::where('user_id', $employee->id)->where('personnelChange_id','1')->where('effectivity','>=',$currentPeriod->startOfDay())->where('effectivity','<=', $endPeriod->startOfDay())->first();
              if (!empty($hasIHmovement))
                $evalForm->evaluatedBy = $hasIHmovement->immediateHead_details->imHeadCampID_old;
              else
                // ** just give it to the current
                $evalForm->evaluatedBy = $employee->supervisor->immediateHead_Campaigns_id;


            }
            
            $evalForm->isDraft = false;
            $evalForm->save();


        } else
        {
            $evalForm = $existingNaBa->first();


        } 

        //return $competencies;
      return view('evaluation.new-employee', compact('evalType', 'currentPeriod','endPeriod','employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
        
    }

    public function create()
    {

    }

    public function edit($id)
    {
      //check first if the one editing is the evaluator
      
        $evalForm = EvalForm::find($id);
        $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($evalForm->evaluatedBy)->immediateHead_id);

        if ($this->user->employeeNumber == $evaluator->employeeNumber){
        $details = $evalForm->details;
        

        $evalType = EvalType::find($evalForm->setting->evalType_id);
        $evalSetting = EvalSetting::find($evalForm->evalSetting_id);
        

        $employee = User::find($evalForm->user_id);
        $ratingScale = RatingScale::all();

        $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();





         $startPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->startPeriod);
         $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->endPeriod);

        

        $competencyAttributes = $evalSetting->competencyAttributes;
        $competencies = $competencyAttributes->groupBy('competency_id');
        $formEntries = new Collection;
        $maxScore = 0;

        foreach ($competencies as $key ) {
            $attributes = new Collection;

            foreach ($key as $k) {
                $attributes->push(Attribute::find($k->attribute_id)->name);
            }

           
               
            
           
         }

        /* ---------- SETUP DETAILS -------------*/
        $ctr = 1;
        foreach ($details as $detail) {
                $comp = Competency__Attribute::find($detail->competency__Attribute_id)->competency;
                $attr = Competency__Attribute::find($detail->competency__Attribute_id)->attribute;

                $rating = RatingScale::find($detail->ratingScale_id); //->ratings; //->label;'ratings'=> $rating['label']


                // --------------- generate form elements based on leader/agent competencies

                      if( $comp['acrossTheBoard'] ){

                        if ( $leadershipcheck->isEmpty()  ){ //agent

                         $formEntries->push([
                          'competency'=> $comp['name'], 
                          'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'id'=>$comp['id'],
                          'detailID'=> $detail->id,
                          'attributes'=>$attributes, //$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                           if ($ctr % 2 !=0 ){
                            $maxScore += $comp['agentPercentage']*5/100;
                           // var_dump("comp: ". $comp['percentage']*5/100);
                           } else {} //var_dump("even");
                         

                        } else {
                          $formEntries->push([
                          'competency'=> $comp['name'], 
                          'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'id'=>$comp['id'],
                          'detailID'=> $detail->id,
                          'attributes'=>$attributes, //$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        if ($ctr % 2 !=0 ){
                          $maxScore += $comp['percentage']*5/100;
                         // var_dump("comp: ". $comp['percentage']*5/100);
                         } else {} //var_dump("even");

                        }

                      } else { //else not acrossTheBoard

                        if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                          $formEntries->push([
                            'competency'=> $comp['name'], 
                            'definitions'=>$comp['definitions'],
                            'percentage'=>$comp['agentPercentage'], 
                            'id'=>$comp['id'],
                            'detailID'=> $detail->id,
                            'attributes'=>$attributes, //$attr['name'],
                            'value'=> $detail->attributeValue,
                            'rating'=> $rating ]);

                             if ($ctr % 2 !=0 ){
                              $maxScore += $comp['agentPercentage']*5/100;
                             // var_dump("comp: ". $comp['percentage']*5/100);
                             } else {} //var_dump("even");

                        } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                         $formEntries->push([
                          'competency'=> $comp['name'], 
                          'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'id'=>$comp['id'],
                          'detailID'=> $detail->id,
                          'attributes'=>$attributes, //$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating ]);

                        if ($ctr % 2 !=0 ){
                          $maxScore += $comp['percentage']*5/100;
                         // var_dump("comp: ". $comp['percentage']*5/100);
                         } else {} //var_dump("even");

                        }

                      }


                      // --------------- end generate form elements

                
                /************* check first if agentPercentage is null ***************/
               

               
               $ctr++;
                
                
                //r_dump("maxScore: ". $maxScore);
            }
            //return $formEntries->groupBy('competency');



            //get all Performance Summary values

            $allSummaries = Summary::all();
            $summaries = new Collection;

            foreach ($allSummaries as $key ) {
               if (!($key->columns->isEmpty()) ) 
                {
                    $cols = $key->columns;

                   
                } else $cols=null;
               if (!($key->rows->isEmpty()) )
               { 
                    $rows = $key->rows;

               }  else $rows = null;

               $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
               // $sValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->get(); //->first()->value;
               // if (count($sValue) > 0)
               //    $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
               // else 
               //    $summaryValue = null;

               $summaries->push(['summaryID'=>$key->id,'summaryValue'=>$summaryValue,  'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
            }

            $perfSum = PerformanceSummary::where('evalForm_id', $evalForm->id)->get();

            if ($perfSum->isEmpty()){
                $performanceSummary = null;

            } else {
                $performanceSummary = new Collection;
                $idx = 0;
                foreach ($perfSum as $ps) {
                    $performanceSummary[$idx] = ['id'=>$ps->id, 'value'=> $ps->value];
                    $idx++;
                }

            }

        /* ---------- END SETUP DETAILS -------- */

         
       //return $formEntries;
       return view('evaluation.edit-employee', compact('performanceSummary','startPeriod', 'endPeriod', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries', 'details'));


        

      } else return "Sorry, you are not ". $evaluator->firstname." ".$evaluator->lastname. ". <br/> How dare you trying to edit this evaluation?!! :P ";// Redirect::route('evalForm.show',$id);
        



    }//end edit()


    public function show($id)
    {
        $evalForm = EvalForm::find($id);
        
       
        // we need to check first the permissions who can view this particular eval
        // if SUPER ADMIN or your subordinate, then yes. Otherwise..cannot

        if (count($evalForm)==0){
          return view('empty');
        }
        $employee = User::find($evalForm->user_id);
        $allowed = false;

        $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();

        // check if ALLOWED TO EDIT
        //if (ImmediateHead::find($employee->immediateHead_Campaigns_id)->employeeNumber == $this->user->employeeNumber ){ $allowed = true; }

        if ( (ImmediateHead::find(ImmediateHead_Campaign::find($evalForm->evaluatedBy)->immediateHead_id)->employeeNumber == $this->user->employeeNumber  )) $allowed = true;

        if( $this->user->userType_id == 1 || $this->user->userType_id == 5 ||  $allowed || $this->user->id == $employee->id ){

              $details = $evalForm->details;


              $evalType = EvalType::find($evalForm->setting->evalType_id);
              $evalSetting = EvalSetting::find($evalForm->evalSetting_id);
              

              
              $ratingScale = RatingScale::all();



              //$evalSetting = EvalSetting::where('evalType_id',$evalType_id)->first();

              //setup now the evaluation form
              // !! but check first if it's existing already, if not -- create new one

              $currentPeriod = new \DateTime(date("Y")."-".$evalForm->setting->startMonth."-".$evalForm->setting->startDate." 00:00:00"); // " "2010-07-05T06:00:00Z"); date_create(date("Y").",timezone_open("Europe/Oslo"));
              $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
              $currentPeriod->setTime(0,0,0);

              $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod)->get();
              $doneEval = new Collection;
              if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null];
                  else $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id];

              

              $competencyAttributes = $evalForm->setting->competencyAttributes;
              $competencies = $competencyAttributes->groupBy('competency_id');
              $formEntries = new Collection;
              $maxScore = 0;

              $formEntries = new Collection;

              if ($details->isEmpty()){
                  
                  return $this->newEvaluation($employee->id,$evalType->id);

              } else
              {
                  foreach ($details as $detail) {
                      $comp = Competency__Attribute::find($detail->competency__Attribute_id)->competency;
                      $attr = Competency__Attribute::find($detail->competency__Attribute_id)->attribute;

                      $rating = RatingScale::find($detail->ratingScale_id); //->ratings; //->label;'ratings'=> $rating['label']
                      //var_dump($comp);

                      //if (empty($comp['agentPercentage'])){

                      // --------------- generate form elements based on leader/agent competencies

                      if( $comp['acrossTheBoard'] ){

                        if ( $leadershipcheck->isEmpty()  ){ //agent

                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating,
                          'detailID'=>$detail->id ]);
                         

                        } else {
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating,
                          'detailID'=>$detail->id  ]);

                        }

                      } else { //else not acrossTheBoard

                        if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['agentPercentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating,
                          'detailID'=>$detail->id  ]);

                        } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                          $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                          'percentage'=>$comp['percentage'], 
                          'attribute'=>$attr['name'],
                          'value'=> $detail->attributeValue,
                          'rating'=> $rating,
                          'detailID'=>$detail->id  ]);

                        }

                      }


                      // --------------- end generate form elements
                      
                  }
                 // return $details; //$formEntries->groupBy('competency');



                  //get all Performance Summary values

                  $allSummaries = Summary::all();
                  $summaries = new Collection;

                  foreach ($allSummaries as $key ) {
                     if (!($key->columns->isEmpty()) ) 
                      {
                          $cols = $key->columns;

                          // foreach ($cols as $c) {
                          //     $colVal =  PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                          //     $colValues[] = 
                          // }
                      } else $cols=null;
                     if (!($key->rows->isEmpty()) )
                     { 
                          $rows = $key->rows;

                     }  else $rows = null;

                     $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                     $summaries->push(['summaryID'=>$key->id,'summaryValue'=>$summaryValue,  'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
                  }

                  $perfSum = PerformanceSummary::where('evalForm_id', $evalForm->id)->get();

                  if ($perfSum->isEmpty()){
                      $performanceSummary = null;

                  } else {
                      $performanceSummary = new Collection;
                      $idx = 0;
                      foreach ($perfSum as $ps) {
                          $performanceSummary[$idx] = $ps->value;
                          $idx++;
                      }

                  }
                  

                   $startPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->startPeriod);
                   $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->endPeriod);
                   //$evaluator = ImmediateHead::find($evalForm->evaluatedBy);
                   $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($evalForm->evaluatedBy)->immediateHead_id);


                   /* -------  for maxscore -----*/

                   foreach ($competencies as $key ) {
                       
                        $comp = Competency::find($key[0]->competency_id);


                        if( $comp['acrossTheBoard'] )
                        {

                              if ( $leadershipcheck->isEmpty()  ){ //agent

                                 $maxScore += $comp->agentPercentage*5/100;
                               

                              } else {
                                $maxScore += $comp->percentage*5/100;

                              }

                       } else 
                            { //else not acrossTheBoard

                                if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                                    $maxScore += $comp->agentPercentage*5/100;

                                } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                                  $maxScore += $comp->percentage*5/100;

                                }

                        }

                      
                        
                       
                    }




                      //return $formEntries;

                   /* ------- end for maxscore ---- */

                  return view('evaluation.show-employee', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
              }        

        } else {

          return view('access-denied');

        }

        
        
        
    }//end show()

    public function review($id)
    {
        $evalForm = EvalForm::find($id);

        if (count($evalForm)==0){
              return view('empty');
        } else { //check first if the one reviewing is the owner

            $employee = User::find($evalForm->user_id);
            $allowed = false;

          if ( $this->user->id !== $employee->id) return view('access-denied');

        }

        // before doing anything, check mo kung reviewed na
        // if yes, no need to review again, return mo lang ulit

        if ($evalForm->coachingDone) return redirect()->action('EvalFormController@show',$id);
        else 
        {

            // we need to check first the permissions who can view this particular eval
            // if SUPER ADMIN or your subordinate, then yes. Otherwise..cannot

            
            

            $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();

            //if (ImmediateHead::find($employee->immediateHead_Campaigns_id)->employeeNumber == $this->user->employeeNumber ){ $allowed = true; }
            if ($evalForm->user_id == $this->user->id) $allowed=true;

            if( $this->user->userType_id == 1 ||  $allowed  ){ //|| $this->user->id == $employee->id

                  $details = $evalForm->details;

                  $evalType = EvalType::find($evalForm->setting->evalType_id);
                  $evalSetting = EvalSetting::find($evalForm->evalSetting_id);
                  

                  
                  $ratingScale = RatingScale::all();



                  //$evalSetting = EvalSetting::where('evalType_id',$evalType_id)->first();

                  //setup now the evaluation form
                  // !! but check first if it's existing already, if not -- create new one

                  $currentPeriod = new \DateTime(date("Y")."-".$evalForm->setting->startMonth."-".$evalForm->setting->startDate." 00:00:00"); // " "2010-07-05T06:00:00Z"); date_create(date("Y").",timezone_open("Europe/Oslo"));
                  $currentPeriod->setTimeZone(new \DateTimeZone("Asia/Manila"));
                  $currentPeriod->setTime(0,0,0);

                  $existing = EvalForm::where('user_id', $employee->id)->where('startPeriod',$currentPeriod)->get();
                  $doneEval = new Collection;
                  if ($existing->isEmpty()) $doneEval[$employee->id] = ['evaluated'=>0, 'evalForm_id'=> null];
                      else $doneEval[$employee->id] = ['evaluated'=>1, 'evalForm_id'=> $existing->first()->id];

                  

                  $competencyAttributes = $evalForm->setting->competencyAttributes;
                  $competencies = $competencyAttributes->groupBy('competency_id');
                  $formEntries = new Collection;
                  $maxScore = 0;

                  $formEntries = new Collection;

                  if ($details->isEmpty()){
                      
                      return $this->newEvaluation($employee->id,$evalType->id);

                  } else
                  {
                      foreach ($details as $detail) {
                          $comp = Competency__Attribute::find($detail->competency__Attribute_id)->competency;
                          $attr = Competency__Attribute::find($detail->competency__Attribute_id)->attribute;

                          $rating = RatingScale::find($detail->ratingScale_id); //->ratings; //->label;'ratings'=> $rating['label']
                          //var_dump($comp);

                          //if (empty($comp['agentPercentage'])){

                          // --------------- generate form elements based on leader/agent competencies

                          if( $comp['acrossTheBoard'] ){

                            if ( $leadershipcheck->isEmpty()  ){ //agent

                              $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                              'percentage'=>$comp['agentPercentage'], 
                              'attribute'=>$attr['name'],
                              'id'=>$comp['id'],
                              'value'=> $detail->attributeValue,
                              'detailID'=> $detail->id,
                              'rating'=> $rating ]);
                             

                            } else {
                              $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                              'percentage'=>$comp['percentage'], 
                              'attribute'=>$attr['name'],
                              'id'=>$comp['id'],
                              'value'=> $detail->attributeValue,
                              'detailID'=> $detail->id,
                              'rating'=> $rating ]);

                            }

                          } else { //else not acrossTheBoard

                            if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                              $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                              'percentage'=>$comp['agentPercentage'], 
                              'attribute'=>$attr['name'],
                              'id'=>$comp['id'],
                              'value'=> $detail->attributeValue,
                              'detailID'=> $detail->id,
                              'rating'=> $rating ]);

                            } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                              $formEntries->push(['competency'=> $comp['name'], 'definitions'=>$comp['definitions'],
                              'percentage'=>$comp['percentage'], 
                              'attribute'=>$attr['name'],
                              'id'=>$comp['id'],
                              'value'=> $detail->attributeValue,
                              'detailID'=> $detail->id,
                              'rating'=> $rating ]);

                            }

                          }


                          // --------------- end generate form elements
                          
                      }
                     // return $details; //$formEntries->groupBy('competency');



                      //get all Performance Summary values

                      $allSummaries = Summary::all();
                      $summaries = new Collection;

                      foreach ($allSummaries as $key ) {
                         if (!($key->columns->isEmpty()) ) 
                          {
                              $cols = $key->columns;

                              // foreach ($cols as $c) {
                              //     $colVal =  PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                              //     $colValues[] = 
                              // }
                          } else $cols=null;
                         if (!($key->rows->isEmpty()) )
                         { 
                              $rows = $key->rows;

                         }  else $rows = null;

                         $summaryValue = PerformanceSummary::where('summary_id',$key->id)->where('evalForm_id',$evalForm->id)->first()->value;
                         $summaries->push(['summaryID'=>$key->id,'summaryValue'=>$summaryValue,  'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
                      }

                      $perfSum = PerformanceSummary::where('evalForm_id', $evalForm->id)->get();

                      if ($perfSum->isEmpty()){
                          $performanceSummary = null;

                      } else {
                          $performanceSummary = new Collection;
                          $idx = 0;
                          foreach ($perfSum as $ps) {
                              $performanceSummary[$idx] = $ps->value;
                              $idx++;
                          }

                      }
                      

                       $startPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->startPeriod);
                       $endPeriod = Carbon::createFromFormat('Y-m-d H:i:s',$evalForm->endPeriod);
                       //$evaluator = ImmediateHead::find($evalForm->evaluatedBy);
                       $evaluator = ImmediateHead::find(ImmediateHead_Campaign::find($evalForm->evaluatedBy)->immediateHead_id);


                       /* -------  for maxscore -----*/

                       foreach ($competencies as $key ) {
                           
                            $comp = Competency::find($key[0]->competency_id);


                            if( $comp['acrossTheBoard'] )
                            {

                                  if ( $leadershipcheck->isEmpty()  ){ //agent

                                     $maxScore += $comp->agentPercentage*5/100;
                                   

                                  } else {
                                    $maxScore += $comp->percentage*5/100;

                                  }

                           } else 
                                { //else not acrossTheBoard

                                    if ( empty($comp['percentage']) && $leadershipcheck->isEmpty()){ //agent sya
                                        $maxScore += $comp->agentPercentage*5/100;

                                    } else if (!empty($comp['percentage']) && !$leadershipcheck->isEmpty()){ //leader sya
                                      $maxScore += $comp->percentage*5/100;

                                    }

                            }

                          
                            
                           
                        }






                       /* ------- end for maxscore ---- */

                      return view('evaluation.review-employee', compact('allowed', 'doneEval', 'evaluator', 'startPeriod', 'endPeriod', 'performanceSummary', 'evalType', 'employee', 'ratingScale', 'evalForm','evalSetting', 'formEntries','maxScore','summaries'));
                  }        

            } else {

              return view('access-denied');

            }


        }// end else !coachingDone
        


        
        
        
        
    }//end review()

    public function destroy($id)
    {
      $this->evalForm->destroy($id);
      return back();

    }

    public function update($id, Request $request)
    {
        $evalForm = EvalForm::find($id);

        $details = $evalForm->details;


        $coll = new Collection;
             /* -------------- log updates made --------------------- */
         $file = fopen('public/build/changes.txt', 'a') or die("Unable to open logs");

         $ctr = 1;
         $var = null;

        foreach ($details as $deet) {
            $attvar = "att_".$deet->id;
            $ratingvar = "rating_".$deet->id;

             //check if things were changed

           // if ($deet->ratingScale_id !== (int)Input::get($ratingvar)) {
                
                if ( Input::get($ratingvar) == null){
                    //if no val for new rating, just check the value
                    if (Input::get($attvar) == $deet->attributeValue) { } //same, do nothing
                    else {
                        $changeme = EvalDetail::find($deet->id);
                        $changeme->attributeValue = Input::get($attvar); //theres a new value
                        $changeme->push();
                    } 
                } else {
                    $changeme = EvalDetail::find($deet->id);
                    $changeme->ratingScale_id = (int)Input::get($ratingvar);
                    $changeme->attributeValue = Input::get($attvar);
                    $changeme->push();
                }

         //   }





            // if ($ctr % 2 == 0) {  
            //    //$changeme2 = EvalDetail::find($deet->id-1)->ratingScale_id;
               
            //    $changeme = EvalDetail::find($deet->id);
            //    $changeme->ratingScale_id = $var;
            //    $changeme->attributeValue = Input::get($attvar);
            //    fwrite($file,"\n ID: ". $deet->id. " RSid: ". $var." Deet->id: ". $changeme->id. " From: if\n" );
            //    //$changeme->push();
            // } else {

            //    $changeme = EvalDetail::find($deet->id);
            //     $changeme->ratingScale_id = (int)Input::get($ratingvar);
            //     $changeme->attributeValue = Input::get($attvar);
            //     //$changeme->push();
            //     $var = (int)Input::get($ratingvar);
            //     fwrite($file,"\n ID: ". $deet->id. " RSid: ". (int)Input::get($ratingvar)." Deet->id: ". $changeme->id. " From: else \n" );


            // } $ctr++;

            //check if things were changed

           


           // if ( Input::get($ratingvar) == null){ //($ctr % 2 == 0) {   //
           //          //if no val for new rating, just check the value
           //          if (Input::get($attvar) == $deet->attributeValue) {  
           //            fwrite($file,"\n --- do nothing --- \n" );
           //          } //same, do nothing
           //          else {
           //              $changeme = EvalDetail::find($deet->id);
           //              $changeme->attributeValue = Input::get($attvar); //theres a new value
           //              $changeme->push();
           //              fwrite($file,"\n DeetID: ". $deet->id." att: ". $changeme->attributeValue );

           //              //this is to fix bug for Notes & Employee Notes attributes
           //              //kasi they generate 2 items, so update mo rin ung for employee notes
           //             // if($ctr !== 1){
           //                $changeme2 = EvalDetail::find($deet->id-1);
           //                //$changeme2->ratingScale_id = (int)Input::get($ratingvar);
           //                //$changeme2->attributeValue = Input::get($attvar);
           //                $changeme2->push();
           //                fwrite($file,"\n ChangeMe: ". $changeme2->id. " RS: ". $changeme2->ratingScale_id);

           //           //   }
                        

           //              $coll->push(['rating'=>$changeme->ratingScale_id,'att'=>$changeme->attributeValue, 'from'=>"if!="]);
                        
           //          } 
           //      } else {
           //          $changeme = EvalDetail::find($deet->id);
           //          $changeme->ratingScale_id = (int)Input::get($ratingvar);
           //           $changeme->attributeValue = Input::get($attvar);
           //          $changeme->push();
           //          fwrite($file,"\n DeetID else: ". $deet->id ." att: ". $changeme->attributeValue);

           //          $changeme2 = EvalDetail::find($deet->id-1);
           //          $changeme2->ratingScale_id = (int)Input::get($ratingvar);
           //          //$changeme2->attributeValue = Input::get($attvar);
           //          $changeme2->push();
           //          fwrite($file,"\n ChangeMe: ". $changeme2->id . " RS: ". $changeme2->ratingScale_id);
                    
           //      }

         

        
                
               
               
                
               
           
        }

        //return $coll;

       


            $evalForm->coachingDone = $request->coachingDone;
            $evalForm->overAllScore = $request->overAllScore;
            $evalForm->salaryIncrease = $request->salaryIncrease;
            $evalForm->isDraft = $request->isDraft;
            $evalForm->push();


            //save Performance Summary
            $allSummaries = Summary::all();
            $summaries = new Collection;

            foreach ($allSummaries as $key ) {
               if (!($key->columns->isEmpty()) ) 
                {
                    $cols = $key->columns;
                } else $cols=null;
               if (!($key->rows->isEmpty()) )
               { 
                    $rows = $key->rows;

               }  else $rows = null;

               $summaries->push(['summaryID'=>$key->id, 'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
            }

            //$psum = new Collection;

            $ctrSummary=1; 
            foreach ($summaries as $summary){
                
                                    if ( $summary['columns'] !== null)
                                    { 
                                    
                                       foreach ($summary['columns'] as $col)
                                       {
                                        
                                        $varname = 'val_'.$ctrSummary.'_'.$col->id;
                                        $idvar = 'id_'.$ctrSummary.'_'.$col->id;

                                        $ps = PerformanceSummary::find((int)$request->$idvar);

                                        if (!$ps->isEmpty){
                                            if ($ps->value !== $request->$varname){
                                            $ps->value = $request->$varname;
                                            $ps->push();
                                        }

                                        }

                                        
                                        
                                        

                                       }
                                      
                                   }

                                    if ( $summary['rows'] !== null) 
                                    {
                                        foreach ($summary['rows'] as $row)
                                        { 
                                            
                                            $var2 = 'val_'.$ctrSummary.'_'.$row->id;
                                            $idvar2 = 'id_'.$ctrSummary.'_'.$row->id;

                                            $ps2 = PerformanceSummary::find((int)$request->$idvar2);
                                            if (count($ps2) !== 0){
                                                if ($ps2->value !== $request->$var2){
                                                $ps2->value = $request->$var2;
                                                $ps2->push();
                                            }

                                            }

                                            

                                           ;
                                        }
                                    
                                    }
                                     
                                    
                                    
                                    $ctrSummary++;

            }//end foreach summaries

       

        
            fwrite($file, "-------------------\n EvalID: ". $evalForm->id ." for: ". User::find($evalForm->user_id)->lastname.", ". User::find($evalForm->user_id)->firstname." updated ". date('M d h:i:s'). " by ". $this->user->firstname.", ".$this->user->lastname."\n");
            
           
            fclose($file);
        
        return response()->json($evalForm);

    }

    public function updateReview($id, Request $request)
    {
        $evalForm = EvalForm::find($id);

        $details = $evalForm->details;
        $ctr = 0;

        $arrStat = new Collection;


        
        foreach ($details as $deet) {
            $attvar = "att_".$deet->id;
            $empCheck = Competency__Attribute::find($deet->competency__Attribute_id)->attribute;
            $feedback = $empCheck->name;

            if ($feedback === "Employee Feedback"){

                    // if (Input::get($attvar) == $deet->attributeValue) { } //same, do nothing
                    // else {
                        $changeme = EvalDetail::find($deet->id);
                        $changeme->attributeValue = Input::get($attvar); //theres a new value
                        $changeme->push();
                    //} 
                        $arrStat->push($deet->id);

            }

            
           
          }
        $evalForm->coachingDone = true;
        $evalForm->coachingTimestamp = date('Y-m-d h:i:s');
        
        $evalForm->push();

        return response()->json($arrStat);

    }

    //public function

    public function store(Request $request)
    {
        
        $evalForm = EvalForm::find($request->evalForm_id);

        if (!$evalForm->isEmpty)
        {
            $evalSetting = EvalSetting::find($evalForm->evalSetting_id);
            

            $competencyAttributes = $evalSetting->competencyAttributes;
            $competencies = $competencyAttributes->groupBy('competency_id');
            $coll = new Collection;

            $employee = User::find($evalForm->user_id);
            $leadershipcheck = ImmediateHead::where('employeeNumber', $employee->employeeNumber)->get();

           
            $coll = new Collection;

            foreach ($competencies as $competency ) {

                if (Competency::find($competency[0]->competency_id)->acrossTheBoard) 
                {
                    //determine if leader or not

                   

                       $c=1;
                        foreach ($competency as $comp) {

                            $evalDetail = new EvalDetail;
                            $evalDetail->evalForm_id = $request->evalForm_id;
                            $evalDetail->competency__Attribute_id = $comp->id;


                            

                            // -------- quick fix for autosave kahit di pa complete
                            $rscale = Input::get('ratingScaleID_'.$comp->competency_id);
                            

                            if ($rscale == "0")
                            {
                              $evalDetail->ratingScale_id = 5; //automatic zero
                               $coll->push(['rsID'=>5]);

                            } else {
                              $evalDetail->ratingScale_id = $rscale; //$request->ratingScaleID_.;
                              $coll->push(['rsID'=>"nonzero"]);

                            }

                            

                            $evalDetail->attributeValue =  Input::get('attributeValue_'.$comp->competency_id.'_'.$c);//$request->attributeValue_.$comp->id.'_'.$c;
                           
                            $c++;
                            $evalDetail->save();
                        } //end foreach competency attribute

                    


                    


                }else // -----------  hindi across the board
                {

                  //check the properties of competency and match it whether agent or leader

                  if( empty(Competency::find($competency[0]->competency_id)->percentage) && $leadershipcheck->isEmpty() )
                    { //if null percentage and an agent, save it

                      $c=1;
                        foreach ($competency as $comp) {

                            $evalDetail = new EvalDetail;
                            $evalDetail->evalForm_id = $request->evalForm_id;
                            $evalDetail->competency__Attribute_id = $comp->id;
                            //$evalDetail->ratingScale_id = Input::get('ratingScaleID_'.$comp->competency_id); //$request->ratingScaleID_.;
                            $evalDetail->attributeValue =  Input::get('attributeValue_'.$comp->competency_id.'_'.$c);//$request->attributeValue_.$comp->id.'_'.$c;

                            // -------- quick fix for autosave kahit di pa complete
                            $rscale = Input::get('ratingScaleID_'.$comp->competency_id);
                            

                            if ($rscale == "0")
                            {
                              $evalDetail->ratingScale_id = 5; //automatic zero
                               $coll->push(['rsID'=>5]);

                            } else {
                              $evalDetail->ratingScale_id = $rscale; //$request->ratingScaleID_.;
                              $coll->push(['rsID'=>"nonzero"]);

                            }


                           
                            $c++;
                            $evalDetail->save();
                        } //end foreach competency attribute

                  } else  if( empty(Competency::find($competency[0]->competency_id)->percentage) && !($leadershipcheck->isEmpty()) ) {
                    //deadmahin mo lang kasi null percentage and leader sya

                  } else if ( !empty(Competency::find($competency[0]->competency_id)->percentage) && !($leadershipcheck->isEmpty()) ) {
                    //save mo kasi leader comp sya
                    $c=1;
                        foreach ($competency as $comp) {

                            $evalDetail = new EvalDetail;
                            $evalDetail->evalForm_id = $request->evalForm_id;
                            $evalDetail->competency__Attribute_id = $comp->id;
                            //$evalDetail->ratingScale_id = Input::get('ratingScaleID_'.$comp->competency_id); //$request->ratingScaleID_.;
                            $evalDetail->attributeValue =  Input::get('attributeValue_'.$comp->competency_id.'_'.$c);//$request->attributeValue_.$comp->id.'_'.$c;

                            // -------- quick fix for autosave kahit di pa complete
                            $rscale = Input::get('ratingScaleID_'.$comp->competency_id);
                            

                            if ($rscale == "0")
                            {
                              $evalDetail->ratingScale_id = 5; //automatic zero
                               $coll->push(['rsID'=>5]);

                            } else {
                              $evalDetail->ratingScale_id = $rscale; //$request->ratingScaleID_.;
                              $coll->push(['rsID'=>"nonzero"]);

                            }


                           
                            $c++;
                           $evalDetail->save();
                        } //end foreach competency attribute

                  }
                   

                }//end else hindi across




                
           
            }// end foreach grouped competencies

           

            $evalForm->coachingDone = $request->coachingDone;
            $evalForm->overAllScore = $request->total;
            $evalForm->salaryIncrease = $request->salaryIncrease;
            $evalForm->isDraft = $request->isDraft;
            $evalForm->save();


            //save Performance Summary
            $allSummaries = Summary::all();
            $summaries = new Collection;

            foreach ($allSummaries as $key ) {
               if (!($key->columns->isEmpty()) ) 
                {
                    $cols = $key->columns;
                } else $cols=null;
               if (!($key->rows->isEmpty()) )
               { 
                    $rows = $key->rows;

               }  else $rows = null;

               $summaries->push(['summaryID'=>$key->id, 'header'=>$key->heading,'details'=>$key->description, 'columns'=>$cols, 'rows'=>$rows]);
            }

            $psum = new Collection;

            $ctrSummary=1; 
            foreach ($summaries as $summary){
                
                                    if ( $summary['columns'] !== null)
                                    { 
                                    
                                       foreach ($summary['columns'] as $col)
                                       {
                                        $ps = new PerformanceSummary;
                                        $varname = 'val_'.$ctrSummary.'_'.$col->id;
                                        $ps->value = $request->$varname;
                                        //$ps->value = Input::get('val_'.$ctrSummary.'_'.$col->id);
                                        $ps->summary_id = $summary['summaryID'];
                                        $ps->evalForm_id = $evalForm->id;
                                        $ps->save();
                                        $psum->push($ps);

                                       }
                                      
                                   }

                                    if ( $summary['rows'] !== null) 
                                    {
                                        foreach ($summary['rows'] as $row)
                                        { 
                                            $ps = new PerformanceSummary;
                                            $var2 = 'val_'.$ctrSummary.'_'.$row->id;
                                            $ps->value = $request->$var2;
                                            //$ps->value = Input::get('val_'.$ctrSummary.'_'.$row->id);
                                            $ps->summary_id = $summary['summaryID'];
                                            $ps->evalForm_id = $evalForm->id;
                                            $ps->save();
                                            $psum->push($ps);
                                        }
                                    
                                    }
                                     
                                    
                                    
                                    $ctrSummary++;

            }//end foreach summaries

            /* -------------- log updates made --------------------- */
         $file = fopen('public/build/changes.txt', 'a') or die("Unable to open logs");
            fwrite($file, "-------------------\n EvalID: ". $evalForm->id ." for: ". $employee->lastname.", ". $employee->firstname." added ". date('M d h:i:s'). " by ". $this->user->firstname.", ".$this->user->lastname."\n");
            fclose($file);


            return response()->json(['saved'=>true, 'evalFormID' => $evalForm->id, 'psummary'=> $psum]);
            //return $psum;
        } //end if not empty
        else return response()->json(['saved'=>false, 'evalFormID' => '0', 'psummary'=>$psum]);

    }

}
