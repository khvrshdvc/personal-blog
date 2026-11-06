<?php

namespace App\Support;

class RepoName
{
    public static function normalize(?string $repo): string
    {
        $repo = trim((string) $repo);
        $repo = preg_replace('#^https?://(www\.)?github\.com/#i', '', $repo);
        $repo = preg_replace('#\.git$#', '', $repo);
        $repo = trim($repo, '/');

        return str_contains($repo, '/') ? implode('/', array_slice(explode('/', $repo), 0, 2)) : $repo;
    }

    public static function username(?string $value): string
    {
        $value = trim((string) $value);
        $value = ltrim($value, '@');
        $value = preg_replace('#^https?://(www\.)?github\.com/#i', '', $value);

        return preg_replace('#/.*$#', '', $value);
    }

    public static function telegram(?string $value): string
    {
        $value = trim((string) $value);
        $value = ltrim($value, '@');
        $value = preg_replace('#^(https?://)?(www\.)?t\.me/#i', '', $value);

        return preg_replace('#/.*$#', '', $value);
    }
}
