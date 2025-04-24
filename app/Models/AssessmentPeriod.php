<?php

namespace App\Models;

use App\Helpers\AtlanteProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssessmentPeriod extends Model
{

    protected $fillable = [
        'name',
        'assessment_start_date',
        'assessment_end_date',
        'commitment_start_date',
        'commitment_end_date',
    ];

    protected $guarded = ['active'];

    public function positions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function userProfiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function commitments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Commitment::class);
    }

    public function dependencies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dependency::class);
    }

    public function assessments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function aggregateAssessmentResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AggregateAssessmentResult::class);
    }

    public function forms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswer::class);
    }

    public function assessmentReminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssessmentReminder::class);
    }

/*    public static function migrateActiveAssessmentPeriodInformation($activeAssessmentPeriod, $destinationAssessmentPeriod)
    {
        //We have to migrate the competences, response ideals, position_user and job_title_positions
        $tablesName = ['forms'];

        foreach ($tablesName as $tableName){
            //First validate that there are no records associated to the desired assessmentPeriod to migrate to on the selected table
            $destinationAssessmentPeriodRecords = DB::table($tableName)->where('assessment_period_id','=',$destinationAssessmentPeriod->id)
                ->get();
            //If, in fact, there are no records in the new assessmentPeriod, then proceed with the insert of the data
            if(count($destinationAssessmentPeriodRecords)  === 0){
                $activeAssessmentPeriodRecords = DB::table($tableName)->where('assessment_period_id','=',$activeAssessmentPeriod->id)
                    ->get()->map(function ($item) {
                        return collect($item)->except(['id','created_at', 'updated_at'])->all();
                    })->toArray();
                foreach ($activeAssessmentPeriodRecords as &$activeAssessmentPeriodRecord){
                    $activeAssessmentPeriodRecord['assessment_period_id'] = $destinationAssessmentPeriod['id'];
                    $activeAssessmentPeriodRecord['created_at'] = Carbon::now()->toDateTimeString();
                    $activeAssessmentPeriodRecord['updated_at'] = Carbon::now()->toDateTimeString();
                    DB::table($tableName)->insert($activeAssessmentPeriodRecord);
                }
            }
            else {
                return false;
            }
        }
        return true;
    }*/

    public static function migrateActiveAssessmentPeriodInformation($createdAssessmentPeriod)
    {
        $activeAssessmentPeriodId = self::getActiveAssessmentPeriod()->id;
        $now = Carbon::now()->toDateTimeString();

        //dependencies table
        $dependencies = AtlanteProvider::get('functionariesChart/dependencies');
        Dependency::createOrUpdateFromArray($dependencies, $createdAssessmentPeriod->id);
        //

        //dependency_user, functionary_profiles
        $functionaries = AtlanteProvider::get('functionaries', [
            'type_employee' => 'ADM',
        ], true);
        FunctionaryProfile::createOrUpdateFromArray($functionaries, $createdAssessmentPeriod->id);
        //

        //job_title_positions
// Obtener los job_title_positions del periodo activo
        $activeJobTitlePositions = DB::table('job_title_positions')
            ->where('assessment_period_id', $activeAssessmentPeriodId)
            ->get()
            ->keyBy('job_title'); // Indexamos por job_title para acceso rápido

// Obtener los del periodo creado
        $createdJobTitlePositions = DB::table('job_title_positions')
            ->where('assessment_period_id', $createdAssessmentPeriod->id)
            ->get();

        foreach ($createdJobTitlePositions as $created) {
            if (isset($activeJobTitlePositions[$created->job_title])) {
                $activePositionId = $activeJobTitlePositions[$created->job_title]->position_id;

                DB::table('job_title_positions')
                    ->where('id', $created->id)
                    ->update(['position_id' => $activePositionId]);
            }
            //
        }

        //competences
        // Obtener las competencias del periodo activo
        $activeCompetences = DB::table('competences')
            ->where('assessment_period_id', $activeAssessmentPeriodId)
            ->get();



        $competencesToInsert = $activeCompetences->map(function ($competence) use ($createdAssessmentPeriod, $now) {
            return [
                'name' => $competence->name,
                'position' => $competence->position,
                'assessment_period_id' => $createdAssessmentPeriod->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

// Insertar en masa
        DB::table('competences')->insert($competencesToInsert);

        //response_ideals

        // Obtener los response_ideals del periodo activo
        $activeResponses = DB::table('response_ideals')
            ->where('assessment_period_id', $activeAssessmentPeriodId)
            ->get();

        $responseIdealsToInsert = $activeResponses->map(function ($response) use ($createdAssessmentPeriod, $now) {
            return [
                'position_id' => $response->position_id,
                'response' => $response->response,
                'assessment_period_id' => $createdAssessmentPeriod->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

// Insertar en masa
        DB::table('response_ideals')->insert($responseIdealsToInsert);

        //position_user (solo users con un functionary_profile existente)

        // 1. Obtener los user_id del nuevo periodo
        $userIds = DB::table('functionary_profiles')
            ->where('assessment_period_id', $createdAssessmentPeriod->id)
            ->pluck('user_id')
            ->toArray();

// 2. Obtener los registros de position_user del periodo activo, filtrando por user_id
        $positionUsers = DB::table('position_user')
            ->where('assessment_period_id', $activeAssessmentPeriodId)
            ->whereIn('user_id', $userIds)
            ->get();

        $now = now();

// 3. Preparar los nuevos registros para insertar
        $positionUsersToInsert = $positionUsers->map(function ($pu) use ($createdAssessmentPeriod, $now) {
            return [
                'user_id' => $pu->user_id,
                'position_id' => $pu->position_id,
                'assessment_period_id' => $createdAssessmentPeriod->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

// 4. Insertar
        DB::table('position_user')->insert($positionUsersToInsert);


        //assessments (jefe, par, cliente interno / externo) de solo users con un functionary_profile existente en el createdAssessmentPeriodId


        // 1. Obtener los user_id válidos del nuevo periodo
        $validUserIds = DB::table('functionary_profiles')
            ->where('assessment_period_id', $createdAssessmentPeriod->id)
            ->pluck('user_id')
            ->toArray();

// 2. Traer todos los assessments del periodo activo
        $assessments = DB::table('assessments')
            ->where('assessment_period_id', $activeAssessmentPeriodId)
            ->get();

        $allowedRoles = ['jefe', 'par', 'cliente interno', 'cliente externo'];
        $assessmentsToInsert = [];

        foreach ($assessments as $assessment) {

            $role = $assessment->role;

            // Omitir autoevaluación y roles no permitidos
            if ($role === 'autoevaluación') {
                continue;
            }

            $evaluatedInProfile = in_array($assessment->evaluated_id, $validUserIds);
            $evaluatorInProfile = in_array($assessment->evaluator_id, $validUserIds);

            if (in_array($role, ['jefe', 'par', 'cliente interno'])) {
                if (!$evaluatedInProfile) {
                    continue;
                }

                $assessmentsToInsert[] = [
                    'evaluated_id' => $assessment->evaluated_id,
                    'evaluator_id' => $evaluatorInProfile ? $assessment->evaluator_id : null,
                    'role' => $assessment->role,
                    'pending' => $assessment->pending,
                    'assessment_period_id' => $createdAssessmentPeriod->id,
                    'dependency_identifier' => $assessment->dependency_identifier,
                    'form_answer_id' => $assessment->form_answer_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($role === 'cliente externo') {
                if (!$evaluatedInProfile) {
                    continue;
                }

                $assessmentsToInsert[] = [
                    'evaluated_id' => $assessment->evaluated_id,
                    'evaluator_id' => $assessment->evaluator_id,
                    'role' => $assessment->role,
                    'pending' => $assessment->pending,
                    'assessment_period_id' => $createdAssessmentPeriod->id,
                    'dependency_identifier' => $assessment->dependency_identifier,
                    'form_answer_id' => $assessment->form_answer_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

// 3. Insertar los registros válidos
        DB::table('assessments')->insert($assessmentsToInsert);



    }

    public static function getActiveAssessmentPeriod()
    {
        return self::where('active', '=',   1)->firstOrFail();
    }

    public static function importResponseIdeals($assessmentPeriodId){

        $previousResponseIdeals = DB::table('response_ideals')->where('assessment_period_id', '=', self::getActiveAssessmentPeriod()->id)->get();

        foreach ($previousResponseIdeals as $responseIdeal){
            $responseIdeal = ResponseIdeal::find($responseIdeal->id);
            $newResponseIdeal = $responseIdeal->replicate(['assessment_period_id']);
            $newResponseIdeal->assessment_period_id = $assessmentPeriodId;
            $newResponseIdeal->save();
        }

    }


    use HasFactory;
}
