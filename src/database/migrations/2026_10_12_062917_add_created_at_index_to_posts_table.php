<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * 記事一覧の「新しい順」の並び替えを速くするため、created_at にインデックスを追加
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * インデックスを削除して元に戻す
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
