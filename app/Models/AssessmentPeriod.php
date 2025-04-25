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

        // Forms

        $activeForms = DB::table('forms')
            ->where('assessment_period_id', $activeAssessmentPeriodId)->get();

        $formsToInsert = $activeForms->map(function ($form) use ($createdAssessmentPeriod, $now) {
            return [
                'name' => $form->name,
                'description' => $form->description,
                'dependency_role' => $form->dependency_role,
                'position' => $form->position,
                'questions' => $form->questions,
                'assessment_period_id' => $createdAssessmentPeriod->id,
                'creation_assessment_period_id' => $form->assessment_period_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        DB::table('forms')->insert($formsToInsert);

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

// 1. Obtener los perfiles válidos del nuevo periodo
        $validProfiles = DB::table('functionary_profiles')
            ->where('assessment_period_id', $createdAssessmentPeriod->id)
            ->get();

// 1.1 Mapear user_id => dependency_identifier para validación rápida
        $userDependencyMap = $validProfiles->pluck('dependency_identifier', 'user_id')->toArray();

// 1.2 Obtener los dependency_identifier válidos en la tabla dependencies
        $validDependencyIdentifiers = DB::table('dependencies')
            ->where('assessment_period_id', $createdAssessmentPeriod->id)
            ->pluck('identifier')
            ->toArray();

// 2. Traer los assessments del periodo activo
        $assessments = DB::table('assessments')
            ->where('assessment_period_id', $activeAssessmentPeriodId)
            ->get();

        $allowedRoles = ['jefe', 'par', 'cliente interno', 'cliente externo'];
        $assessmentsToInsert = [];

        foreach ($assessments as $assessment) {
            $role = $assessment->role;

            if ($role === 'autoevaluación') {
                continue;
            }

            // Validar que el evaluado está en los perfiles válidos
            if (!array_key_exists($assessment->evaluated_id, $userDependencyMap)) {
                continue;
            }

            $evaluatedInProfile = true;
            $evaluatorInProfile = in_array($assessment->evaluator_id, array_keys($userDependencyMap));

            // Construir el nuevo dependency_identifier
            $prefix = explode('-', $assessment->dependency_identifier)[0];
            $newDependencyIdentifier = $prefix . '-' . $createdAssessmentPeriod->id;

            // Validar que exista en dependencies y que corresponda al evaluated_id
            if (
                !in_array($newDependencyIdentifier, $validDependencyIdentifiers) ||
                $userDependencyMap[$assessment->evaluated_id] !== $newDependencyIdentifier
            ) {
                continue; // Saltar si no es válido
            }

            // Insertar si cumple todas las condiciones
            if (in_array($role, ['jefe', 'par', 'cliente interno'])) {
                $assessmentsToInsert[] = [
                    'evaluated_id' => $assessment->evaluated_id,
                    'evaluator_id' => $evaluatorInProfile ? $assessment->evaluator_id : null,
                    'role' => $assessment->role,
                    'pending' => 1,
                    'assessment_period_id' => $createdAssessmentPeriod->id,
                    'dependency_identifier' => $newDependencyIdentifier,
                    'form_answer_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($role === 'cliente externo') {
                $assessmentsToInsert[] = [
                    'evaluated_id' => $assessment->evaluated_id,
                    'evaluator_id' => $assessment->evaluator_id,
                    'role' => $assessment->role,
                    'pending' => 1,
                    'assessment_period_id' => $createdAssessmentPeriod->id,
                    'dependency_identifier' => $newDependencyIdentifier,
                    'form_answer_id' => null,
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
        return self::where('active', '=', 1)->firstOrFail();
    }

    public static function importResponseIdeals($assessmentPeriodId)
    {

        $previousResponseIdeals = DB::table('response_ideals')->where('assessment_period_id', '=', self::getActiveAssessmentPeriod()->id)->get();

        foreach ($previousResponseIdeals as $responseIdeal) {
            $responseIdeal = ResponseIdeal::find($responseIdeal->id);
            $newResponseIdeal = $responseIdeal->replicate(['assessment_period_id']);
            $newResponseIdeal->assessment_period_id = $assessmentPeriodId;
            $newResponseIdeal->save();
        }

    }


    use HasFactory;
}
