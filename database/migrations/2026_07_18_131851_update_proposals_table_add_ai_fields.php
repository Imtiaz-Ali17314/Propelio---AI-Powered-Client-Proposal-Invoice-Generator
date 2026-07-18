<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // client_brief add karte hain (purana 'brief' column already hai, use rehne dete hain
            // ya niche isko drop bhi kar sakte hain — dekh lo)
            $table->json('scope')->nullable()->after('ai_generated_content');
            $table->json('timeline')->nullable()->after('scope');
            $table->json('cost_breakdown')->nullable()->after('timeline');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['scope', 'timeline', 'cost_breakdown']);
        });
    }
};