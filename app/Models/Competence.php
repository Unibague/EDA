<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $guarded = [];
    use HasFactory;

    public static function createCompetence($request)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        return self::UpdateOrCreate(
            [   'name' => $request['name'],
                'assessment_period_id' => $activeAssessmentPeriodId],
            [
                'position' => $request['position'],
            ]);
    }

}
