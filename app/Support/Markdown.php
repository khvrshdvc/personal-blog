<?php

namespace App\Support;

class Markdown
{
    private const BLOCK = '/^(```|#{1,3}\s|>\s?|[-*]\s|\d+\.\s|!\[[^\]]*\]\(|---+\s*$)/';

    private array $ids = [];

    private array $headings = [];

    public static function render(?string $source): array
    {
        $renderer = new self();
        $html = $renderer->blocks((string) $source);

        return ['html' => $html, 'headings' => $renderer->headings];
    }

    public static function excerpt(?string $body): string
    {
        $text = (string) $body;
        $text = preg_replace('/```[\s\S]*?```/u', ' ', $text);
        $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/u', ' ', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]*\)/u', '$1', $text);
        $text = preg_replace('/[#>*`_]/u', '', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if (mb_strlen($text) <= 160) {
            return $text;
        }

        return preg_replace('/\s+\S*$/u', '', mb_substr($text, 0, 157)).'…';
    }

    public static function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[ʻʼ‘’\'`]/u', '', $text);
        $text = trim(preg_replace('/[^a-z0-9]+/', '-', $text), '-');

        return substr($text, 0, 80);
    }

    private function blocks(string $source): string
    {
        $lines = explode("\n", str_replace("\r", '', $source));
        $count = count($lines);
        $html = '';
        $i = 0;

        while ($i < $count) {
            $line = $lines[$i];

            if (str_starts_with($line, '```')) {
                $lang = trim(substr($line, 3));
                $buffer = [];
                $i++;
                while ($i < $count && ! str_starts_with($lines[$i], '```')) {
                    $buffer[] = $lines[$i];
                    $i++;
                }
                $i++;
                $html .= $this->code($lang, implode("\n", $buffer));

                continue;
            }

            if (preg_match('/^#{1,3}\s/', $line)) {
                $level = preg_match('/^###\s/', $line) ? 3 : 2;
                $text = preg_replace('/^#+\s/', '', $line);
                $id = $this->uniqueId($text);
                $rendered = $this->inline($text);
                $this->headings[] = [
                    'id' => $id,
                    'level' => $level,
                    'text' => html_entity_decode(strip_tags($rendered), ENT_QUOTES),
                ];
                $html .= "<h{$level} id=\"{$id}\">{$rendered}</h{$level}>";
                $i++;

                continue;
            }

            if (preg_match('/^!\[([^\]]*)\]\((https?:\/\/[^)\s]+)\)\s*$/', $line, $m)) {
                $caption = $m[1] !== '' ? '<figcaption>'.$this->e($m[1]).'</figcaption>' : '';
                $html .= '<figure><img src="'.$this->e($m[2]).'" alt="'.$this->e($m[1]).'" loading="lazy">'.$caption.'</figure>';
                $i++;

                continue;
            }

            if (preg_match('/^---+\s*$/', $line)) {
                $html .= '<hr>';
                $i++;

                continue;
            }

            if (preg_match('/^>\s?/', $line)) {
                $buffer = [];
                while ($i < $count && preg_match('/^>\s?/', $lines[$i])) {
                    $buffer[] = preg_replace('/^>\s?/', '', $lines[$i]);
                    $i++;
                }
                $html .= '<blockquote>'.$this->inline(implode(' ', $buffer)).'</blockquote>';

                continue;
            }

            if (preg_match('/^[-*]\s/', $line)) {
                $items = '';
                while ($i < $count && preg_match('/^[-*]\s/', $lines[$i])) {
                    $items .= '<li>'.$this->inline(preg_replace('/^[-*]\s/', '', $lines[$i])).'</li>';
                    $i++;
                }
                $html .= "<ul>{$items}</ul>";

                continue;
            }

            if (preg_match('/^\d+\.\s/', $line)) {
                $items = '';
                while ($i < $count && preg_match('/^\d+\.\s/', $lines[$i])) {
                    $items .= '<li>'.$this->inline(preg_replace('/^\d+\.\s/', '', $lines[$i])).'</li>';
                    $i++;
                }
                $html .= "<ol>{$items}</ol>";

                continue;
            }

            if (trim($line) === '') {
                $i++;

                continue;
            }

            $buffer = [$line];
            $i++;
            while ($i < $count && trim($lines[$i]) !== '' && ! preg_match(self::BLOCK, $lines[$i])) {
                $buffer[] = $lines[$i];
                $i++;
            }
            $html .= '<p>'.$this->inline(implode(' ', $buffer)).'</p>';
        }

        return $html;
    }

    private function code(string $lang, string $body): string
    {
        $label = $lang !== '' ? $lang : 'kod';
        $icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.config('icons.copy').'</svg>';

        return '<div class="code-wrap"><div class="code-bar"><span>'.$this->e($label).'</span><button type="button" class="copy-code">'.$icon.'<span>Nusxalash</span></button></div>'
            .'<pre data-lang="'.$this->e($lang).'"><code>'.$this->e($body).'</code></pre></div>';
    }

    private function inline(string $text): string
    {
        $text = $this->e($text);
        $codes = [];

        $text = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$codes) {
            $codes[] = $m[1];

            return "\0".(count($codes) - 1)."\0";
        }, $text);

        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/(^|[^*])\*([^*\s][^*]*)\*/', '$1<em>$2</em>', $text);

        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
            [, $label, $url] = $m;

            if (! preg_match('#^(https?://|/(?!/)|\#)#i', $url)) {
                return $label;
            }

            $external = preg_match('#^https?:#i', $url) ? ' target="_blank" rel="noopener noreferrer"' : '';

            return '<a href="'.$url.'"'.$external.'>'.$label.'</a>';
        }, $text);

        return preg_replace_callback('/\0(\d+)\0/', fn ($m) => '<code>'.$codes[(int) $m[1]].'</code>', $text);
    }

    private function uniqueId(string $text): string
    {
        $base = self::slugify($text) ?: 'bolim';
        $id = $base;
        $n = 2;

        while (isset($this->ids[$id])) {
            $id = $base.'-'.$n++;
        }

        $this->ids[$id] = true;

        return $id;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
