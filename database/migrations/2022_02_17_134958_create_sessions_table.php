<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }



/*$table->id();
$table->string('name');
$table->text('description')->nullable();
$table->enum('type', ['estudiantes', 'otros']);
$table->foreignId('assessment_period_id')->nullable()->constrained();
$table->json('units')->nullable();
$table->foreignId('academic_period_id')->nullable()->constrained();
$table->enum('teaching_ladder', ['ninguno', 'auxiliar', 'asistente', 'asociado', 'titular'])->nullable();
$table->foreignId('creation_assessment_period_id')->references('id')->on('assessment_periods');
$table->json('questions')->nullable();
$table->timestamps();*/


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
