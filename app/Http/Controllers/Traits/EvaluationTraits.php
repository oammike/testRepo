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


use OAMPI_Eval\EvalType;
use OAMPI_Eval\Movement;
use OAMPI_Eval\RatingScale;
use OAMPI_Eval\EvalDetail;
use OAMPI_Eval\Competency;
use OAMPI_Eval\Competency__Attribute;
use OAMPI_Eval\Attribute;
use OAMPI_Eval\Summary;
use OAMPI_Eval\PerformanceSummary;
use OAMPI_Eval\Movement_ImmediateHead;
use OAMPI_Eval\Notification;
use OAMPI_Eval\User_Notification;


trait EvaluationTraits
{

	public function getPastMemberEvals($mc, $evalSetting)
	{
		/* ---------------------------------------------------------------- 

            GET PAST MEMBERS moved to you

        /* ---------------------------------------------------------------- */

       
        $me = $mc->first();

                            
                          
                          
      /*** OLD --- foreach ($me->myCampaigns as $m) { */
        $changedImmediateHeads = new Collection;
        $doneMovedEvals = new Collection;
        $ctr = 0;
        $mvd = new Collection;

        $currentPeriod = Carbon::create((date("Y")), $evalSetting->startMonth, $evalSetting->startDate,0,0,0, 'Asia/Manila');
        $endPeriod = Carbon::create((date("Y")), $evalSetting->endMonth, $evalSetting->endDate,0,0,0, 'Asia/Manila');

        foreach($mc as $m)
        { 

        

            $moved = Movement_ImmediateHead::where('imHeadCampID_old',$m->id)->get();
            //$mvd->push($moved);
            //$coll->push($moved);
            //$moved = Movement_ImmediateHead::where('imHeadCampID_old',$ihCamp->id)->get();
           

            $changedHeads = new Collection;
            $chIH = new Collection;

            foreach ($moved as $m) {
                $changedHeads->push($m->info);
            }

            $mvd->push($changedHeads);
           
            foreach ($changedHeads as $mvt) {
              //$evalTypes = EvalType::all();
              //$evalSetting = EvalSetting::find(2);
              
              $effective = Carbon::createFromFormat('Y-m-d H:i:s', $mvt->effectivity, 'Asia/Manila');
              //$effective = Carbon::createFromFormat('Y-m-d H:i:s', $mvt->first()->effectivity, 'Asia/Manila');
              $mvd->push(['effective'=>$mvt->effectivity, 'endPeriod'=> $endPeriod->format('Y-m-d'),'currentPeriod'=>$currentPeriod->format('Y-m-d'), 'mvtDone'=>$mvt->isDone]);


              if  ($effective->format('Y-m-d') <= $endPeriod->format('Y-m-d') && $effective->format('Y-m-d') >= $currentPeriod->format('Y-m-d') && $mvt->isDone) 
              {
                //$chIH->push($changedHeads->first());
                $chIH->push($mvt); 
              }
                
              else { // let's check if he already has an eval for that period

                $existingEval = EvalForm::where('evaluatedBy',$me->id)->where('user_id', $mvt->user_id)->where('startPeriod','>=',$currentPeriod->format('Y-m-d'))->where('endPeriod','<=',$endPeriod->format('Y-m-d'))->get();
                //$mvd->push(['existingEval'=>$existingEval, 'me'=>$me]);
                if ($existingEval->isEmpty())
                {
                    $chIH->push($mvt); 

                } 

              }
                                                    
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

                        // **** fix for movements na di pa complete yung previous eval:
                        if ($emp->effectivity > $endPeriod->format('Y-m-d'))
                        {
                            $to = $endPeriod;

                        } else $to = Carbon::createFromFormat('Y-m-d H:i:s', $emp->effectivity, 'Asia/Manila'); //


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

        $coll = new Collection;
        $coll->push(['doneMovedEvals'=>$doneMovedEvals, 'changedImmediateHeads'=>$changedImmediateHeads]);

        //return $mvd;
        return $coll;
		
	}
}

?>