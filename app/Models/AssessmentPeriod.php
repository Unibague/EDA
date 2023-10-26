<?php

namespace App\Models;

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
