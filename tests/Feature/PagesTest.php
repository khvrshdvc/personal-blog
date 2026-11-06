<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_asosiy_sahifalar_ochiladi(): void
    {
        foreach (['/', '/fikrlar', '/dasturlash', '/yozuvlar', '/loyihalar', '/faollik', '/men'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_navigatsiyada_faol_boolim_belgilanadi(): void
    {
        $this->get('/fikrlar')->assertSee('class="c tn on"', false);
    }

    public function test_navigatsiya_yozuvlar_sonini_korsatadi(): void
    {
        Post::factory()->count(2)->create();
        Post::factory()->dev()->create();
        Post::factory()->draft()->create();

        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression('/<span>2<\/span><\/span>\s*<b>Fikrlar/', $html);
        $this->assertMatchesRegularExpression('/<span>1<\/span><\/span>\s*<b>Dasturlash/', $html);
    }

    public function test_ishga_ochiq_holati_sozlamadan_olinadi(): void
    {
        $this->get('/')->assertSee('Ishga ochiq');

        Setting::create(['key' => 'open', 'value' => false]);

        $this->get('/')->assertSee('Hozircha band')->assertDontSee('Ishga ochiq');
    }
}
