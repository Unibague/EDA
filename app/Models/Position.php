<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Position extends Model
{

    protected $guarded = [];

    public function users (): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function responseIdeals (): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResponseIdeal::class);
    }

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    public function ableToAssign()
    {
        $positions = self::all();
        $ableToAssign = [];
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $responseIdeals = DB::table('response_ideals')->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get();

        foreach ($positions as $position){
            foreach ($responseIdeals as $responseIdeal){

            }
        }
    }

    public static function syncJobTitles($jobTitles)
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $upsertData = [];
        foreach ($jobTitles as $jobTitle){
            $upsertData [] = ['job_title' => $jobTitle,
                                'assessment_period_id' => $activeAssessmentPeriodId];
        }
        DB::table('job_title_positions')->upsert($upsertData, ['job_title', 'assessment_period_id']);
    }

    use HasFactory;
}
