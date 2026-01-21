<?php

namespace App\Console\Commands;

use App\Models\AssessmentPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveAssessmentPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessment-period:remove {periodId : ID del periodo de evaluación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina un periodo de evaluación y toda su información asociada (solo si NO está activo)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodId = $this->argument('periodId');

        $period = AssessmentPeriod::find($periodId);

        if (!$period) {
            $this->error("El periodo con ID {$periodId} no existe.");
            return Command::FAILURE;
        }

        if ($period->active) {
            $this->error("No se puede eliminar un periodo de evaluación activo.");
            return Command::FAILURE;
        }

        DB::transaction(function () use ($period) {

            // Eliminar evaluaciones
            DB::table('assessments')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar position_user
            DB::table('position_user')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar response_ideals
            DB::table('response_ideals')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar competences
            DB::table('competences')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar forms
            DB::table('forms')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar job_title_positions
            DB::table('job_title_positions')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar functionary_profiles
            DB::table('functionary_profiles')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Eliminar dependencies
            DB::table('dependencies')
                ->where('assessment_period_id', $period->id)
                ->delete();

            // Finalmente eliminar el periodo
            $period->delete();
        });

        $this->info("Periodo de evaluación '{$period->name}' eliminado correctamente.");
        return Command::SUCCESS;
    }
}
