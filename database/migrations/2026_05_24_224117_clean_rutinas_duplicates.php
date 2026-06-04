<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rutinas')) {
            return;
        }

        DB::statement('DELETE FROM rutinas WHERE id > 6');
    }

    public function down(): void
    {
        //
    }
};