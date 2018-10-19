<?php

namespace OAMPI_Eval\Http\Controllers\Traits;

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
use OAMPI_Eval\User_OT;
use OAMPI_Eval\Holiday;
use OAMPI_Eval\HolidayType;

trait TimekeepingTraits
{

  public function generateShifts($timeFormat)
  {
    $data = array();

    for( $i = 1; $i <= 24; $i++)
    { 
      $time1 = Carbon::parse('1999-01-01 '.$i.':00:00');
      $time2 = Carbon::parse('1999-01-01 '.$i.':30:00');

      if($timeFormat == '12H')
      {
        array_push($data, $time1->format('h:i A')." - ".$time1->addHours(9)->format('h:i A'));
        array_push($data, $time2->format('h:i A')." - ".$time2->addHours(9)->format('h:i A'));

      } else
      {
        array_push($data, $time1->format('H:i')." - ".$time1->addHours(9)->format('H:i'));
        array_push($data, $time2->format('H:i')." - ".$time2->addHours(9)->format('H:i'));
      }
    }
    return $data;


  }


  public function checkIfUndertime($type, $userLog, $schedForToday)
  {
    switch ($type) {
      case 'IN':
            if ($userLog > $schedForToday) return true; else return false;
        
        break;
      
      case 'OUT':
            if ($userLog < $schedForToday) return true; else return false;
        break;
    }
  }


  public function getPayrollPeriod($cutoffStart,$cutoffEnd)
  {
    
    for($date = $cutoffStart; $date->lte($cutoffEnd); $date->addDay()) 
         {
            $payrollPeriod[] = $date->format('Y-m-d');
         }

    return $payrollPeriod;


  }

  public function getLogDetails($type, $id, $biometrics_id, $logType_id, $schedForToday, $undertime, $problemArea)
  {


    $data = new Collection;
    $hasHolidayToday = false;
    $thisPayrollDate = Biometrics::find($biometrics_id)->productionDate;
    $holidayToday = Holiday::where('holidate', $thisPayrollDate)->get();

    if (count($holidayToday) > 0) $hasHolidayToday = true;

      if ($problemArea[0]['problemShift'])
      {
        //kunin mo biometrics from kahapon
        //$userLog = Logs::where('user_id',$id)->where('biometrics_id',$problemArea[0]['biometrics_id'])->where('logType_id',$logType_id)->where('logTime','>=',date('H:i:s',strtotime($problemArea[0]['allotedTimeframe'])))->orderBy('biometrics_id','ASC')->get();
        
        //if($logType_id !== 2) //hack for 12AM logs
          $userLog = Logs::where('user_id',$id)->where('biometrics_id',$problemArea[0]['biometrics_id'])->where('logType_id',$logType_id)->orderBy('biometrics_id','ASC')->get();
       // else
        //  $userLog = Logs::where('user_id',$id)->where('biometrics_id',$biometrics_id)->where('logType_id',$logType_id)->orderBy('biometrics_id','ASC')->get();


      }else
      {
        $userLog = Logs::where('user_id',$id)->where('biometrics_id',$biometrics_id)->where('logType_id',$logType_id)->orderBy('biometrics_id','ASC')->get();

      } 

      if (is_null($userLog) || count($userLog)<1)
      {  
        $link = action('LogsController@viewRawBiometricsData',$id);
         $icons = "<a title=\"Verify Biometrics data\" class=\"pull-right text-danger\" target=\"_blank\" style=\"font-size:1.2em;\" href=\"$link#$biometrics_id\"><i class=\"fa fa-clock-o\"></i></a>";
          
          
         if ($hasHolidayToday)
         {
          $log = "<strong class=\"text-danger\">N/A</strong>". $icons;
          $workedHours = $holidayToday->first()->name;

         } else 
         {

          if($logType_id == 1) $log =  "<strong class=\"text-danger\">No IN</strong>". $icons;
          else if ($logType_id == 2)$log = "<strong class=\"text-danger\">No OUT</strong>". $icons;
          $workedHours = "N/A";

         }
          
          $timing=Carbon::parse('22:22:22');
          $UT = $undertime;
      } 
      else
      {
          
          $log = date('h:i:s A',strtotime($userLog->first()->logTime));

          if ($problemArea[0]['problemShift'])
            $timing = Carbon::parse(Biometrics::find($userLog->first()->biometrics_id)->productionDate." ".$userLog->first()->logTime);
          else
            $timing = Carbon::parse($userLog->first()->logTime);

          $timing2 = $userLog->first()->logTime;

          //*********** APPLICABLE ONLY TO WORK DAY ********************//

          if ($logType_id == 1) 
          {
            $parseThis = $schedForToday['timeStart'];
            if ( (Carbon::parse($parseThis) < $timing)  && !$problemArea[0]['problemShift']) //--- meaning late sya
              {
                $UT  = $undertime + number_format((Carbon::parse($parseThis)->diffInMinutes($timing))/60,2);

              } else $UT = 0;
          }
            
          else if ($logType_id == 2)
            $parseThis = $schedForToday['timeEnd'];
            if (Carbon::parse($parseThis) > $timing && !$problemArea[0]['problemShift']) //--- meaning early out sya
              {
                $UT  = $undertime + number_format((Carbon::parse($parseThis)->diffInMinutes($timing))/60,2);

              } else $UT=$undertime;

          
          //*********** APPLICABLE ONLY TO WORK DAY ********************//

       }//end if may login 

       $data->push(['logs'=>$userLog, 'UT'=>$UT, 'logTxt'=>$log, 'timing'=>$timing]);

              

      return $data;
  }


  public function getRDinfo($user_id, $biometrics,$isSameDayLog,$payday)
  {

    /* init $approvedOT */
      $approvedOT=0; $OTattribute="";
      $hasHolidayToday = false;
    $thisPayrollDate = Biometrics::find($biometrics->id)->productionDate;
    $holidayToday = Holiday::where('holidate', $thisPayrollDate)->get();

     if (count($holidayToday) > 0) $hasHolidayToday = true;


      /* -- you still have to create module for checking and filing OTs */

       $userLogIN = Logs::where('user_id',$user_id)->where('biometrics_id',$biometrics->id)->where('logType_id',1)->orderBy('biometrics_id','ASC')->get();
       if (count($userLogIN) == 0)
       {  
          //--- di nga sya pumasok
          $logIN = "* RD *";
          $logOUT = "* RD *";
          $shiftStart = "* RD *";
          $shiftEnd = "* RD *";

          if ($hasHolidayToday)
          {
            $workedHours = "N/A <br/> "; 
            $workedHours .= $holidayToday->first()->name;

          } else $workedHours="N/A"; 
          $UT = 0;
          $billableForOT=0;
       } else
       {
          $logIN = date('h:i A',strtotime($userLogIN->first()->logTime));
          $timeStart = Carbon::parse($userLogIN->first()->logTime);

          if ($isSameDayLog)
              $userLogOUT = Logs::where('user_id',$user_id)->where('biometrics_id',$biometrics->id)->where('logType_id',2)->orderBy('biometrics_id','ASC')->get();
          else
          {
            //--- NEXT DAY LOG OUT
            $nextDay = Carbon::parse($payday)->addDay();
            $bioForTomorrow = Biometrics::where('productionDate',$nextDay->format('Y-m-d'))->first();
                $userLogOUT = Logs::where('user_id',$user_id)->where('biometrics_id',$bioForTomorrow->id)->where('logType_id',2)->orderBy('biometrics_id','ASC')->get();

          }
            


          //--- ** May issue: pano kung RD OT ng gabi, then kinabukasan na sya nag LogOUT. Need to check kung may approved OT from IH
          $rdOT = User_OT::where('biometrics_id',$biometrics->id)->where('user_id',$user_id)->where('isApproved',1)->get();
          if (count($rdOT) > 0) $approvedOT = $rdOT; 

          if( is_null($userLogOUT) || count($userLogOUT)<1 )
          {
              $logOUT = "No OT-Out <br/><small>Verify with Immediate Head</small>";

              $workedHours="N/A"; 

              if ($hasHolidayToday)
              {
                
                $workedHours .= "<br />" . $holidayToday->first()->name;

              }  

             
              $shiftStart = "* RD *";
              $shiftEnd = "* RD *";
              $UT = 0;
              $billableForOT=0;
              

          } else 
          { 
                //--- legit OT, compute billable hours
                $logOUT = date('h:i A',strtotime($userLogOUT->first()->logTime));
                $timeEnd = Carbon::parse($userLogOUT->first()->logTime);
                $wh = $timeEnd->diffInMinutes($timeStart); //--- pag RD OT, no need to add breaktime 1HR
                $workedHours = number_format($wh/60,2);
                if ($hasHolidayToday)
                {
                  
                  $workedHours .= "<br /><strong>" . $holidayToday->first()->name . "</strong>";

                } 


                 if ($hasHolidayToday)
                 $icons = "<a  data-toggle=\"modal\" data-target=\"#myModal_OT".$payday."\"  title=\"File this Overtime\" class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
               else
                $icons = "<a  data-toggle=\"modal\" data-target=\"#myModal_OT".$payday."\"  title=\"File this Holiday Overtime\" class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
               

                $billableForOT = $workedHours;
                $OTattribute = $icons;
                $shiftStart = "* RD *";
                $shiftEnd = "* RD *";
                $UT = 0;
          }



       }//end if may login kahit RD

       $data = new Collection;
       $data->push(['shiftStart'=>$shiftStart, 
        'shiftEnd'=>$shiftEnd, 'logIN'=>$logIN, 
        'logOUT'=>$logOUT,'workedHours'=>$workedHours, 
        'billableForOT'=>$billableForOT, 'OTattribute'=>$OTattribute, 'UT'=>$UT, 
        'approvedOT'=>$approvedOT]);
       return $data;


   
  }

  public function getWorkedHours($user_id, $userLogIN, $userLogOUT, $schedForToday,$shiftEnd, $payday)
  {

    $data = new Collection;
    $billableForOT=0;
    $OTattribute = "";
    $campName = User::find($user_id)->campaign->first()->name;

    $hasHolidayToday = false;
    //$thisPayrollDate = Biometrics::where(find($biometrics->id)->productionDate;
    $holidayToday = Holiday::where('holidate', $payday)->get();

     if (count($holidayToday) > 0) $hasHolidayToday = true;



        if (count($userLogIN[0]['logs']) > 0 && count($userLogOUT[0]['logs']) > 0)
        {
          //---- To get the right Worked Hours, check kung early pasok == get schedule Time
          //---- if late pumasok, get user timeIN


          //************ CHECK FOR LATEIN AND EARLY OUT ***************//

          // $checkLate = $userLogIN[0]['timing']->diffInMinutes(Carbon::parse($schedForToday['timeStart']));
          // if ($checkLate > 1) $isLateIN = true; else $isLateIN= false;

          // $checkEarlyOut = $userLogOUT[0]['timing']->diffInMinutes(Carbon::parse($schedForToday['timeEnd']));
          // if ($checkEarlyOut > 1)  $isEarlyOUT = true; else $isEarlyOUT= false;

          if ($userLogIN[0]['timing']->format('H:i:s') > $schedForToday['timeStart'])
          {
            $checkLate = $userLogIN[0]['timing']->diffInMinutes(Carbon::parse($schedForToday['timeStart']));
            //---- MARKETING TEAM CHECK: 15mins grace period


            // if ($hasHolidayToday)
            // {
            //   $isLateIN = false;
            // }
            // else
            // {

              if( $campName == "Marketing" || $campName == "Lebua")
              {
                 if ($checkLate > 15) $isLateIN = true; else $isLateIN= false;

              } else
              {
                 if ($checkLate > 1) $isLateIN = true; else $isLateIN= false;
              }


            //}
              
              
            
          } else $isLateIN= false;


          if ($userLogOUT[0]['timing']->format('H:i:s') < $schedForToday['timeEnd'])
          {
            $checkEarlyOut = $userLogOUT[0]['timing']->diffInMinutes(Carbon::parse($schedForToday['timeEnd']));

            // if ($hasHolidayToday)
            // {
            //   $isEarlyOUT = false;
            // }
            // else
            //---- MARKETING TEAM CHECK: 15mins grace period
              if( $campName == "Marketing" || $campName == "Lebua")
              {
                 if ($checkEarlyOut > 15) $isEarlyOUT = true; else $isEarlyOUT= false;

              } else
              {
                 if ($checkEarlyOut > 1) $isEarlyOUT = true; else $isEarlyOUT= false;
              }

            
          } else $isEarlyOUT= false;

        

          if ($isEarlyOUT && $isLateIN)//use user's logs
          {

            $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
            $workedHours = number_format($wh/60,2);
            $billableForOT=0; //$userLogIN[0]['timing']/60;
            

          }
          else if ($isEarlyOUT){

            //--- but u need to make sure if nag late out sya
            if (Carbon::parse($userLogOUT[0]['timing']) > Carbon::parse($schedForToday['timeEnd']))
            {
              $workedHours = 8.00;

              $icons = "<a title=\"File this Overtime\" class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
               $totalbill = number_format((Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($userLogOUT[0]['timing']) ))/60,2);
              $totalbill = number_format((Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($userLogOUT[0]['timing']) ))/60,2);

              if ($totalbill > 0.5){
                $billableForOT = $totalbill;
                $OTattribute = $icons;
              }
                
              else
              {
                $billableForOT = $totalbill;
                $OTattribute = "&nbsp;&nbsp;&nbsp;";
              } 
            }
              
            else
            {
              $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(early OUT)</small>";$billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }

            }

            
          }
          else if ($isLateIN){

            //--- but u need to make sure if nag late out sya
            if (Carbon::parse($userLogOUT[0]['timing']) > Carbon::parse($schedForToday['timeEnd']))
            {
             $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(Late IN)</small>";$billableForOT=0;
              if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
              
              $icons = "<a  data-toggle=\"modal\" data-target=\"#myModal_OT".$payday."\"   title=\"File this Overtime\" class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
               $totalbill = number_format((Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($userLogOUT[0]['timing']) ))/60,2);
              $totalbill = number_format((Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($userLogOUT[0]['timing']) ))/60,2);

              if ($totalbill > 0.5)
              {
                $billableForOT = $totalbill;
                $OTattribute = $icons;
              }
                
              else { $billableForOT = $totalbill; $OTattribute = "&nbsp;&nbsp;&nbsp;";} 

            }
            else
            {
              $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(Late IN)</small>";$billableForOT=0;
              if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }

            }
            
          }
          else {

             $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour());

            if ($wh > 480)
            {
              $workedHours =8.00; 
               $icons = "<a  data-toggle=\"modal\" data-target=\"#myModal_OT".$payday."\"  title=\"File this overtime\" class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
              $totalbill = number_format((Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($userLogOUT[0]['timing']) ))/60,2);

              if ($totalbill > 0.5)
              {
                $billableForOT = $totalbill;
                $OTattribute = $icons;
              }
                
              else { $billableForOT = $totalbill; $OTattribute= "&nbsp;&nbsp;&nbsp;";} 


            } 
            else 
              { 
                $workedHours = number_format($wh/60,2); $billableForOT=0; 
                  if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
              }
            

          }
          
         
        } 
        else
        {
          if ($hasHolidayToday)
          {
            $workedHours = "(8.0)<br/> <strong>* " . $holidayToday->first()->name . " *</strong>";
          } 
          else
            $workedHours = "<a title=\"Check your Biometrics data. \n It's possible that you pressed a wrong button, or you really didn't log in / out.\"><strong class=\"text-danger\">AWOL</strong></a>";
        }

        
        $data->push(['workedHours'=>$workedHours, 'billableForOT'=>$billableForOT, 'OTattribute'=>$OTattribute]);

        return $data;


  }

  public function getComplicatedWorkedHours($user_id, $userLogIN, $userLogOUT, $schedForToday,$shiftEnd,$isRDYest,$payday)
  {

    $data = new Collection;
    $billableForOT=0; $endshift = Carbon::parse($shiftEnd); $diff = null; $OTattribute="";
    $campName = User::find($user_id)->campaign->first()->name;

    $hasHolidayToday = false;
    //$thisPayrollDate = Biometrics::where(find($biometrics->id)->productionDate;
    $holidayToday = Holiday::where('holidate', $payday)->get();

     if (count($holidayToday) > 0) $hasHolidayToday = true;


        if (count($userLogIN[0]['logs']) > 0 && count($userLogOUT[0]['logs']) > 0)
        {
          //---- To get the right Worked Hours, check kung early pasok == get schedule Time
          //---- if late pumasok, get user timeIN


          //************ CHECK FOR LATEIN AND EARLY OUT ***************//

          if ($isRDYest)
          {

            $todayStart = Carbon::parse($payday." ".$schedForToday['timeStart']); //->format('Y-m-d H:i:s');
            $todayEnd = Carbon::parse($payday." ".$schedForToday['timeEnd'])->addDay(); //->format('Y-m-d H:i:s');
            $actualIN = Carbon::parse($userLogIN[0]['timing']); //->format('Y-m-d H:i:s');
            $actualOUT = Carbon::parse($userLogOUT[0]['timing']); //->format('Y-m-d H:i:s');

            if ($actualIN > $todayStart && $actualIN < $todayEnd)
            {
              $checkLate = $actualIN->diffInMinutes($todayStart);
              
               //---- MARKETING TEAM CHECK: 15mins grace period
              
              if( $campName == "Marketing" || $campName == "Lebua")
              {
                 if ($checkLate > 15) $isLateIN = true; else $isLateIN= false;

              } else
              {
                 if ($checkLate > 1) $isLateIN = true; else $isLateIN= false;
              }

            } else $isLateIN=false;


            if ($actualOUT > $todayStart && $actualOUT < $todayEnd)
            {
              $checkEarlyOut = $actualOUT->diffInMinutes($todayEnd);

               //---- MARKETING TEAM CHECK: 15mins grace period
              if( $campName == "Marketing" || $campName == "Lebua")
              {
                 if ($checkEarlyOut > 15) $isEarlyOUT = true; else $isEarlyOUT= false;

              } else
              {
                 if ($checkEarlyOut > 1) $isEarlyOUT = true; else $isEarlyOUT= false;
              }

              
            } else $isEarlyOUT=false;

            // if ($userLogIN[0]['timing']->format('H:i:s') > $schedForToday['timeStart'] && $userLogIN[0]['timing']->format('H:i:s') > $schedForToday['timeEnd'] )  $isLateIN = false; else $isLateIN= true;
            // if ($userLogOUT[0]['timing']->format('H:i:s') < $schedForToday['timeEnd'])  $isEarlyOUT = true; else $isEarlyOUT= false;

          

            if ($isEarlyOUT && $isLateIN)//use user's logs
            {

              $wh = $actualOUT->diffInMinutes($actualIN->addHour());
              $workedHours = number_format($wh/60,2);
              $billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
              

            }
            else if ($isEarlyOUT){
              $wh = $actualOUT->diffInMinutes($todayStart->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(early OUT)</small>";$billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
            }
            else if ($isLateIN){
              $wh = $actualOUT->diffInMinutes($actualIN->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(Late IN)</small>";$billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
            }
            else {

               $wh = $actualOUT->diffInMinutes($todayStart->addHour());
                $out = Carbon::parse($userLogOUT[0]['timing'])->format('H:i:s');
               $out2 = Carbon::parse($out);


              if ($wh > 480)
              {
                $workedHours =8.00; 
                $icons = "<a data-toggle=\"modal\" data-target=\"#myModal_OT".$payday."\" title=\"File this overtime\"  class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
                $totalbill = number_format(($endshift->diffInMinutes($out2))/60,2);

                if ($totalbill > 0.5)
                {
                  $billableForOT = $totalbill; $OTattribute=$icons;
                }
                  
                else { $billableForOT = $totalbill; $OTattribute="&nbsp;&nbsp;&nbsp;"; } 

              } 
              else 
                { 
                  $workedHours = number_format($wh/60,2); $billableForOT=0; 
                   if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
                }



              

            }





          }
          else
          {

            $actualIN = Carbon::parse($userLogIN[0]['timing']); //->format('Y-m-d H:i:s');
            $actualOUT = Carbon::parse($userLogOUT[0]['timing']); //->format('Y-m-d H:i:s');
            $todayStart = Carbon::parse($payday." ".$schedForToday['timeStart']); //->format('Y-m-d H:i:s');
            $todayEnd = Carbon::parse($payday." ".$schedForToday['timeEnd']); //->format('Y-m-d H:i:s');

            if ($userLogIN[0]['timing']->format('H:i:s') > $schedForToday['timeStart'] && $userLogIN[0]['timing']->format('H:i:s') > $schedForToday['timeEnd'] )
            {

              $checkLate = $actualIN->diffInMinutes($todayStart);

              //---- MARKETING TEAM CHECK: 15mins grace period
              
              if( $campName == "Marketing" || $campName == "Lebua")
              {
                 if ($checkLate > 15) $isLateIN = true; else $isLateIN= false;

              } else
              {
                 if ($checkLate > 1) $isLateIN = true; else $isLateIN= false;
              }
             

            }else $isLateIN= false;

            if ($userLogOUT[0]['timing']->format('H:i:s') < $schedForToday['timeEnd']) 
            {
              $checkEarlyOut = $actualOUT->diffInMinutes($todayEnd);

               //---- MARKETING TEAM CHECK: 15mins grace period
              if( $campName == "Marketing" || $campName == "Lebua")
              {
                 if ($checkEarlyOut > 15) $isEarlyOUT = true; else $isEarlyOUT= false;

              } else
              {
                 if ($checkEarlyOut > 1) $isEarlyOUT = true; else $isEarlyOUT= false;
              }



            } else $isEarlyOUT= false;

          

            if ($isEarlyOUT && $isLateIN)//use user's logs
            {

              $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
              $workedHours = number_format($wh/60,2);
              $billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
              

            }
            else if ($isEarlyOUT){
              $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(early OUT)</small>";$billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
            }
            else if ($isLateIN){
              $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
              $workedHours = number_format($wh/60,2)."<br/><small>(Late IN)</small>";$billableForOT=0;
               if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
            }
            else {

               $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour());
              
               $out = Carbon::parse($userLogOUT[0]['timing'])->format('H:i:s');
               $out2 = Carbon::parse($out);

              if ($wh > 480)
              { 
                $workedHours =8.00; 
                $icons = "<a data-toggle=\"modal\" data-target=\"#myModal_OT".$payday."\"  title=\"File this Overtime\" class=\"pull-right\" style=\"font-size:1.2em;\" href=\"#\"><i class=\"fa fa-credit-card\"></i></a>";
                $totalbill = number_format(($endshift->diffInMinutes($out2))/60,2);

                if ($totalbill > 0.5)
                {
                  $billableForOT = $totalbill; $OTattribute=$icons;
                }  else { $billableForOT = $totalbill; $OTattribute="&nbsp;&nbsp;&nbsp;";} 

                 if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }

              } //number_format(($endshift->diffInMinutes($out2))/60,2);}
              else 
                { $workedHours = number_format($wh/60,2); $billableForOT=0; 
                   if ($hasHolidayToday)
                  {
                    $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
                  }
              }
              

            }


          }//end not RD yesterday

          
          
         
        } 
        else
        {
          $workedHours = "<a title=\"Check your Biometrics data. It's possible that you pressed a wrong button, or you really didn't log in / out.\"><strong class=\"text-danger\">AWOL</strong></a>";
        }

        if ($hasHolidayToday)
        {
          $workedHours .= "<br/> <strong>* ". $holidayToday->first()->name. " *</strong>";
        }

        $data->push(['payday'=>$payday, 'workedHours'=>$workedHours, 'billableForOT'=>$billableForOT, 'OTattribute'=>$OTattribute 
          // 'actualIN'=>$actualIN, 'actualOUT'=>$actualOUT, 
          // 'todayStart'=>$todayStart,'todayEnd'=>$todayEnd,
          // 'isEarlyOUT'=>$isEarlyOUT,'isLateIN'=>$isLateIN,
          // 'diffIN'=>$actualIN->diffInHours($actualOUT),
          ]);

        return $data;


  }









}//end trait




                                
/*
        $shiftStart = date('h:i A',strtotime($schedForToday['timeStart']));
        $shiftEnd = date('h:i A',strtotime($schedForToday['timeEnd']));



        //*************** TRAIT *******************
        // getLogDetails ( dayType[RD|work] , user_id, biometrics_id, logType, $schedForToday)
        // returns =>  $data->push(['logs'=>$userLog, 'UT'=>$UT, 'logTxt'=>$log, 'timing'=>$timing]);
        $userLogIN = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 1, $schedForToday, $UT);
        $userLogOUT = $this->getLogDetails('WORK', $id, $bioForTheDay->id, 2, $schedForToday,0); //$userLogIN[0]['UT']

        $coll->push(['payday'=>date('M d, Y', strtotime($payday)), 'userLogIN'=>$userLogIN, 'userLogOUT'=>$userLogOUT]);

        

        if (!is_null($userLogIN[0]['logs']) && !is_null($userLogOUT[0]['logs']))
        {
          //---- To get the right Worked Hours, check kung early pasok == get schedule Time
          //---- if late pumasok, get user timeIN


          //************ CHECK FOR LATEIN AND EARLY OUT *************

          $isLateIN = $this->checkIfUndertime('IN', $userLogIN[0]['timing']->format('H:i:s'), $schedForToday['timeStart']);
          $isEarlyOUT = $this->checkIfUndertime('OUT',$userLogOUT[0]['timing']->format('H:i:s'),$schedForToday['timeEnd'] );

          //$coll->push(['lateIn'=>$isLateIN, 'user'=>$userLogIN[0]['timing']->format('H:i:s'), 'sched'=> $schedForToday['timeStart'],
          //  'lateOUT'=>$isEarlyOUT, 'user2'=>$userLogOUT[0]['timing']->format('H:i:s'), 'sched2'=> $schedForToday['timeEnd'] ]);

          if ($isEarlyOUT && $isLateIN)//use user's logs
          {

            $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
            $workedHours = number_format($wh/60,2);
            $billableForOT=0;
            

          }
          else if ($isEarlyOUT){
            $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour());
            $workedHours = number_format($wh/60,2)."<br/><small>(early OUT)</small>";$billableForOT=0;
          }
          else if ($isLateIN){
            $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($userLogIN[0]['timing'])->addHour());
            $workedHours = number_format($wh/60,2)."<br/><small>(Late IN)</small>";$billableForOT=0;
          }
          else {

             $wh = Carbon::parse($userLogOUT[0]['timing'])->diffInMinutes(Carbon::parse($schedForToday['timeStart'])->addHour());

            if ($wh > 480){ $workedHours = 8.0; $billableForOT = number_format((Carbon::parse($shiftEnd)->diffInMinutes(Carbon::parse($userLogOUT[0]['timing']) ))/60,2);  }
            else { $workedHours = number_format($wh/60,2)."else"; }
            

          }
          
          //$coll->push(['wh'=>$wh, 'timeStart'=>$schedForToday['timeStart'], 'end'=>$userLogOUT[0]['timing'], 'logsIN'=>$userLogIN[0]['logs'] ]);
        } 
        else
        {
          $workedHours = "<strong class=\"text-danger\">AWOL</strong>";
        }
       */






                                


?>