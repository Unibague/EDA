<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessmentPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    public function form(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public static function createFormFromRequest(Request $request, Form $form): void
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $competencesAverage = self::getCompetencesAverage(json_decode(json_encode($request->input('answers'), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR));

        $formAnswer = self::create([
            'user_id' => auth()->user()->id,
            'form_id' => $form->id,
            'answers' => json_encode($request->input('answers')),
            'submitted_at' => Carbon::now('GMT-5')->toDateTimeString(),
            'evaluated_id' => $request->input('evaluatedId'),
            'first_competence_average' => $competencesAverage['C1'] ?? null,
            'second_competence_average' => $competencesAverage['C2'] ?? null,
            'third_competence_average' => $competencesAverage['C3'] ?? null,
            'fourth_competence_average' => $competencesAverage['C4'] ?? null,
            'fifth_competence_average' => $competencesAverage['C5'] ?? null,
            'sixth_competence_average' => $competencesAverage['C6'] ?? null,
            'assessment_period_id' => $activeAssessmentPeriodId,
        ]);

        self::updateResponseStatusToAnswered($request->input('evaluatedId'), $request->input('role'), $formAnswer);
    }

    public static function getCompetencesAverage($answers): array
    {
        $competences = self::getCompetencesFromFormAnswer($answers);
        return self::getAveragesFromCompetences($competences);
    }

    private static function getCompetencesFromFormAnswer($formAnswers): array
    {
        $competences = [];
        try{
            foreach ($formAnswers as $answer) {
                if (isset($competences[$answer->competence]) === true) {
                    $competences[$answer->competence]['totalAnswers']++;
                } else {
                    $competences[$answer->competence]['totalAnswers'] = 1;
                }

                // the competence already exist at this point
                if (isset($competences[$answer->competence]['accumulatedValue']) === true) {
                    $competences[$answer->competence]['accumulatedValue'] += (double)$answer->answer;
                } else {
                    $competences[$answer->competence]['accumulatedValue'] = (double)$answer->answer;
                }
            }
        }
        catch (\Exception $exception) {
            $message = 'Debes contestar todas las preguntas para poder enviar el formulario';
            throw new \RuntimeException($message);
        }
        return $competences;
    }

    private static function getAveragesFromCompetences($competences): array
    {
        $averages = [];
        foreach ($competences as $competence => $attributes) {
            $averages[$competence] = $attributes['accumulatedValue'] / $attributes['totalAnswers'];
        }
        return $averages;
    }

    public static function updateResponseStatusToAnswered($evaluatedId, $role, $formAnswer): void
    {
        $evaluatorId = auth()->user()->id;
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        Assessment::where('evaluated_id','=', $evaluatedId)->where('role', $role)->where('evaluator_id', $evaluatorId)
            ->where('assessment_period_id', $activeAssessmentPeriodId)->update([
                'pending' => 0 , 'form_answer_id' => $formAnswer->id
            ]);
    }


}
