<?php

namespace App\Services;

use App\Models\AssessmentPeriod;
use App\Models\Form;
use InvalidArgumentException;

class FormService
{
    /**
     * Duplicate a form in the same assessment period, prefixing its name.
     */
    public function copyForm(Form $form): void
    {
        $newForm = $form->replicate(['name']);
        $newForm->name = 'Copia de ' . $form->name;
        $newForm->save();
    }

    /**
     * Delete a form, unless it already has answers.
     *
     * @return bool false when the form has answers and was not deleted
     */
    public function deleteForm(Form $form): bool
    {
        if ($form->formAnswers()->exists()) {
            return false;
        }

        $form->delete();
        return true;
    }

    /**
     * Copy the forms of a previous assessment period into the active one.
     * Forms whose name already exists in the active period are skipped.
     *
     * @return int number of forms copied
     *
     * @throws InvalidArgumentException when the source period is the active period
     */
    public function copyFormsFromPeriod(int $sourceAssessmentPeriodId): int
    {
        $activeAssessmentPeriodId = (int)AssessmentPeriod::getActiveAssessmentPeriod()->id;

        if ($sourceAssessmentPeriodId === $activeAssessmentPeriodId) {
            throw new InvalidArgumentException('No puedes migrar formularios desde el periodo activo');
        }

        $existingNames = Form::where('assessment_period_id', '=', $activeAssessmentPeriodId)->pluck('name')->all();
        $copied = 0;

        Form::where('assessment_period_id', '=', $sourceAssessmentPeriodId)->get()
            ->each(function (Form $form) use ($activeAssessmentPeriodId, $existingNames, &$copied) {
                if (in_array($form->name, $existingNames, true)) {
                    return;
                }

                $newForm = $form->replicate();
                $newForm->assessment_period_id = $activeAssessmentPeriodId;
                $newForm->creation_assessment_period_id = $activeAssessmentPeriodId;
                $newForm->save();
                $copied++;
            });

        return $copied;
    }
}
