<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
