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





class bak_DTRController extends Controller
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

    public function show($id)
    {
        DB::connection()->disableQueryLog();

      

    	$cutoff = date('M d, Y', strtotime(Cutoff::first()->startingPeriod())). " - " . date('M d, Y',strtotime(Cutoff::first()->endingPeriod()));
    	$user = User::find($id);
        $leadershipcheck = ImmediateHead::where('employeeNumber', $user->employeeNumber)->first();
        $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->supervisor->immediateHead_Campaigns_id)->immediateHead_id);

         if (!empty($leadershipcheck)){ $camps = $leadershipcheck->campaigns->sortBy('name'); } else $camps = $user->campaign;



         // ---------------------------
         // Generate cutoff period
         //----------------------------

         $payrollPeriod = [];
         $cutoffStart = new Carbon(Cutoff::first()->startingPeriod());
         $cutoffEnd = new Carbon(Cutoff::first()->endingPeriod());
         $noWorkSched = false;

         //Timekeeping Trait
         $payrollPeriod = $this->getPayrollPeriod($cutoffStart,$cutoffEnd);

         

         // ---------------------------
         // Determine first if FIXED OR SHIFTING sched
         // and then get WORKSCHED and RD sched
         // ---------------------------
          

         if (count($user->monthlySchedules) > 0)
         {
            $monthlySched = MonthlySchedules::where('productionDate','>=', $cutoffStart->format('Y-m-d'))->where('productionDate','<=',$cutoffEnd->format('Y-m-d'))->get();
            $workSched = $monthlySched->where('isRS','0')->all();
            $RDsched = $monthlySched->where('isRS','1')->all();
            $isFixedSched = false;

         } else
         {
            if (count($user->fixedSchedule) > 0)
            {
                //merong fixed sched
                $workSched = $user->fixedSchedule->where('isRD',0);
                $RDsched = $user->fixedSchedule->where('isRD',1)->pluck('workday');
                $isFixedSched =true;
            } else
            {
                $noWorkSched = true;
                $isFixedSched = false;
            }
         }


         // ---------------------------  INITIALIZATIONS
         $myDTR = new Collection;
         $daysOfWeek = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun'); // for Gregorian cal. Iba kasi jddayofweek sa PHP day
         $coll = new Collection; $nightShift=""; $panggabi=""; $approvedOT=0; $billableForOT=0; $UT=0;



         // ---------------------------
         // Start Payroll generation
         // ---------------------------

         foreach ($payrollPeriod as $payday) 
         {
            $bioForTheDay = Biometrics::where('productionDate',$payday)->first();

            if( is_null($bioForTheDay) )
            {
                    $logIN = "<strong class=\"text-success\">No <br/>Biometrics</strong>";
                    $logOUT = "<strong class=\"text-success\">No <br/>Biometrics</strong>";
                    $workedHours = 'N/A';
                    

            } else
            {

              // if($isFixedSched)
              // {
                  
                //---------- gawin mo lang to for Fixed scheds
                if ($isFixedSched)
                {
                  $day = date('D', strtotime($payday)); //--- get his worksched and RDsched
                  $theday = (string)$day;
                  $numDay = array_search($theday, $daysOfWeek);
                  $prevNumDay = array_search($theday-1, $daysOfWeek);

                }
                
                //---------- gawin mo lang to for Fixed scheds




                
                $UT = 0;
                
                //--- Check if RD nya today
                
                $isRDToday = $RDsched->contains($numDay); 
                if ($isRDToday)
                {
                    //--- check muna kung may Log In sya today, meaning pumasok sya kahit RD
                     $userLogIN = Logs::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('logType_id',1)->orderBy('biometrics_id','ASC')->get();
                     if (is_null($userLogIN))
                     {  
                        //--- di nga sya pumasok
                        $logIN = "* RD *";
                        $logOUT = "* RD *";
                        $shiftStart = "* RD *";
                        $shiftEnd = "* RD *";
                        $workedHours = "N/A";
                        $UT = 0;
                     } else
                     {
                        $logIN = date('h:i A',strtotime($userLogIN->first()->logTime));
                        $timeStart = Carbon::parse($userLogIN->first()->logTime);

                        //--- RD OT, but check first if VALID. It should have a LogOUT AND approved OT
                        $userLogOUT = Logs::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('logType_id',2)->orderBy('biometrics_id','ASC')->get();

                        //--- ** May issue: pano kung RD OT ng gabi, then kinabukasan na sya nag LogOUT. Need to check kung may approved OT from IH
                        if( is_null($userLogOUT) )
                        {
                            $logOUT = "No OT-Out <br/><small>Verify with Immediate Head</small>";
                            $workedHours = "N/A";
                            $shiftStart = "* RD *";
                            $shiftEnd = "* RD *";
                            $UT = 0;
                            

                        } else 
                        { 
                              //--- legit OT, compute billable hours
                              $logOUT = date('h:i A',strtotime($userLogOUT->first()->logTime));
                              $timeEnd = Carbon::parse($userLogOUT->first()->logTime);
                              $wh = $timeEnd->diffInMinutes($timeStart); //--- pag RD OT, no need to add breaktime 1HR
                              $workedHours = number_format($wh/60,2);
                              $billableForOT = $workedHours;
                              $shiftStart = "* RD *";
                              $shiftEnd = "* RD *";
                              $UT = 0;
                        }



                     }//end if may login kahit RD
                }//end if isRDToday
                else 
                {
                      //--- it's not RD, its a WORKDAY

                      //---------------------------
                      // We need to establish details ng schedule for today
                      // if may issue ba sa sched nya: [12Mn start], [mga 3PM onwards na next day logout]
                      //---------------------------


                      // ** SCHEDFORTODAY (fixed) =    [workDay | timeStart | timeEnd | isFlexi | isRD ]     
                      // ** SCHEDFORTODAY (shifting) = [productionDate | timeStart | timeEnd | isFlexi | isRD ]
                      
                      //dd($workSched);

                      if ($isFixedSched) 
                        $schedForToday = $workSched->where('workday',$numDay)->first();
                      else 
                        $schedForToday = $workSched->where('productionDate',$payday)->first();



                      //---------------------------
                      // if shift  is between 3am-2:59PM, yung logs nya eh within the day
                      if ( ( $schedForToday->timeStart >= date('H:i:s',strtotime('03:00:00')) ) && ($schedForToday->timeStart <= date('H:i:s',strtotime('14:59:00'))) )
                      {
                        $sameDayLog = true;
                      } else $sameDayLog=false;





                    if ($sameDayLog)
                    {
                        $shiftStart = date('h:i A',strtotime($schedForToday['timeStart']));
                        $shiftEnd = date('h:i A',strtotime($schedForToday['timeEnd']));

                        $userLogIN = Logs::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('logType_id',1)->orderBy('biometrics_id','ASC')->get();


                         if (is_null($userLogIN))
                         {  
                            
                            $logIN = "No In";
                            $workedHours = "N/A";
                         } else
                         {
                            $logIN = date('h:i A',strtotime($userLogIN->first()->logTime));
                            $timeStart = Carbon::parse($userLogIN->first()->logTime);

                            if (Carbon::parse($schedForToday['timeStart']) < $timeStart) //--- meaning late sya
                            {
                              //$UT  = number_format((Carbon::parse($schedForToday['timeStart'])->diffInMinutes($timeStart))/60,2);
                              $UT  = number_format((Carbon::parse($schedForToday['timeStart'])->diffInMinutes($timeStart))/60,2);

                            }
                         }//end if may login 

                            
                          $userLogOUT = Logs::where('user_id',$id)->where('biometrics_id',$bioForTheDay->id)->where('logType_id',2)->orderBy('biometrics_id','ASC')->get();

                            //--- ** May issue: pano kung RD OT ng gabi, then kinabukasan na sya nag LogOUT. Need to check kung may approved OT from IH
                            if( is_null($userLogOUT) )
                            {
                                $logOUT = "No Out";
                                $workedHours = "N/A";
                            } else 
                            { 
                                  //--- legit logs
                                  $logOUT = date('h:i A',strtotime($userLogOUT->first()->logTime));
                                  $timeEnd = Carbon::parse($userLogOUT->first()->logTime);

                                  if (Carbon::parse($schedForToday['timeEnd']) > $timeEnd) //--- meaning undertime sya
                                  {
                                    $UT  += number_format((Carbon::parse($schedForToday['timeEnd'])->diffInMinutes($timeEnd))/60,2);

                                  }

                            }

                            if (!is_null($userLogIN) && !is_null($userLogOUT))
                            {
                              $wh = $timeEnd->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour()); //--- Need to add 1HR breaktime
                              if ($wh > 480){ $workedHours = 8.0; $billableForOT = number_format((Carbon::parse($shiftEnd)->diffInMinutes($timeEnd))/60,2);  }
                                  else { $workedHours = number_format($wh/60,2); }
                            } 
                            else
                            {
                              $workedHours = "<strong class=\"text-danger\">AWOL</strong>";
                            }


                    } //--- end sameDayLog
                    else
                    {


                    } //--- else not sameDayLog


                       $coll->push(['userLogIN'=>$userLogIN, 'userLogOUT'=>$userLogOUT, 'wh'=> $wh]);
                     

                    


                }//end else WORK DAY

                

                //$coll->push(['RD'=>$RDsched, 'numDay'=>$numDay, 'RDforToday'=>$RDschedForToday, 'WorkForToday'=>$schedForToday]);

              // } //end if FIXED SCHED else
              // else
              // {




              // }//end if isFixedSched()

            }//end else not null BioForTheDay



            $myDTR->push(['productionDate'=> date('M d, Y', strtotime($payday)),
                 'day'=> date('D',strtotime($payday)),
                 //'numDay'=>$numDay,
                 //'RD'=> $isRD,
                 'shiftStart'=> $shiftStart,
                 'shiftEnd'=>$shiftEnd,
                 // 'nightShift'=> $nightShift,
                 // 'panggabi'=>$panggabi,
                 'logIN' => $logIN,
                 'logOUT'=>$logOUT,
                 'workedHours'=> $workedHours,
                 'billableForOT' => $billableForOT,
                 'UT'=>$UT,
                 'approvedOT' => $approvedOT]);



 

         }//END foreach payrollPeriod

         //return $coll;





         

         // ***************** 2 way of processing DTR, check first if fixed or changing sched


        
        //return $myDTR;

//return $coll;
         //return $workSched;
         
      

         //----------------------------OLD
      //   	$dtr = $user->logs->sortBy('id')->groupBy('biometrics_id');
	    	//return $myDTR;
	    	

	    	//  foreach ($dtr as $daily) {

	    	//  	$logIN = $daily->where('logType_id',1)->sortBy('id')->pluck('logTime'); //->get();
	    	//  	$logOUT = $daily->where('logType_id',2)->sortBy('id')->pluck('logTime'); //->get();

	    	//  	if (count($logIN) > 0)
	    	//  	{
	    	//  		$in = $logIN->first();
	    	//  		$timeStart = Carbon::parse($in);

	    	//  	}  else { $in=null; $timeStart=null; }
	    	//  	if (count($logOUT) > 0)
	    	//  	{
	    	//  		$out = $logOUT->first();
	    	//  		$timeEnd = Carbon::parse($out); 
	    	//  	} else { $out=null; $timeEnd=null; }

	    	//  	if ($in !== null && $out !== null)
	    	//  	{
	    	//  		//$coll->push(['in'=>$in, 'out'=>$out]);
	    	//  		$workedHours = $timeEnd->diffInMinutes($timeStart->addHour());
	                
	    	//  	} else $workedHours=null;

	    	//  	//DB::table('user_dtr')->insert(['user_id'=>$key[0]->user_id, 'timeIN']);
	    	//  $myDTR->push(['biometrics_id'=>$daily[0]->biometrics_id, 'user_id'=>$daily[0]->user_id, 'Time IN'=> $in, 'Time OUT'=> $out, 'Hours Worked'=> round($workedHours/60,2) ]);
	    	//  }
	    	 //----------------------------

	    	return view('timekeeping.myDTR', compact('myDTR','camps','user','immediateHead','cutoff','noWorkSched'));

        //}

    	

    }


    public function myDTR()
    {
        
        /* ---- testing purposes 

        $tl = User::find(512);
        $ih = ImmediateHead::where('employeeNumber',$tl->employeeNumber)->first();
        $team = ImmediateHead_Campaign::where('immediateHead_id',$ih->id)->where('campaign_id',16)->first();

        $members = DB::table('team')->where('ImmediateHead_Campaigns_id','=',$team->id)->leftJoin('users','team.user_id','=','users.id')->where('users.employeeNumber', '!=',$ih->employeeNumber)
                                ->where('users.status_id','!=','7')->where('users.status_id','!=','8')->where('users.status_id','!=',9)
                                ->select('users.id','users.lastname','users.firstname','users.middlename')->orderBy('lastname')->get();


        $coll = new Collection;

        foreach ($members as $key) {
            if (count(User::find($key->id)->monthlySchedule) !== 0)
                $coll->push(User::find($key->id)->monthlySchedule);
        }
        return $coll;
         ---------------- End testing purposes */

    	//$roles = UserType::find($this->user->userType_id)->roles->pluck('label'); //->where('label','MOVE_EMPLOYEES');
        //$canMoveEmployees =  ($roles->contains('MOVE_EMPLOYEE')) ? '1':'0';
        //$canEditEmployees =  ($roles->contains('EDIT_EMPLOYEE')) ? '1':'0';

        //$hrDept = Campaign::where('name',"HR")->first();

       // return $this->paycutoff->startingPeriod(). " - " . $this->paycutoff->endingPeriod();

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
