<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Wizard progress track karne ke liye — 'status' (business state) se alag concern
            $table->string('generation_step')->default('brief')->after('status');
            // values: brief | scope | timeline | cost | completed

            // Purana unused JSON column — ab scope/timeline/cost_breakdown use ho rahe hain
            $table->dropColumn('ai_generated_content');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('generation_step');
            $table->json('ai_generated_content')->nullable();
        });
    }
};