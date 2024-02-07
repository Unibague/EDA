<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Assessment extends Model
{

    protected $guarded = [];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->BelongsTo(AssessmentPeriod::class);
    }

    public static function getUserAssessments($user = null){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        if ($user !== null){
            return  DB::table('assessments as a')->select(['a.id', 'a.pending','u.name', 'a.role', 'a.evaluator_id', 'a.evaluated_id', 'a.assessment_period_id'])
                ->where('evaluated_id','=', $user['user_id'])->where('assessment_period_id','=', $activeAssessmentPeriodId)
                ->join('users as u', 'u.id', '=', 'a.evaluator_id')->get();
        }

        $user = auth()->user();

        if($user->role()->name == "funcionario" || $user->role()->name == "administrador" || $user->role()->name == "cliente externo") {

            return  DB::table('assessments as a')
                ->select(['a.id', 'a.pending','u.name', 'a.role', 'a.evaluator_id', 'a.evaluated_id', 'a.assessment_period_id', 'fp.dependency_name'])
                ->where('evaluator_id','=', $user['id'])->where('a.assessment_period_id','=', $activeAssessmentPeriodId)
                ->join('users as u', 'u.id', '=', 'a.evaluated_id')
                ->join('functionary_profiles as fp', 'fp.user_id', '=', 'u.id')
                ->where('fp.assessment_period_id','=', $activeAssessmentPeriodId)->get();
        }

    }

    public static function createAssessment($request){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        return self::UpdateOrCreate(
            [
                'evaluated_id' => $request->input('evaluated_id'),
                'role' => $request->input('role'),
                'assessment_period_id' => $activeAssessmentPeriodId,
            ],
            [
                'evaluator_id' => $request->input('evaluator_id'),
                'pending' => $request->input('pending'),
                'dependency_identifier' => $request->input('dependency_identifier')
            ]);
    }

    use HasFactory;
}
