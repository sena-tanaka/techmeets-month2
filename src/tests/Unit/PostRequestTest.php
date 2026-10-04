<?php

namespace Tests\Unit;

use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PostRequestTest extends TestCase
{
    private function validate(array $overrides = []): \Illuminate\Validation\Validator
    {
        $data = array_merge([
            'title'    => 'タイトル',
            'content'  => '本文',
            'category' => '日記',
        ], $overrides);

        return Validator::make($data, (new PostRequest())->rules());
    }

    public function test_正しいデータは通る(): void
    {
        $this->assertTrue($this->validate()->passes());
    }

    public function test_タイトルが空だとエラー(): void
    {
        $this->assertTrue($this->validate(['title' => ''])->fails());
    }

    public function test_タイトル255文字ちょうどはOK(): void
    {
        $this->assertTrue($this->validate(['title' => str_repeat('あ', 255)])->passes());
    }

    public function test_タイトル256文字はエラー(): void
    {
        $this->assertTrue($this->validate(['title' => str_repeat('あ', 256)])->fails());
    }

    public function test_本文10000文字ちょうどはOK(): void
    {
        $this->assertTrue($this->validate(['content' => str_repeat('あ', 10000)])->passes());
    }

    public function test_本文10001文字はエラー(): void
    {
        $this->assertTrue($this->validate(['content' => str_repeat('あ', 10001)])->fails());
    }

    public function test_カテゴリは空でもOK(): void
    {
        $this->assertTrue($this->validate(['category' => null])->passes());
    }

    public function test_カテゴリ51文字はエラー(): void
    {
        $this->assertTrue($this->validate(['category' => str_repeat('あ', 51)])->fails());
    }
}
