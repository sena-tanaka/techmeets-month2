<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceSeeder extends Seeder
{
    /**
     * パフォーマンス計測用のダミーデータを投入する
     */
    public function run(): void
    {
        // ① ユーザーを50人作成
        User::factory()->count(50)->create();
        $userIds = User::pluck('id')->all();

        // ② 記事を5万件、1000件ずつまとめて投入
        $totalPosts = 50000;
        $chunkSize  = 1000;
        $categories = ['日記', '技術', '雑談'];

        for ($i = 0; $i < $totalPosts; $i += $chunkSize) {
            $rows = [];
            for ($j = 0; $j < $chunkSize; $j++) {
                // 過去2年のどこかの日時(新しい順の並び替えに意味を持たせる)
                $date = fake()->dateTimeBetween('-2 years', 'now');
                $rows[] = [
                    'user_id'    => fake()->randomElement($userIds),
                    'title'      => fake()->sentence(),
                    'content'    => fake()->paragraph(),
                    'category'   => fake()->randomElement($categories),
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
            DB::table('posts')->insert($rows);
            $this->command->info(($i + $chunkSize) . ' / ' . $totalPosts . ' 件の記事を投入');
        }

        // ③ いいねを約2万件投入(同じ組み合わせは insertOrIgnore で無視)
        $minPostId = DB::table('posts')->min('id');
        $maxPostId = DB::table('posts')->max('id');

        for ($i = 0; $i < 20; $i++) {
            $likes = [];
            for ($j = 0; $j < 1000; $j++) {
                $likes[] = [
                    'user_id'    => fake()->randomElement($userIds),
                    'post_id'    => fake()->numberBetween($minPostId, $maxPostId),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('likes')->insertOrIgnore($likes);
        }
        $this->command->info('いいねの投入が完了しました');
    }
}
