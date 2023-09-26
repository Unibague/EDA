<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentPeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('assessment_start_date');
            $table->date('assessment_end_date');
            $table->date('commitment_start_date');
            $table->date('commitment_end_date');
            $table->boolean('active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_periods');
    }
}
