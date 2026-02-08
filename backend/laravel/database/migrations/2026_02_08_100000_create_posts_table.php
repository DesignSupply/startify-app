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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id');
            $table->string('title', 255);
            $table->text('body');
            $table->string('author', 255);
            $table->timestamp('published_at');
            $table->boolean('is_deleted')
                ->default(false)
                ->index();
            $table->timestamp('deleted_at')
                ->nullable();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('admin_user_id')
                ->references('id')
                ->on('admin_users')
                ->onDelete('cascade');

            // インデックス
            $table->index('admin_user_id');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
