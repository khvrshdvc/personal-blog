<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Models\Post;
use Database\Seeders\PostSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostEditorTest extends AdminTestCase
{
    private function fields(array $override = []): array
    {
        return $override + [
            'category' => 'fikr',
            'title' => 'Yangi yozuv',
            'slug' => '',
            'tag' => 'Hayot',
            'excerpt' => '',
            'body' => 'Matn **qalin**',
            'published_at' => '2026-11-06',
            'cover' => null,
        ];
    }

    private function png(): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent('a.png', $bytes);
    }

    public function test_royxat_va_muharrir_sahifalari_ochiladi(): void
    {
        $this->seed(PostSeeder::class);
        $post = Post::where('slug', 'laravel-events')->first();

        $this->get('/admin/posts')->assertOk()->assertSee('Bugun nima haqida yozasiz?')->assertSee('Mustaqil oʻrganishning eng qiyin joyi');
        $this->get('/admin/posts/new')->assertOk()->assertSee('Yozuv sozlamalari');
        $this->get('/admin/posts/new/dev')->assertOk();
        $this->get('/admin/posts/'.$post->id)->assertOk()->assertSee('Laravel events: post yaratilganda');
        $this->get('/admin/posts/999999')->assertNotFound();
    }

    public function test_boʻsh_yozuv_saqlanmaydi(): void
    {
        $this->postJson('/admin/posts', $this->fields(['title' => '', 'body' => '  ']))
            ->assertOk()
            ->assertExactJson(['post' => null]);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_yangi_yozuv_qoralama_boʻlib_yaratiladi(): void
    {
        $response = $this->postJson('/admin/posts', $this->fields(['title' => 'Birinchi fikr']))->assertOk();

        $post = Post::first();

        $response->assertJsonPath('post.id', $post->id)->assertJsonPath('post.status', 'draft')->assertJsonPath('post.url', null);
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertSame('birinchi-fikr', $post->slug);
        $this->assertNull($post->pending);
    }

    public function test_qoralama_avtosaqlashda_bevosita_yangilanadi(): void
    {
        $post = Post::factory()->draft()->create(['title' => 'Eski']);

        $this->putJson('/admin/posts/'.$post->id, $this->fields(['title' => 'Yangi sarlavha', 'slug' => 'maxsus']))->assertOk();

        $post->refresh();
        $this->assertSame('Yangi sarlavha', $post->title);
        $this->assertSame('maxsus', $post->slug);
        $this->assertNull($post->pending);
    }

    public function test_qoralama_havolasi_band_boʻlsa_ikkinchisi_raqamlanadi_va_belgilanadi(): void
    {
        Post::factory()->create(['slug' => 'band']);
        $draft = Post::factory()->draft()->create();

        $this->putJson('/admin/posts/'.$draft->id, $this->fields(['slug' => 'band']))
            ->assertOk()
            ->assertJsonPath('post.slug_taken', true);

        $this->assertSame('band-2', $draft->fresh()->slug);
    }

    public function test_havola_tozalanadi(): void
    {
        $post = Post::factory()->draft()->create();

        $this->putJson('/admin/posts/'.$post->id, $this->fields(['slug' => '--Salom Dunyo!--']))->assertOk();

        $this->assertSame('salom-dunyo', $post->fresh()->slug);
    }

    public function test_notoʻgʻri_teg_birinchi_tegga_almashadi(): void
    {
        $post = Post::factory()->draft()->create();

        $this->putJson('/admin/posts/'.$post->id, $this->fields(['category' => 'dev', 'tag' => 'Hayot']))->assertOk();

        $this->assertSame('Laravel', $post->fresh()->tag);
    }

    public function test_chop_etilgan_yozuvda_ozgarish_pendingga_tushadi(): void
    {
        $post = Post::factory()->create(['title' => 'Asl sarlavha', 'slug' => 'asl', 'body' => 'asl matn']);

        $this->putJson('/admin/posts/'.$post->id, $this->fields(['title' => 'Yangi sarlavha', 'slug' => 'asl', 'body' => 'yangi matn', 'tag' => $post->tag]))
            ->assertOk()
            ->assertJsonPath('post.pending', true)
            ->assertJsonPath('post.fields.title', 'Yangi sarlavha');

        $post->refresh();
        $this->assertSame('Asl sarlavha', $post->title);
        $this->assertSame('Yangi sarlavha', $post->pending['title']);
        $this->get('/post/asl')->assertSee('Asl sarlavha')->assertDontSee('Yangi sarlavha');
    }

    public function test_pending_asl_holatga_qaytsa_tozalanadi(): void
    {
        $post = Post::factory()->create();
        $post->update(['pending' => array_replace($post->fields(), ['title' => 'Boshqa'])]);

        $this->putJson('/admin/posts/'.$post->id, $post->fields())->assertOk()->assertJsonPath('post.pending', false);

        $this->assertNull($post->fresh()->pending);
    }

    public function test_chop_etish(): void
    {
        $post = Post::factory()->draft()->create(['title' => 'Chop etiladigan', 'slug' => 'chop', 'excerpt' => null, 'body' => "Birinchi gap **muhim**.\n\nIkkinchi."]);

        $this->postJson('/admin/posts/'.$post->id.'/publish')
            ->assertOk()
            ->assertJsonPath('message', 'Yozuv chop etildi')
            ->assertJsonPath('post.status', 'published')
            ->assertJsonPath('post.url', route('post.show', 'chop'));

        $post->refresh();
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertSame('Birinchi gap muhim. Ikkinchi.', $post->excerpt);
        $this->get('/post/chop')->assertOk();
    }

    public function test_sarlavhasiz_yoki_matnsiz_chop_etib_boʻlmaydi(): void
    {
        $noTitle = Post::factory()->draft()->create(['title' => '', 'body' => 'matn']);
        $noBody = Post::factory()->draft()->create(['body' => '   ']);

        $this->postJson('/admin/posts/'.$noTitle->id.'/publish')->assertUnprocessable()->assertJsonValidationErrors('title');
        $this->postJson('/admin/posts/'.$noBody->id.'/publish')->assertUnprocessable()->assertJsonValidationErrors('body');

        $this->assertSame(PostStatus::Draft, $noTitle->fresh()->status);
    }

    public function test_yangilash_pendingni_qoʻllaydi(): void
    {
        $post = Post::factory()->create(['title' => 'Asl', 'slug' => 'asl']);
        $post->update(['pending' => array_replace($post->fields(), ['title' => 'Yangilangan', 'body' => 'yangi matn'])]);

        $this->postJson('/admin/posts/'.$post->id.'/publish')->assertOk()->assertJsonPath('message', 'Yozuv yangilandi');

        $post->refresh();
        $this->assertSame('Yangilangan', $post->title);
        $this->assertNull($post->pending);
        $this->get('/post/asl')->assertSee('Yangilangan');
    }

    public function test_oʻzgarishsiz_yangilash_hech_narsa_qilmaydi(): void
    {
        $post = Post::factory()->create();

        $this->postJson('/admin/posts/'.$post->id.'/publish')->assertOk()->assertJsonPath('changed', false);
    }

    public function test_band_havola_qatʼiy_rejimda_rad_etiladi(): void
    {
        Post::factory()->create(['slug' => 'band']);
        $other = Post::factory()->create(['slug' => 'asl']);
        $other->update(['pending' => array_replace($other->fields(), ['slug' => 'band'])]);

        $this->postJson('/admin/posts/'.$other->id.'/publish', ['strict' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');

        $this->assertSame('asl', $other->fresh()->slug);
    }

    public function test_band_havola_qatʼiy_boʻlmasa_raqamlanadi(): void
    {
        Post::factory()->create(['slug' => 'band']);
        $other = Post::factory()->create(['slug' => 'asl']);
        $other->update(['pending' => array_replace($other->fields(), ['slug' => 'band'])]);

        $this->postJson('/admin/posts/'.$other->id.'/publish')->assertOk();

        $this->assertSame('band-2', $other->fresh()->slug);
    }

    public function test_oʻzgarishlarni_bekor_qilish(): void
    {
        $post = Post::factory()->create(['title' => 'Asl']);
        $post->update(['pending' => array_replace($post->fields(), ['title' => 'Boshqa'])]);

        $this->postJson('/admin/posts/'.$post->id.'/discard')
            ->assertOk()
            ->assertJsonPath('post.pending', false)
            ->assertJsonPath('post.fields.title', 'Asl');

        $this->assertNull($post->fresh()->pending);
    }

    public function test_qoralamaga_olish_pendingni_ham_saqlaydi(): void
    {
        $post = Post::factory()->create(['title' => 'Asl', 'slug' => 'asl']);
        $post->update(['pending' => array_replace($post->fields(), ['title' => 'Yangi qoralama', 'slug' => 'asl'])]);

        $this->postJson('/admin/posts/'.$post->id.'/unpublish')->assertOk()->assertJsonPath('post.status', 'draft');

        auth()->logout();

        $post->refresh();
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertSame('Yangi qoralama', $post->title);
        $this->assertNull($post->pending);
        $this->get('/post/asl')->assertNotFound();
    }

    public function test_oʻchirish(): void
    {
        $post = Post::factory()->create();

        $this->deleteJson('/admin/posts/'.$post->id)
            ->assertOk()
            ->assertJsonPath('redirect', route('admin.posts.index'));

        $this->assertModelMissing($post);
    }

    public function test_matn_koʻrinishi_xavfsiz_html_qaytaradi(): void
    {
        $html = $this->postJson('/admin/posts/preview', ['body' => "## Sarlavha\n\n<script>alert(1)</script> [x](javascript:alert(1))"])
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('<h2 id="sarlavha">', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_muqova_yuklash(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/admin/posts/cover', ['cover' => $this->png()])->assertOk();

        Storage::disk('public')->assertExists($response->json('path'));
        $this->assertSame(asset('storage/'.$response->json('path')), $response->json('url'));
        $this->assertStringStartsWith('covers/', $response->json('path'));
    }

    public function test_muqova_faqat_rasm_boʻlishi_kerak(): void
    {
        Storage::fake('public');

        $this->postJson('/admin/posts/cover', ['cover' => UploadedFile::fake()->create('a.php', 10, 'application/x-php')])->assertUnprocessable();
        $this->postJson('/admin/posts/cover', ['cover' => UploadedFile::fake()->create('big.png', 6000, 'image/png')])->assertUnprocessable();
        $this->postJson('/admin/posts/cover', [])->assertUnprocessable();

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_muqova_manzili_validatsiya_qilinadi(): void
    {
        $post = Post::factory()->draft()->create();

        $this->putJson('/admin/posts/'.$post->id, $this->fields(['cover' => 'javascript:alert(1)']))->assertUnprocessable()->assertJsonValidationErrors('cover');
        $this->putJson('/admin/posts/'.$post->id, $this->fields(['cover' => '../../etc/passwd']))->assertUnprocessable();
        $this->putJson('/admin/posts/'.$post->id, $this->fields(['cover' => 'covers/abc.jpg']))->assertOk();
    }

    public function test_qoralama_faqat_adminga_koʻrinadi_chop_etilgach_hammaga(): void
    {
        $post = Post::factory()->draft()->create(['slug' => 'sirli']);

        auth()->logout();
        $this->get('/post/sirli')->assertNotFound();

        $this->actingAs(\App\Models\User::factory()->create());
        $this->postJson('/admin/posts/'.$post->id.'/publish')->assertOk();

        auth()->logout();
        $this->get('/post/sirli')->assertOk();
    }
}
