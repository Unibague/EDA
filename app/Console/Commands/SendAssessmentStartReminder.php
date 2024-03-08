<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendAssessmentStartReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessmentReminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $assessmentStartDate = DB::table('assessment_periods as ap')->where('active','=',1)->first()->assessment_start_date;
        $now = Carbon::now();
/*        dd($now);

        $date = $now->toDateString();

        if($date === )*/
        Log::info("Entrando correctamente cronjob correo recordatorio");

        return 0;
    }
}
