<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('income3s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->integer("income3")->default(0);
            $table->integer("lead_1_id")->default(0);
            $table->integer("lead_1_income")->default(0);
            $table->integer("lead_2_id")->default(0);
            $table->integer("lead_3_id")->default(0);
            $table->integer("lead_2_income")->default(0);
            $table->integer("lead_3_income")->default(0);
            $table->integer("isDeleted")->default(0);
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income3s');
    }
};
