<?php

use App\Support\Markdown;

it('escapes raw html', function () {
    $html = Markdown::render("<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>")['html'];

    expect($html)->not->toContain('<script')
        ->not->toContain('<img')
        ->toContain('&lt;script&gt;');
});

it('strips dangerous links and keeps safe ones', function () {
    $html = Markdown::render('[a](javascript:alert) [b](//evil.uz) [c](data:text/html;base64,AAA) [d](https://ok.uz) [e](/post/x) [f](#bolim)')['html'];

    expect($html)->not->toContain('javascript:')
        ->not->toContain('//evil.uz')
        ->not->toContain('data:')
        ->toContain('href="https://ok.uz"')
        ->toContain('rel="noopener noreferrer"')
        ->toContain('href="/post/x"')
        ->toContain('href="#bolim"');
});

it('allows only https images and escapes attributes', function () {
    $html = Markdown::render("![x\"onerror=\"a](https://a.uz/i.png)\n![j](javascript:alert(1))")['html'];

    expect($html)->toContain('<img src="https://a.uz/i.png"')
        ->not->toContain('"onerror="')
        ->not->toContain('src="javascript');
});

it('renders escaped code blocks with a safe language class', function () {
    $html = Markdown::render("```php\n<?php echo \"<b>\";\n```")['html'];

    expect($html)->toContain('<pre><code class="language-php">')
        ->toContain('&lt;?php echo &quot;&lt;b&gt;&quot;;');
});

it('drops unsafe language names from code blocks', function () {
    $html = Markdown::render("```\"><script>x\ncode\n```")['html'];

    expect($html)->not->toContain('<script')->toContain('<pre><code>');
});

it('gives headings unique ids and collects the table of contents', function () {
    $result = Markdown::render("## Oʻzimga eslatma\n\n### Kichik\n\n## Oʻzimga eslatma");

    expect(array_column($result['headings'], 'id'))->toBe(['ozimga-eslatma', 'kichik', 'ozimga-eslatma-2'])
        ->and(array_column($result['headings'], 'level'))->toBe([2, 3, 2])
        ->and($result['html'])->toContain('<h2 id="ozimga-eslatma-2">');
});

it('renders the basic elements', function () {
    $html = Markdown::render("Matn **qalin** *kursiv* `kod`\n\n- bir\n- ikki\n\n1. a\n2. b\n\n> iqtibos\n\n---")['html'];

    foreach (['<strong>qalin</strong>', '<em>kursiv</em>', '<code>kod</code>', '<ul><li>bir</li><li>ikki</li></ul>', '<ol><li>a</li><li>b</li></ol>', '<blockquote>iqtibos</blockquote>', '<hr>'] as $part) {
        expect($html)->toContain($part);
    }
});

it('builds a short plain excerpt', function () {
    $excerpt = Markdown::excerpt("# Sarlavha\n\n**Qalin** matn [havola](https://a.uz)\n\n```php\nkod\n```");

    expect($excerpt)->toBe('Sarlavha Qalin matn havola');
});
