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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->default(0);
            $table->string("link")->nullable();
            $table->integer("blue_user_id");
            $table->string("blue_code");
            $table->integer("saffron_user_id");
            $table->string("saffron_code");
            $table->integer("gold_user_id");
            $table->string("gold_code");
            $table->integer("isActive")->default(0);
            $table->integer("isVerified")->default(0);
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
        Schema::dropIfExists('registrations');
    }
};
