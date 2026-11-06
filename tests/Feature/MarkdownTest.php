<?php

namespace Tests\Feature;

use App\Support\Markdown;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    public function test_html_ekranlanadi(): void
    {
        $html = Markdown::render("<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>")['html'];

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_xavfli_havolalar_olib_tashlanadi(): void
    {
        $html = Markdown::render('[a](javascript:alert) [b](//evil.uz) [c](data:text/html;base64,AAA) [d](https://ok.uz) [e](/post/x) [f](#bolim)')['html'];

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('//evil.uz', $html);
        $this->assertStringNotContainsString('data:', $html);
        $this->assertStringContainsString('href="https://ok.uz"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('href="/post/x"', $html);
        $this->assertStringContainsString('href="#bolim"', $html);
    }

    public function test_rasm_faqat_https_va_atributlar_ekranlanadi(): void
    {
        $html = Markdown::render("![x\"onerror=\"a](https://a.uz/i.png)\n![j](javascript:alert(1))")['html'];

        $this->assertStringContainsString('<img src="https://a.uz/i.png"', $html);
        $this->assertStringNotContainsString('"onerror="', $html);
        $this->assertStringNotContainsString('src="javascript', $html);
    }

    public function test_kod_bloki_ekranlanadi_va_nusxalash_tugmasi_bor(): void
    {
        $html = Markdown::render("```php\n<?php echo \"<b>\";\n```")['html'];

        $this->assertStringContainsString('&lt;?php echo &quot;&lt;b&gt;&quot;;', $html);
        $this->assertStringContainsString('class="copy-code"', $html);
        $this->assertStringContainsString('<span>php</span>', $html);
    }

    public function test_sarlavhalar_unikal_id_oladi_va_royxatga_tushadi(): void
    {
        $result = Markdown::render("## Oʻzimga eslatma\n\n### Kichik\n\n## Oʻzimga eslatma");

        $this->assertSame(['ozimga-eslatma', 'kichik', 'ozimga-eslatma-2'], array_column($result['headings'], 'id'));
        $this->assertSame([2, 3, 2], array_column($result['headings'], 'level'));
        $this->assertStringContainsString('<h2 id="ozimga-eslatma-2">', $result['html']);
    }

    public function test_asosiy_elementlar(): void
    {
        $html = Markdown::render("Matn **qalin** *kursiv* `kod`\n\n- bir\n- ikki\n\n1. a\n2. b\n\n> iqtibos\n\n---")['html'];

        foreach (['<strong>qalin</strong>', '<em>kursiv</em>', '<code>kod</code>', '<ul><li>bir</li><li>ikki</li></ul>', '<ol><li>a</li><li>b</li></ol>', '<blockquote>iqtibos</blockquote>', '<hr>'] as $part) {
            $this->assertStringContainsString($part, $html);
        }
    }
}
