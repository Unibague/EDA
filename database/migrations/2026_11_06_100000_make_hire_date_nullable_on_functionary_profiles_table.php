<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeHireDateNullableOnFunctionaryProfilesTable extends Migration
{
    /**
     * Pensioned functionaries have no hire date, so the column must accept NULL.
     */
    public function up()
    {
        Schema::table('functionary_profiles', function (Blueprint $table) {
            $table->date('hire_date')->nullable()->change();
        });
    }

    /**
     * Rolling back fails if any row has a NULL hire_date.
     */
    public function down()
    {
        Schema::table('functionary_profiles', function (Blueprint $table) {
            $table->date('hire_date')->nullable(false)->change();
        });
    }
}
