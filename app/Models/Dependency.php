<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dependency extends Model
{

    protected $guarded = [];
    protected $primaryKey = 'identifier';
    public $incrementing = false;
    protected $keyType = 'string';

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->BelongsToMany(User::class);
    }

    public static function createOrUpdateFromArray(array $dependencies): void
    {
        $upsertData = [];
        $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        foreach ($dependencies as $dependency) {
            $dependencyAsString = (string)$dependency->code;
            $assessmentPeriodAsString = (string)$assessmentPeriodId;
            $identifier = $dependencyAsString.'-'.$assessmentPeriodAsString;

            $upsertData[] = [
                'identifier' => $identifier,
                'code' => $dependency->code,
                'name' => $dependency->name,
                'is_custom' => 0,
                'assessment_period_id' => $assessmentPeriodId
            ];

            DB::table('dependencies')->upsert($upsertData, $identifier, ['name', 'is_custom', 'assessment_period_id']);
        }
    }

    public static function getDependencies(int $assessmentPeriodId = null){

        if ($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return self::where('assessment_period_id','=', $assessmentPeriodId)->with('functionariesFromDependency')->get();
    }

    public function functionariesFromDependency(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        $functionaryRoleId= Role::getRoleIdByName('funcionario');

        return $this->belongsToMany(User::class,'dependency_user','dependency_identifier','user_id', 'identifier', 'id')->
        wherePivot('role_id', $functionaryRoleId);
    }


    use HasFactory;
}
