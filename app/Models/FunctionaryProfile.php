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

    public static function createOrUpdateFromArray(array $functionaries): void
    {
        $finalFunctionaries = $functionaries;
        $functionaryRoleId = Role::getRoleIdByName('funcionario');
        $jobTitles = array_unique(array_column($finalFunctionaries, 'position'));
//        $failedToSyncTeachersCounter = 0;
//        $failedToSyncTeachersArray = [];
//        $failedToSyncTeachersNames = [];
        $serialized = array_map('serialize', $finalFunctionaries);
        $unique = array_unique($serialized);
        $finalFunctionaries = array_intersect_key($finalFunctionaries, $unique);

        //Iterate over received data and create the academic period
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

       /* if($failedToSyncTeachersCounter) {

            foreach ($failedToSyncTeachersArray as $failedToSyncTeacher){

                $failedToSyncTeachersNames[] = $failedToSyncTeacher['name'];
            }
            throw new \RuntimeException("Docentes cargados, pero ocurrió un problema sincronizando a los siguientes docentes TC: " . implode(",", $failedToSyncTeachersNames));
        }*/

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
