<?php

namespace App\Console\Commands;

use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAggregateAssessmentResultsReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aggregate_grades:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        //First retrieve all the form_answers
        $formAnswers = DB::table('form_answers as fa')
            ->select(['fa.user_id','fa.evaluated_id','a.role','fa.first_competence_average','fa.second_competence_average',
                'fa.third_competence_average','fa.fourth_competence_average','fa.fifth_competence_average','fa.sixth_competence_average',])
            ->where('fa.assessment_period_id','=',$activeAssessmentPeriodId)
            ->join('assessments as a', 'fa.id','=','a.form_answer_id')->get()->toArray();

        $evaluatedIds = array_unique(array_column($formAnswers, 'evaluated_id'));

        //Now iterate over every evaluatedId and filter the $formAnswers array and do the calculations
        foreach ($evaluatedIds as $evaluatedId){
            $localFormAnswers = array_filter($formAnswers, function ($formAnswer) use($evaluatedId){
                return $formAnswer->evaluated_id === $evaluatedId;
            });

            //In order to calculate the final aggregate results, four (or more) actors have to submit the answer
            if(count($localFormAnswers) >= 4){
                //First, define the final values
                $final_first_aggregate_competence = 0;
                $final_second_aggregate_competence= 0;
                $final_third_aggregate_competence= 0;
                $final_fourth_aggregate_competence = 0;
                $final_fifth_aggregate_competence= 0;
                $final_sixth_aggregate_competence= 0;
                $userHasTwoClients = false;
                $weights = DB::table('actors_assessment_weight')->get();

                //Now, let's check if the functionary has two evaluators clients
                if(count($localFormAnswers) > 4){
                    $userHasTwoClients = true;
                }

                foreach ($localFormAnswers as $formAnswer) {
                    if($formAnswer->role === "cliente interno" || $formAnswer->role === "cliente externo"){
                        $formAnswer->role = "cliente";
                    }

                    //Depending on the assessment role, then we get the assessment weight
                    $assessmentWeight =  $weights->where('name','=', $formAnswer->role)->first()->value;

                    if($formAnswer->role === "cliente" && $userHasTwoClients)
                    {$assessmentWeight = 0.15;
                    }

                    //Now for every $formAnswer, multiply the value for the corresponding $assessmentWeight and add it to the final result on every final_aggregate_competence
                    $final_first_aggregate_competence += $formAnswer->first_competence_average*$assessmentWeight;
                    $final_second_aggregate_competence += $formAnswer->second_competence_average*$assessmentWeight;
                    $final_third_aggregate_competence += $formAnswer->third_competence_average*$assessmentWeight;
                    $final_fourth_aggregate_competence += $formAnswer->fourth_competence_average*$assessmentWeight;
                    $final_fifth_aggregate_competence += $formAnswer->fifth_competence_average*$assessmentWeight;
                    $final_sixth_aggregate_competence += $formAnswer->sixth_competence_average*$assessmentWeight;
                }

                $firstCompetenceTotal = number_format($final_first_aggregate_competence, 1);
                $secondCompetenceTotal = number_format($final_second_aggregate_competence, 1);
                $thirdCompetenceTotal = number_format($final_third_aggregate_competence, 1);
                $fourthCompetenceTotal = number_format($final_fourth_aggregate_competence, 1);
                $fifthCompetenceTotal = number_format($final_fifth_aggregate_competence, 1);
                $sixthCompetenceTotal = number_format($final_sixth_aggregate_competence, 1);

                //Get the dependency of the user
                $dependencyIdentifier = DB::table('assessments as a')->where('evaluated_id','=',$evaluatedId)
                    ->where('assessment_period_id','=',$activeAssessmentPeriodId)->where('pending','=',0)->first()->dependency_identifier;

                //Insert the data
                DB::table('aggregate_assessment_results')->updateOrInsert(
                    ['user_id' => $evaluatedId, 'assessment_period_id' => $activeAssessmentPeriodId],
                    ['first_competence' => $firstCompetenceTotal,
                        'second_competence' => $secondCompetenceTotal,
                        'third_competence' => $thirdCompetenceTotal,
                        'fourth_competence' => $fourthCompetenceTotal,
                        'fifth_competence' => $fifthCompetenceTotal,
                        'sixth_competence' => $sixthCompetenceTotal,
                        'dependency_identifier' => $dependencyIdentifier,
                        'role' => 'promedio final',
                        'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                        'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);
            }
        }

        Log::info("Aggregate grades updated correctly");

        return 0;
    }
}
