<?php

namespace OAMPI_Eval\Http\Controllers;

use Carbon\Carbon;
use Excel;
use \PDF;
use \App;
use \DB;
use \Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

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
use OAMPI_Eval\Restday;
use OAMPI_Eval\Cutoff;
use OAMPI_Eval\Biometrics;
use OAMPI_Eval\Biometrics_Uploader;
use OAMPI_Eval\Logs;
use OAMPI_Eval\LogType;
use OAMPI_Eval\TempUpload;
use OAMPI_Eval\User_DTR;
use OAMPI_Eval\MonthlySchedules;
use OAMPI_Eval\FixedSchedules;
use OAMPI_Eval\Paycutoff;
use OAMPI_Eval\User_CWS;
use OAMPI_Eval\User_OT;




class DTRController extends Controller
{
    protected $user;
   	protected $user_dtr;
    use Traits\TimekeepingTraits;



     public function __construct(User_DTR $user_dtr)
    {
        $this->middleware('auth');
        $this->user_dtr = $user_dtr;
        $this->user =  User::find(Auth::user()->id);
    }

    public function show($id, Request $request )
    {
        DB::connection()->disableQueryLog();
        $collect = new Collection;
        $roles = UserType::find($this->user->userType_id)->roles->pluck('label'); //->where('label','MOVE_EMPLOYEES');
        $canViewOtherDTR =  ($roles->contains('VIEW_OTHER_DTR')) ? '1':'0';
        $canViewTeamDTR =  ($roles->contains('VIEW_SUBORDINATE_DTR')) ? '1':'0';
        $canChangeSched =  ($roles->contains('CHANGE_EMPLOYEE_SCHEDULE')) ? '1':'0';
        $hrDept = Campaign::where('name',"HR")->first();
        $financeDept = Campaign::where('name',"Finance")->first();
        $paycutoffs = Paycutoff::all();

        $user = User::find($id);

        $leadershipcheck = ImmediateHead::where('employeeNumber', $user->employeeNumber)->first();
        $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->supervisor->immediateHead_Campaigns_id)->immediateHead_id);
        if($immediateHead->employeeNumber == $this->user->employeeNumber ) $theImmediateHead = true; else $theImmediateHead=false;

        if (!empty($leadershipcheck)){ $camps = $leadershipcheck->campaigns->sortBy('name'); } else $camps = $user->campaign;



       // check if viewing is not an agent, an HR personnel, or the owner, or youre the immediateHead $this->user->campaign_id == $hrDept->id 
        
        if ($canViewOtherDTR ||  $this->user->id == $id || ($immediateHead->employeeNumber == $this->user->employeeNumber && $canViewTeamDTR)  )  //($this->user->userType_id == 1 || $this->user->userType_id == 2)
        {          

             //$cutoff = date('M d, Y', strtotime(Cutoff::first()->startingPeriod())). " - " . date('M d, Y',strtotime(Cutoff::first()->endingPeriod()));
               if(empty($request->from) && empty($request->to) )
               {
                $currPeriod =  Cutoff::first()->getCurrentPeriod();
                $currentPeriod = explode('_', $currPeriod);
                $cutoffStart = new Carbon(Cutoff::first()->startingPeriod());
                $cutoffEnd = new Carbon(Cutoff::first()->endingPeriod());
                $cutoffID = Paycutoff::where('fromDate',$currentPeriod[0])->first()->id;

                
              }else 
              {
                $currentPeriod[0] = $request->from;
                $currentPeriod[1] = $request->to;
                $cutoffStart = new Carbon($currentPeriod[0]);
                $cutoffEnd = new Carbon($currentPeriod[1]);

                if (count($cid = Paycutoff::where('fromDate',$request->from)->get())>0)
                  $cutoffID = $cid->first()->id;
                else $cutoffID=0;
               

              }
              
              $getday = explode('-',$currentPeriod[0]);
               if ($getday[2] < Cutoff::first()->second)
                {
                  $prevF= Carbon::createFromDate(null,date('m',strtotime($currentPeriod[0])),Cutoff::first()->second+1);
                  $prevFrom = $prevF->subMonth()->format('Y-m-d');
                  
                  $prevTo = Carbon::parse($currentPeriod[0])->subDay()->format('Y-m-d');
                  $nextFrom = Carbon::parse($currentPeriod[1])->addDay()->format('Y-m-d');
                  $nextTo = Carbon::createFromDate(null,date('m',strtotime($currentPeriod[1]))+1,Cutoff::first()->first)->format('Y-m-d');
                  
                }
                else
                {
                  $m = date('m',strtotime($currentPeriod[0]));
                  
                  $prevFrom = Carbon::createFromDate(null,$m,Cutoff::first()->first+1)->format('Y-m-d');
                  $prevTo = Carbon::parse($currentPeriod[0])->subDay()->format('Y-m-d');
                  $nextFrom = Carbon::parse($currentPeriod[1])->addDay()->format('Y-m-d');
                  $nextTo = Carbon::createFromDate(null,date('m',strtotime($currentPeriod[1])),Cutoff::first()->second)->format('Y-m-d');
                }

              $cutoff = date('M d, Y', strtotime($currentPeriod[0])). " - ". date('M d,Y', strtotime($currentPeriod[1])); 

              // $collect->push(['0'=>$currentPeriod[0], '1'=>$currentPeriod[1],'cutoff'=>$cutoff,'prev'=>$prevFrom." - ".$prevTo, 'next'=>$nextFrom." - ".$nextTo]);
              // return $collect;

              

             // ---------------------------
             // Generate cutoff period
             //----------------------------

             $payrollPeriod = [];
             
             $noWorkSched = false;

             //Timekeeping Trait
             $payrollPeriod = $this->getPayrollPeriod($cutoffStart,$cutoffEnd);

             // ---------------------------  INITIALIZATIONS
             $myDTR = new Collection;
             $daysOfWeek = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun'); // for Gregorian cal. Iba kasi jddayofweek sa PHP day
             $coll = new Collection; $nightShift=""; $panggabi=""; $approvedOT=0; $billableForOT=0; $UT=0; $workSched=null; 
             $hasApprovedCWS=false; $usercws=null;$userOT=null; $OTattribute=""; $hasOT=false; $hasApprovedOT=false; $isFlexitime=null;$workedHours=null;


             

             // ---------------------------
             // Determine first if FIXED OR SHIFTING sched
             // and then get WORKSCHED and RD sched
             // ---------------------------
              
             $noWorkSched = true;
             if (count($user->monthlySchedules) > 0)
              //if (!empty($user->monthlySchedules))
             {
                $monthlySched = MonthlySchedules::where('user_id',$id)->where('productionDate','>=', $currentPeriod[0])->where('productionDate','<=',$currentPeriod[1])->get();
                //$monthlySched = MonthlySchedules::where('productionDate','>=', $cutoffStart->format('Y-m-d'))->where('productionDate','<=',$cutoffEnd->format('Y-m-d'))->get();
                
               
                $workSched = MonthlySchedules::where('user_id',$id)->where('productionDate','>=', $currentPeriod[0])->where('productionDate','<=',$currentPeriod[1])->where('isRD',0)->get(); //Collection::make($monthlySched->where('isRD',0)->all());
                $RDsched = MonthlySchedules::where('user_id',$id)->where('productionDate','>=', $currentPeriod[0])->where('productionDate','<=',$currentPeriod[1])->where('isRD',1)->get();  //$monthlySched->where('isRD',1)->all();
                $isFixedSched = false;
                $noWorkSched = false;

             } else
             {
                if (count($user->fixedSchedule) > 0)
                {
                    //merong fixed sched
                    $workSched = $user->fixedSchedule->where('isRD',0);
                    $RDsched = $user->fixedSchedule->where('isRD',1)->pluck('workday');
                    $isFixedSched =true;
                    $noWorkSched = false;
                } else
                {
                    $noWorkSched = true;
                    $workSched = null;
                    $RDsched = null;
                    $isFixedSched = false;
                }
             }

             //return $RDsched;

             //**** ------- verify schedules if meron nga
             //if (empty($workSched) && empty($RDsched) ) $noWorkSched=true;


             //return $workSched;


             // ---------------------------
             // Start Payroll generation
             // ---------------------------
             //$coll->push(['cutoffStart'=>$cutoffStart->format('Y-m-d'), 'cutoffEnd'=>$cutoffEnd->format('Y-m-d'), 'workday'=>$monthlySched, 'RD'=>$RDsched, 'noSched'=>$noWorkSched]);
            

             $shifts = $this->generateShifts('12H');
              
             foreach ($payrollPeriod as $payday) 
             {

              $hasCWS = false; $hasApprovedCWS=false; $hasOT=false; $hasApprovedOT=false;

              $bioForTheDay = Biometrics::where('productionDate',$payday)->first();
              $nextDay = Carbon::parse($payday)->addDay();
              $prevDay = Carbon::parse($payday)->subDay();
              $bioForTom = Biometrics::where('productionDate',$nextDay->format('Y-m-d'))->first();
              if ( is_null($bioForTom) )
              {
                $bioForTomorrow = new Collection;
                $bioForTomorrow->push(['productionDate'=>$nextDay->format('Y-m-d')]);
              }
              else
                $bioForTomorrow = $bioForTom;




                             
                
                if($noWorkSched)
                {

                  if( is_null($bioForTheDay) ) 
                      {
                              $logIN = "<strong class=\"text-success\">No <br/>Biometrics</strong>";
                              $logOUT = "<strong class=\"text-success\">No <br/>Biometrics</strong>";
                              $workedHours = 'N/A';
                              

                      } else
                      {

                         
                        $usercws = User_CWS::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->get();
                        if ( count($usercws) > 0 ) $hasCWS=true;

                         $link = action('LogsController@viewRawBiometricsData',$id);
                         $icons = "<a title=\"Verify Biometrics data\" class=\"pull-right text-danger\" style=\"font-size:1.2em;\" target=\"_blank\" href=\"".$link."\"><i class=\"fa fa-clock-o\"></i></a>";

                          $userLogIN = Logs::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('logType_id',1)->orderBy('biometrics_id','ASC')->get();
                           if (count($userLogIN)==0)
                           {  
                              
                              $logIN = "<strong class=\"text-danger\">No IN</strong>".$icons;
                              $shiftStart = null;
                              $shiftEnd = "<em>No Saved Sched</em>";
                              $workedHours = "N/A";
                              
                           } else
                           {
                              $logIN = date('h:i A',strtotime($userLogIN->first()->logTime));
                              $timeStart = Carbon::parse($userLogIN->first()->logTime);
                           }


                          //--- RD OT, but check first if VALID. It should have a LogOUT AND approved OT
                          $userLogOUT = Logs::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('logType_id',2)->orderBy('biometrics_id','ASC')->get();

                          //--- ** May issue: pano kung RD OT ng gabi, then kinabukasan na sya nag LogOUT. Need to check kung may approved OT from IH
                          if( count($userLogOUT)==0 )
                          {
                              $logOUT = "<strong class=\"text-danger\">No OUT</strong>".$icons;
                              $workedHours = "N/A";
                              $shiftStart = "<em>No Saved Sched</em>";
                              $shiftEnd = "<em>No Saved Sched</em>";
                          } else 
                          { 
                                //--- legit OT, compute billable hours
                                $logOUT = date('h:i A',strtotime($userLogOUT->first()->logTime));
                                $timeEnd = Carbon::parse($userLogOUT->first()->logTime);
                                $workedHours = "N/A";
                                $shiftStart = null;
                                $shiftEnd = "<em>No Saved Sched</em>";
                          }

                           $myDTR->push(['payday'=>$payday,
                               'biometrics_id'=>$bioForTheDay->id,
                               'hasCWS'=>$hasCWS,
                                'usercws'=>$usercws->first(),
                                'userOT'=>$userOT,
                                'hasOT'=>$hasOT,
                               'isRD'=>0,
                               'isFlexitime'=>$isFlexitime,
                               'productionDate'=> date('M d, Y', strtotime($payday)),
                               'day'=> date('D',strtotime($payday)),
                               'shiftStart'=> null,
                               'shiftEnd'=>$shiftEnd,
                               'logIN' => $logIN,
                               'logOUT'=>$logOUT,
                               'workedHours'=> $workedHours,
                               'billableForOT' => $billableForOT,
                               'OTattribute'=>$OTattribute,
                               'UT'=>$UT,
                               'approvedOT' => $approvedOT]);

                      }// end if isnull bioForToday


                }
                else //Has Work Sched
                {


                      if( is_null($bioForTheDay) ) 
                      {
                              $logIN = "<strong class=\"text-success\">No <br/>Biometrics</strong>";
                              $logOUT = "<strong class=\"text-success\">No <br/>Biometrics</strong>";
                              $workedHours = 'N/A';
                              

                      } else
                      {

                         //--- We now check if employee has a CWS submitted for this day
                          //**************************************************************
                          //      CWS & OT
                          //**************************************************************
                        $usercws = User_CWS::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->get();
                        $approvedCWS  = User_CWS::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('isApproved',1)->get();
                        if ( count($usercws) > 0 ) $hasCWS=true;
                        if ( count($approvedCWS) > 0 ) $hasApprovedCWS=true;

                        $userOT = User_OT::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->get();
                        $approvedOT  = User_OT::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('isApproved',1)->get();
                        if ( count($userOT) > 0 ) $hasOT=true;
                        if ( count($approvedOT) > 0 ) $hasApprovedOT=true;


                       
                          //**************************************************************
                          //       FIXED SCHED
                          //**************************************************************

                          if($isFixedSched)
                          {
                              $day = date('D', strtotime($payday)); //--- get his worksched and RDsched
                              $theday = (string)$day;
                              $numDay = array_search($theday, $daysOfWeek);

                              $yest = date('D', strtotime(Carbon::parse($payday)->subDay()->format('Y-m-d')));
                              $prevNumDay = array_search($yest, $daysOfWeek);
                              //$coll->push(['day'=>$day,'theday-1'=>$yest, 'prevNumDay'=>$prevNumDay]);
                          }


                           //---------------------------
                          // to check for non same-day logs on a Rest Day, kunin mo yung prev sched
                          // if sameDayLog yun, proceed with normal RD process
                          // if prev sched is RD as well, kunin mo next sched
                          // if shift  is between 3am-2:59PM, yung logs nya eh within the day
                          // if ( ( $schedForToday->timeStart >= date('H:i:s',strtotime('03:00:00')) ) && ($schedForToday->timeStart <= date('H:i:s',strtotime('14:59:00'))) )
                          // {
                            $sameDayLog = true;
                          //} else $sameDayLog=false;

                            
                            
                            $UT = 0;
                            
                            //---------------------------------- Check if RD nya today

                            if($isFixedSched)
                              $isRDToday = $RDsched->contains($numDay); 
                            else
                            {
                              $rd = $monthlySched->where('isRD',1)->where('productionDate',$payday)->all();  
                              if (count($rd)<= 0 ) 
                                $isRDToday=false; else $isRDToday=true;
                            }
                           
                              
                            //**************************************************************
                            //       Rest Day SCHED
                            //**************************************************************

                            if ($isRDToday)
                            {



                                    if($sameDayLog)
                                    {
                                       $data = $this->getRDinfo($id, $bioForTheDay,true,$payday);
                                          $myDTR->push(['payday'=>$payday,
                                             'biometrics_id'=>$bioForTheDay->id,
                                             'hasCWS'=>$hasCWS,
                                             'usercws'=>$usercws,
                                             'userOT'=>$userOT,
                                             'hasApprovedCWS'=> $hasApprovedCWS,
                                             'hasOT'=>$hasOT,
                                             'hasApprovedOT'=>$hasApprovedOT,
                                             'isRD'=>$isRDToday,
                                             'isFlexitime'=> $isFlexitime,
                                             'productionDate'=> date('M d, Y', strtotime($payday)),
                                             'day'=> date('D',strtotime($payday)),
                                             'shiftStart'=> $data[0]['shiftStart'],
                                             'shiftEnd'=>$data[0]['shiftEnd'],
                                             'logIN' => $data[0]['logIN'],
                                             'logOUT'=>$data[0]['logOUT'],
                                             'workedHours'=> $data[0]['workedHours'],
                                             'billableForOT' => $data[0]['billableForOT'],
                                             'OTattribute' => $data[0]['OTattribute'],
                                             'UT'=>$data[0]['UT'],
                                             'approvedOT' => $data[0]['approvedOT']]);

                                    }
                                    else //****** not sameDayLog
                                    {
                                        $data = $this->getRDinfo($id, $bioForTheDay,false,$payday);
                                         $myDTR->push(['payday'=>$payday,
                                             'biometrics_id'=>$bioForTheDay->id,
                                             'hasCWS'=>$hasCWS,
                                             'usercws'=>$usercws,
                                             'userOT'=>$userOT,
                                             'hasApprovedCWS'=> $hasApprovedCWS,
                                             'hasOT'=>$hasOT,
                                             'hasApprovedOT'=>$hasApprovedOT,
                                             'isRD'=>$isRDToday,
                                             'isFlexitime'=> $isFlexitime,
                                             'productionDate'=> date('M d, Y', strtotime($payday)),
                                             'day'=> date('D',strtotime($payday)),
                                             'shiftStart'=> $data[0]['shiftStart'],
                                             'shiftEnd'=>$data[0]['shiftEnd'],
                                             'logIN' => $data[0]['logIN'],
                                             'logOUT'=>$data[0]['logOUT']."<br/><small>".$bioForTomorrow->productionDate."</small>",
                                             'workedHours'=> $data[0]['workedHours'],
                                             'billableForOT' => $data[0]['billableForOT'],
                                             'OTattribute' => $data[0]['OTattribute'],
                                             'UT'=>$data[0]['UT'],
                                             'approvedOT' => $data[0]['approvedOT']]);

                                    

                                    }//end RD not SAME DAY LOG

                                


                            }//end if isRDToday
                            //**************************************************************
                            //       WORK DAY
                            //**************************************************************
                            else  // --------------------------  it's not RD, its a WORKDAY
                            {
                              $problemArea = new Collection;
                              $problemArea->push(['problemShift'=>false, 'allotedTimeframe'=>null, 'biometrics_id'=>null]);
                              $isAproblemShift = false;


                             

                              if ($isFixedSched)
                              {
                                if ($hasApprovedCWS)
                                {
                                  if ( count($workSched->where('workday',$numDay)->all()) > 0 )
                                  {
                                    $schedForToday = array('timeStart'=>$approvedCWS->first()->timeStart, 
                                                          'timeEnd'=> $approvedCWS->first()->timeEnd,
                                                          'isFlexitime' =>  $workSched->where('workday',$numDay)->first()->isFlexitime,
                                                          'isRD'=> $workSched->where('workday',$numDay)->first()->isRD);

                                  } else
                                  {
                                    $schedForToday = array('timeStart'=>$approvedCWS->first()->timeStart, 
                                                          'timeEnd'=> $approvedCWS->first()->timeEnd,
                                                          'isFlexitime' => false,
                                                          'isRD'=> null);
                                  }
                                  
                                 

                                } else 
                                  $schedForToday = $workSched->where('workday',$numDay)->first();
                              }
                              else
                              {
                                if ($hasApprovedCWS)
                                {
                                  //--- hack for flexitime
                                  if ( count($workSched->where('productionDate',$payday)->all()) > 0 )
                                  {
                                    $schedForToday = array('timeStart'=>$approvedCWS->first()->timeStart, 
                                                          'timeEnd'=> $approvedCWS->first()->timeEnd, 
                                                          'isFlexitime'=>$workSched->where('productionDate',$payday)->first()->isFlexitime,
                                                          'isRD'=>$workSched->where('productionDate',$payday)->first()->isRD);

                                  } else 
                                  {
                                    $schedForToday = array('timeStart'=>$approvedCWS->first()->timeStart, 
                                                          'timeEnd'=> $approvedCWS->first()->timeEnd, 
                                                          'isFlexitime'=>false,
                                                          'isRD'=>null);

                                  }
                                  
                                 

                                } else 
                                  $schedForToday = $workSched->where('productionDate',$payday)->first();

                              }


                                    $coll->push(['schedForToday'=>$schedForToday]);
                                  

                                    



                                  //---------------------------
                                  // if shift  is between 3am-2:59PM, yung logs nya eh within the day

                              if ($hasApprovedCWS)
                              {
                                if ( ( $schedForToday['timeStart'] >= date('H:i:s',strtotime('03:00:00')) ) && ($schedForToday['timeStart'] <= date('H:i:s',strtotime('14:59:00'))) )
                                  {
                                    $sameDayLog = true;
                                  } else $sameDayLog=false;

                              } else
                              {
                                if ( ( $schedForToday['timeStart'] >= date('H:i:s',strtotime('03:00:00')) ) && ($schedForToday['timeStart'] <= date('H:i:s',strtotime('14:59:00'))) )
                                  {
                                    $sameDayLog = true;
                                  } else $sameDayLog=false;

                              }
                                  



                                if ($sameDayLog)
                                {
                                  $shiftStart = date('h:i A',strtotime($schedForToday['timeStart']));
                                  $shiftEnd = date('h:i A',strtotime($schedForToday['timeEnd']));

                                  $userLogIN = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 1, $schedForToday, $UT,$problemArea);
                                  $userLogOUT = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 2, $schedForToday,0,$problemArea); //$userLogIN[0]['UT']

                                  //$coll->push(['userLogIN'=>$userLogIN, 'userLogOUT'=>$userLogOUT]);

                                  $data = $this->getWorkedHours($userLogIN, $userLogOUT, $schedForToday,$shiftEnd,$payday);

                                  //--- *********************************
                                  //--- We now add in Flexi scheds for Managers
                                  //--- *********************************

                                  // if($schedForToday['isFlexitime'])
                                  // {
                                  //   $workedHours= "Flexi";
                                  //   $billableForOT = "N/A";
                                  //   $OTattribute = null;

                                  // } else
                                  // {
                                    $workedHours= $data[0]['workedHours'];
                                    $billableForOT = $data[0]['billableForOT'];
                                    $OTattribute = $data[0]['OTattribute'];

                                 // }


                                  



                                } //--- end sameDayLog
                                else
                                {


                                    $shiftStart = date('h:i A',strtotime($schedForToday['timeStart']));
                                    $shiftEnd = date('h:i A',strtotime($schedForToday['timeEnd']));
                                    

                                    //*****************************
                                    // we need to setup now cases like Farah
                                    // if !sameDayLog, check muna shiftStart: IF dehadong time, kunin mo yung TIMEIN pang kahapon within shiftStart subHours(5)
                                    //                                        if meron, then ok LOGIN
                                    // if wala, kunin mo login (today within shiftStart & shiftEnd) == LATE SYA
                                    //          IF waley, AWOL
                                    // for the LOGOUT, get log today normally

                                    // if shift is 12MN - 5AM -> PROBLEM AREA
                                    //----------------------------------------
                                    if($isFixedSched)
                                        $isRDYest = $RDsched->contains($prevNumDay); 
                                      else
                                      {
                                        $rd = $monthlySched->where('isRD',1)->where('productionDate',$prevDay->format('Y-m-d'))->first();  
                                        if (empty($rd)) 
                                          $isRDYest=false; else $isRDYest=true;
                                      }


                                    if( date('H:i:s', strtotime($shiftStart)) <= date('H:i:s', strtotime('05:59:00'))) //date('h:i A', strtotime($shiftStart)) >= $d=date('h:i A', strtotime('00:00:00')) && 
                                    {
                                      $isAproblemShift=true;

                                      //*************************************
                                      //-- introduce a new check; IF RD nya kahapon, then get the current sched
                                      //-- Check if RD nya kahapon
                                      //*************************************

                                      

                                      

                                      if ($isRDYest)
                                      {
                                        $userLogIN = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 1, $schedForToday, $UT, $problemArea);

                                      }
                                      else
                                      {

                                          //-- we check first if may logIN sya kahapon
                                          $yesterday = Carbon::parse($payday)->subDay();
                                          $bioForYest = Biometrics::where('productionDate',$yesterday->format('Y-m-d'))->first();
                                                                        

                                          if(!empty($bioForYest))
                                          {
                                            $problemArea = new Collection;
                                            

                                            //$coll->push(['problemArea'=>$problemArea]); 

                                            if($schedForToday['timeStart']!== '00:00:00')
                                            {
                                              $problemArea->push([
                                              'problemShift'=>true, 
                                              'allotedTimeframe'=>Carbon::parse($payday." ".$schedForToday['timeStart'])->subHours(6)->format('Y-m-d H:i:s'),
                                              'biometrics_id'=>$bioForYest->id]);
                                              $userLogIN = $this->getLogDetails('WORK', $id, $bioForYest->id, 1, $schedForToday, $UT, $problemArea);


                                            }
                                            else
                                            {
                                              $problemArea->push([
                                              'problemShift'=>true, 
                                              'allotedTimeframe'=>Carbon::parse($payday." ".$schedForToday['timeStart'])->subHours(6)->format('Y-m-d H:i:s'),
                                              'biometrics_id'=>$bioForTheDay->id]);
                                              $userLogIN = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 1, $schedForToday, $UT, $problemArea); 
                                            }
                                               
                                          }else 
                                          {
                                            //$userLogIN = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 1, $schedForToday, $UT, $problemArea);
                                            $userLogIN[0]= array('logTxt'=> "No Data", 'UT'=>0,'logs'=>null);
                                            
                                          }


                                      }//end RD nya yesterday     
                                      

                                    } else $userLogIN = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 1, $schedForToday, $UT, $problemArea);

                                    

                                          if ($isAproblemShift && !$isRDYest)
                                          {

                                            if($schedForToday['timeStart']!== '00:00:00')
                                            {
                                              $problemArea = new Collection;
                                              $problemArea->push([
                                              'problemShift'=>true, 
                                              'allotedTimeframe'=>Carbon::parse($payday." ".$schedForToday['timeStart'])->subHours(6)->format('Y-m-d H:i:s'),
                                              'biometrics_id'=>$bioForTheDay->id]);
                                              $userLogOUT = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 2, $schedForToday,0, $problemArea);

                                            } else
                                            {
                                              if(empty($bioForTom))
                                              {
                                                $userLogOUT[0]= array('logTxt'=>"<em>No Biometrics Data</em>", 'UT'=>0);
                                                $workedHours= 'N/A';
                                                $billableForOT=0;

                                              } else
                                              {
                                                $problemArea = new Collection;
                                                $problemArea->push([
                                                'problemShift'=>true, 
                                                'allotedTimeframe'=>Carbon::parse($payday." ".$schedForToday['timeStart'])->subHours(6)->format('Y-m-d H:i:s'),
                                                'biometrics_id'=>$bioForTom->id]);
                                                $userLogOUT = $this->getLogDetails('WORK', $id, $bioForTom->id, 2, $schedForToday,0, $problemArea);
                                                $data = $this->getComplicatedWorkedHours($userLogIN, $userLogOUT, $schedForToday,$shiftEnd,$isRDYest,$payday);
                                                $workedHours= $data[0]['workedHours'];
                                                $billableForOT = $data[0]['billableForOT'];
                                                $OTattribute = $data[0]['OTattribute'];
                                              }
                                              
                                            }
                                             
                                            

                                            

                                          }
                                          else
                                          { 
                                            if(empty($bioForTom))
                                              $userLogOUT[0]= array('logTxt'=> "No Data", 'UT'=>0,'logs'=>null);
                                            else
                                              $userLogOUT = $this->getLogDetails('WORK', $id, $bioForTomorrow->id, 2, $schedForToday,0, $problemArea);

                                            if($isRDYest || $isAproblemShift)
                                              $data = $this->getComplicatedWorkedHours($userLogIN, $userLogOUT, $schedForToday,$shiftEnd,$isRDYest,$payday);
                                            else
                                              $data = $this->getWorkedHours($userLogIN, $userLogOUT, $schedForToday,$shiftEnd, $payday);
                                            $workedHours= $data[0]['workedHours'];
                                            $billableForOT = $data[0]['billableForOT'];
                                            $OTattribute = $data[0]['OTattribute'];

                                           

                                          }


                                

                                    //$coll->push(['productionDate'=>$payday, 'schedForToday Flex'=>$schedForToday] );
                                    

                                } //--- else not sameDayLog



                                if(is_null($schedForToday)) //walang saved sched
                                  {
                                    //$coll->push("No sched");
                                    $myDTR->push(['payday'=>$payday,
                                   'biometrics_id'=>$bioForTheDay->id,
                                   'hasCWS'=>$hasCWS,
                                    'usercws'=>$usercws->first(),
                                    'userOT'=>$userOT,
                                    'hasOT'=>$hasOT,
                                   'isRD'=>0,
                                   'isFlexitime'=>$isFlexitime,
                                   'productionDate'=> date('M d, Y', strtotime($payday)),
                                   'day'=> date('D',strtotime($payday)),
                                   'shiftStart'=> null,
                                   'shiftEnd'=>null,
                                   'logIN' => $userLogIN[0]['logTxt'],
                                   'logOUT'=>$userLogOUT[0]['logTxt'],
                                   'workedHours'=> $workedHours,
                                   'billableForOT' => $billableForOT,
                                   'OTattribute'=>$OTattribute,
                                   'UT'=>$UT,
                                   'approvedOT' => $approvedOT]);

                                  } else{
                                    $myDTR->push([ 'payday'=> $payday,
                                      'biometrics_id'=>$bioForTheDay->id,
                                      'hasCWS'=>$hasCWS,
                                      'usercws'=>$usercws->first(),
                                      'userOT'=>$userOT,
                                      'hasOT'=>$hasOT,
                                      'hasApprovedOT'=>$hasApprovedOT,
                                      'isRD'=> 0,
                                      'isFlexitime'=>$schedForToday['isFlexitime'], //$isFlexitime,
                                      'productionDate'=> date('M d, Y', strtotime($payday)),
                                      'hasApprovedCWS'=>$hasApprovedCWS,
                                     'day'=> date('D',strtotime($payday)),
                                     'shiftStart'=> $shiftStart,
                                     'shiftEnd'=>$shiftEnd,
                                     'logIN' => $userLogIN[0]['logTxt'],
                                     'logOUT'=>$userLogOUT[0]['logTxt'],
                                     'workedHours'=> $workedHours,
                                     'billableForOT' => $billableForOT,
                                     'OTattribute'=> $OTattribute,
                                     'UT'=>$userLogOUT[0]['UT'],
                                     'approvedOT' => $approvedOT]);


                                } 




                                



                            }//end else WORK DAY

                            
                          // $coll->push(['productionDate'=>$payday, 'isFlexitime'=>$schedForToday['isFlexitime']] );
                           // $coll->push($myDTR );


                        
                        

                        

                          


                      }//end else not null BioForTheDay
                      //$coll->push(['productionDate'=>$payday, 'schedForToday Flex'=>$schedForToday] );

                }//end if else noWorkSched

                 
                 
     

             }//END foreach payrollPeriod

          
            //return $coll;
            return view('timekeeping.myDTR', compact('canChangeSched', 'paycutoffs', 'shifts','cutoffID', 'myDTR','camps','user','theImmediateHead', 'immediateHead','cutoff','noWorkSched', 'prevTo','prevFrom','nextTo','nextFrom'));


        } else return view('access-denied');

        //}

    	

    }


    public function myDTR()
    {

    	$cutoff = date('M d, Y', strtotime(Cutoff::first()->startingPeriod())). " - " . date('M d, Y',strtotime(Cutoff::first()->endingPeriod()));

        $user = $this->user; //User::find($id); 
        $leadershipcheck = ImmediateHead::where('employeeNumber', $user->employeeNumber)->first();
        $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->supervisor->immediateHead_Campaigns_id)->immediateHead_id);

         if (!empty($leadershipcheck)){ $camps = $leadershipcheck->campaigns->sortBy('name'); } else $camps = $user->campaign;

    	$dtr = $this->user->logs->sortBy('id')->groupBy('biometrics_id');
    	//return $myDTR;
    	$myDTR = new Collection;

    	 foreach ($dtr as $daily) {

    	 	$logIN = $daily->where('logType_id',1)->sortBy('id')->pluck('logTime'); //->get();
    	 	$logOUT = $daily->where('logType_id',2)->sortBy('id')->pluck('logTime'); //->get();

    	 	if (count($logIN) > 0)
    	 	{
    	 		$in = $logIN->first();
    	 		$timeStart = Carbon::parse($in);

    	 	}  else { $in=null; $timeStart=null; }
    	 	if (count($logOUT) > 0)
    	 	{
    	 		$out = $logOUT->first();
    	 		$timeEnd = Carbon::parse($out); 
    	 	} else { $out=null; $timeEnd=null; }

    	 	if ($in !== null && $out !== null)
    	 	{
    	 		//$coll->push(['in'=>$in, 'out'=>$out]);
    	 		$workedHours = $timeEnd->diffInMinutes($timeStart->addHour());
                
    	 	} else $workedHours=null;

    	 	//DB::table('user_dtr')->insert(['user_id'=>$key[0]->user_id, 'timeIN']);
    	 $myDTR->push(['biometrics_id'=>$daily[0]->biometrics_id, 'user_id'=>$daily[0]->user_id, 'Time IN'=> $in, 'Time OUT'=> $out, 'Hours Worked'=> round($workedHours/60,2) ]);
    	 }
    	 //return $myDTR;

    	return view('timekeeping.myDTR', compact('myDTR','camps','user','immediateHead', 'cutoff'));
    }


}
