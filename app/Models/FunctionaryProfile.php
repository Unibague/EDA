<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FunctionaryProfile extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function  user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getFunctionariesAssessments(string $dependencyIdentifier)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $dataFromFunctionaries = [];
        $functionariesFromDependency = DB::table('assessments as a')->select(['a.evaluated_id as id', 'fp.id as functionary_profile_id', 'fp.name as name', 'fp.job_title as job_title'])
            ->where('a.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->join('functionary_profiles as fp','a.evaluated_id', '=', 'fp.user_id')
            ->where('fp.assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('a.dependency_identifier', '=', $dependencyIdentifier)->distinct()->get();

        foreach ($functionariesFromDependency as $functionaryFromDependency){
            $functionaryAssessments = (object) ['id' => $functionaryFromDependency->id, 'functionary_profile_id' => $functionaryFromDependency->functionary_profile_id,
                'name' => $functionaryFromDependency->name, 'job_title' => $functionaryFromDependency->job_title];

            $assessmentsFromFunctionary = DB::table('assessments as a')->select(['u.name as name', 'a.role as role'])
                ->where('a.evaluated_id', '=', $functionaryFromDependency->id)
                ->where('a.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->join('users as u','a.evaluator_id', '=', 'u.id')->get();

            foreach($assessmentsFromFunctionary as $assessmentFromFunctionary){
                if($assessmentFromFunctionary->role === "jefe"){
                    $functionaryAssessments->boss = $assessmentFromFunctionary->name;
                }
                if($assessmentFromFunctionary->role === "par"){
                    $functionaryAssessments->peer = $assessmentFromFunctionary->name;
                }
                if($assessmentFromFunctionary->role === "cliente interno" || $assessmentFromFunctionary->role === "cliente externo"){
                    $functionaryAssessments->client = $assessmentFromFunctionary->name;
                }
            }
            $dataFromFunctionaries [] = $functionaryAssessments;
        }

        return $dataFromFunctionaries;
    }

    public static function createOrUpdateFromArray(array $functionaries): void
    {
        $finalFunctionaries = $functionaries;
        $functionaryRoleId = Role::getRoleIdByName('funcionario');
        $jobTitles = array_unique(array_column($finalFunctionaries, 'position'));
        $serialized = array_map('serialize', $finalFunctionaries);
        $unique = array_unique($serialized);
        $finalFunctionaries = array_intersect_key($finalFunctionaries, $unique);

        $assessment_period_id = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $assessmentPeriodAsString = (string)$assessment_period_id;
        $errorMessage = '';

        Position::syncJobTitles($jobTitles);

        foreach ($finalFunctionaries as $functionary) {
            $user = User::firstOrCreate(['email' => $functionary['email']], ['name' => $functionary['full_name'],
                'password' => Hash::make($functionary['identification'] . $functionary['email'])]);

            if ($functionary['dep_code'] === "") {
                continue;
            }
            $dependencyIdentifier = $functionary['dep_code'] . '-' . $assessmentPeriodAsString;
            try {
                self::updateOrCreate(
                    [
                        'identification_number' => $functionary['identification'],
                        'dependency_identifier' => $dependencyIdentifier === '' ? null : $dependencyIdentifier,
                    ],
                    [
                        'name' => $functionary['full_name'],
                        'user_id' => $user->id,
                        'dependency_name' => $functionary['faculty'] === '' ? null : $functionary['faculty'],
                        'job_title' => $functionary['position'] === '' ? null : $functionary['position'],
                        'hire_date' => $functionary['date_admission'] === '' ? null : $functionary['date_admission'],
                        'assessment_period_id' => $assessment_period_id
                    ]);

                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id,
                        'role_id' => $functionaryRoleId]
                );
                self::assignFunctionaryToDependency($user->id, $dependencyIdentifier);
            } catch (\Exception $e) {
                $errorMessage .= nl2br("Ha ocurrido el siguiente error mirando al docente $functionary[full_name] : {$e->getMessage()}");
            }
        }
        if ($errorMessage !== '') {
            throw new \RuntimeException($errorMessage);
        }

    }

    public static function assignFunctionaryToDependency($userId, $dependencyIdentifier): void
    {
        $functionaryRoleId = Role::getRoleIdByName('funcionario');
        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        DB::table('dependency_user')->updateOrInsert(
            ['user_id' => $userId, 'dependency_identifier' => $dependencyIdentifier, 'role_id' => $functionaryRoleId],
            ['user_id' => $userId, 'dependency_identifier' => $dependencyIdentifier, 'role_id' => $functionaryRoleId]
        );

        $user = DB::table('assessments')->where('evaluated_id', $userId)
            ->where('evaluator_id', $userId)->where('assessment_period_id', '=', $activeAssessmentPeriod)->first();

        if (!$user) {
            DB::table('assessments')->updateOrInsert(
                ['evaluated_id' => $userId, 'evaluator_id' => $userId, 'role' => 'autoevaluación'],
                ['pending' => 1, 'dependency_identifier' => $dependencyIdentifier,
                    'assessment_period_id' => $activeAssessmentPeriod,
                    'created_at' => Carbon::now('GMT-5')->toDateTimeString(),
                    'updated_at' => Carbon::now('GMT-5')->toDateTimeString()]);
        }
    }
}
