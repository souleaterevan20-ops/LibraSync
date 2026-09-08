<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('email');
            $table->string('department')->nullable()->after('contact_number');
            $table->string('course_program')->nullable()->after('department');
            $table->string('school_or_employee_id')->nullable()->after('course_program');
            $table->string('year_level_position')->nullable()->after('school_or_employee_id');
            $table->date('date_of_birth')->nullable()->after('year_level_position');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'contact_number',
                'department',
                'course_program',
                'school_or_employee_id',
                'year_level_position',
                'date_of_birth',
            ]);
        });
    }
};
