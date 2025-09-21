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
        Schema::create("refresh_tokens", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->unsignedBigInteger("user_id");
            $table->char("token_hash", 64)->unique();
            $table->string("ip", 45)->nullable();
            $table->text("ua")->nullable();
            $table->timestamp("revoked_at")->nullable();
            $table->timestamp("expires_at")->nullable();
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->index(["user_id"]);
            $table->index(["expires_at"]);
            $table->index(["revoked_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("refresh_tokens");
    }
};
