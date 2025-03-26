<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateRedirectsFromDrupal extends Command
{
    protected $signature = 'app:generate-redirects';

    protected $description = 'Generates redirects from Drupal';

    public function handle()
    {

        $response = Http::get('https://wildatheart.org/json/podcasts');
        $data = $response->json();

        $onlyPodcasts = collect($data)->filter(function ($item) {
            $url = data_get($item, 'url');

            return ! preg_match("/\/rhplay\/[^\/]+$/", $url)
                && ! str_contains($url, 'rhplay/video')
                && ! str_contains($url, 'rhplay/audio');
        });

        if (! $data) {
            $this->error('Failed to fetch podcast data');

            return 1;
        }

        $fileName = $this->getFileName('podcast_redirects.txt');

        Storage::makeDirectory('redirects');

        $missingEntries = [];

        // Discovered a few things that were imported and then deleted.
        $ignoreRoutes = [
            '/psa-back-next-week',
            "John Eldredge Explains Why Understanding Isn't Healing and how Jesus Is Integrating Force of the Universe", // changed title
            '/140-marriage-self-care-and-deeper-questions', // AS episode that appears to have been deleted
        ];

        $fallbackRoutes = [

            '/rhplay/podcast/wild-heart/809-john-eldredge-answers-your-questions-part-2' => '/podcasts/wild-at-heart/e809-john-eldredge-answers-your-questions-part-2',
        ];

        foreach ($onlyPodcasts as $item) {
            $url = data_get($item, 'url');

            $line = $url;
            $line .= ' ';

            $title = data_get($item, 'title');
            $slug = Str::slug($title);
            $entry = Entry::query()->where('slug', $slug)->first();

            if ($entry) {
                $entryTitle = $entry->title;
                $this->info("Found $entryTitle");
                $line .= $entry->uri;
                $line .= ' 301';
                Storage::append($fileName, $line);
            } elseif (in_array($url, $ignoreRoutes)) {
                $this->info("Skipping $title");
            } elseif ($fallback = data_get($fallbackRoutes, $url)) {
                $line .= $fallback;
                $line .= ' 301';
            } else {
                $this->error("Could not find $title");
                $missingEntries[] = $title;
            }
        }

        foreach ($missingEntries as $entry) {
            $this->error($entry);
        }

        $this->info("Written to $fileName");
    }

    private function getFileName(string $fileName)
    {
        $fileDate = now()->format('Ymd');
        $fileDateMillis = round(microtime(true) * 1000);

        return "{$fileDate}_{$fileDateMillis}_{$fileName}";
    }

    private function create_pathauto_slug($string, $options = [])
    {
        // Default options matching Pathauto's defaults
        $defaults = [
            'separator' => '-',
            'transliterate' => false,
            'reduce_ascii' => false,
            'lowercase' => true,
            'maxlength' => 100,
            'ignore_words' => 'a, an, as, at, before, but, by, for, from, in, into, like, of, off, on, over, plus, since, than, the, this, that, to, up, via, with',
        ];

        $options = array_merge($defaults, $options);

        // Empty strings return empty
        if (empty($string)) {
            return '';
        }

        // Remove HTML tags and decode HTML entities
        $output = strip_tags(html_entity_decode($string));

        // Transliterate if enabled (requires Transliteration module)
        if ($options['transliterate']) {
            // Note: This would require the Transliteration module
            // $output = transliteration_get($output, $options['reduce_ascii'] ? '' : '?');
        }

        // Replace punctuation with separator
        $punctuation = [
            ' ' => $options['separator'],
            '_' => $options['separator'],
            '/' => $options['separator'],
            '\\' => $options['separator'],
            '#' => $options['separator'],
            '?' => $options['separator'],
            ':' => $options['separator'],
            ';' => $options['separator'],
            '.' => $options['separator'],
            ',' => $options['separator'],
            '!' => $options['separator'],
            '*' => $options['separator'],
            '|' => $options['separator'],
            '"' => $options['separator'],
            "'" => $options['separator'],
            '(' => $options['separator'],
            ')' => $options['separator'],
            '[' => $options['separator'],
            ']' => $options['separator'],
            '{' => $options['separator'],
            '}' => $options['separator'],
            '<' => $options['separator'],
            '>' => $options['separator'],
            '&' => $options['separator'],
            '+' => $options['separator'],
            '=' => $options['separator'],
            '@' => $options['separator'],
            '§' => $options['separator'],
            '$' => $options['separator'],
            '%' => $options['separator'],
            '£' => $options['separator'],
            '€' => $options['separator'],
            '©' => $options['separator'],
            '®' => $options['separator'],
            '™' => $options['separator'],
            '¶' => $options['separator'],
            '†' => $options['separator'],
            '‡' => $options['separator'],
            '•' => $options['separator'],
            '¤' => $options['separator'],
            '¢' => $options['separator'],
            '∞' => $options['separator'],
            '≠' => $options['separator'],
            '≈' => $options['separator'],
            '∆' => $options['separator'],
            '∏' => $options['separator'],
            '∑' => $options['separator'],
            '√' => $options['separator'],
            '∂' => $options['separator'],
            'µ' => $options['separator'],
            '∞' => $options['separator'],
            '∅' => $options['separator'],
            '∈' => $options['separator'],
            '∉' => $options['separator'],
            '∋' => $options['separator'],
            '∌' => $options['separator'],
            '⊂' => $options['separator'],
            '⊃' => $options['separator'],
            '⊄' => $options['separator'],
            '⊅' => $options['separator'],
            '∪' => $options['separator'],
            '∩' => $options['separator'],
            '∨' => $options['separator'],
            '∧' => $options['separator'],
            '⊕' => $options['separator'],
            '⊗' => $options['separator'],
            '⊥' => $options['separator'],
            '⊤' => $options['separator'],
            '⊢' => $options['separator'],
            '⊨' => $options['separator'],
            '⊩' => $options['separator'],
            '⊪' => $options['separator'],
            '⊫' => $options['separator'],
            '⊬' => $options['separator'],
            '⊭' => $options['separator'],
            '⊮' => $options['separator'],
            '⊯' => $options['separator'],
            '⊰' => $options['separator'],
            '⊱' => $options['separator'],
            '⊲' => $options['separator'],
            '⊳' => $options['separator'],
            '⊴' => $options['separator'],
            '⊵' => $options['separator'],
            '⊶' => $options['separator'],
            '⊷' => $options['separator'],
            '⊸' => $options['separator'],
            '⊹' => $options['separator'],
            '⊺' => $options['separator'],
            '⊻' => $options['separator'],
            '⊼' => $options['separator'],
            '⊽' => $options['separator'],
            '⊾' => $options['separator'],
            '⊿' => $options['separator'],
            '⋀' => $options['separator'],
            '⋁' => $options['separator'],
            '⋂' => $options['separator'],
            '⋃' => $options['separator'],
            '⋄' => $options['separator'],
            '⋅' => $options['separator'],
            '⋆' => $options['separator'],
            '⋇' => $options['separator'],
            '⋈' => $options['separator'],
            '⋉' => $options['separator'],
            '⋊' => $options['separator'],
            '⋋' => $options['separator'],
            '⋌' => $options['separator'],
            '⋍' => $options['separator'],
            '⋎' => $options['separator'],
            '⋏' => $options['separator'],
            '⋐' => $options['separator'],
            '⋑' => $options['separator'],
            '⋒' => $options['separator'],
            '⋓' => $options['separator'],
            '⋔' => $options['separator'],
            '⋕' => $options['separator'],
            '⋖' => $options['separator'],
            '⋗' => $options['separator'],
            '⋘' => $options['separator'],
            '⋙' => $options['separator'],
            '⋚' => $options['separator'],
            '⋛' => $options['separator'],
            '⋜' => $options['separator'],
            '⋝' => $options['separator'],
            '⋞' => $options['separator'],
            '⋟' => $options['separator'],
            '⋠' => $options['separator'],
            '⋡' => $options['separator'],
            '⋢' => $options['separator'],
            '⋣' => $options['separator'],
            '⋤' => $options['separator'],
            '⋥' => $options['separator'],
            '⋦' => $options['separator'],
            '⋧' => $options['separator'],
            '⋨' => $options['separator'],
            '⋩' => $options['separator'],
            '⋪' => $options['separator'],
            '⋫' => $options['separator'],
            '⋬' => $options['separator'],
            '⋭' => $options['separator'],
            '⋮' => $options['separator'],
            '⋯' => $options['separator'],
            '⋰' => $options['separator'],
            '⋱' => $options['separator'],
            '⋲' => $options['separator'],
            '⋳' => $options['separator'],
            '⋴' => $options['separator'],
            '⋵' => $options['separator'],
            '⋶' => $options['separator'],
            '⋷' => $options['separator'],
            '⋸' => $options['separator'],
            '⋹' => $options['separator'],
            '⋺' => $options['separator'],
            '⋻' => $options['separator'],
            '⋼' => $options['separator'],
            '⋽' => $options['separator'],
            '⋾' => $options['separator'],
            '⋿' => $options['separator'],
        ];

        $output = strtr($output, $punctuation);

        // Reduce to ASCII if enabled
        if ($options['reduce_ascii']) {
            $output = preg_replace('/[^a-zA-Z0-9\/]+/', $options['separator'], $output);
        }

        // Remove ignored words
        if (! empty($options['ignore_words'])) {
            $ignore_words = explode(',', $options['ignore_words']);
            $ignore_words = array_map('trim', $ignore_words);
            $ignore_pattern = '/\b(' . implode('|', array_map('preg_quote', $ignore_words)) . ')\b/i';
            $output = preg_replace($ignore_pattern, '', $output);
        }

        // Replace multiple separators with single separator
        $output = preg_replace('/' . preg_quote($options['separator'], '/') . '+/', $options['separator'], $output);

        // Convert to lowercase if enabled
        if ($options['lowercase']) {
            $output = mb_strtolower($output);
        }

        // Trim separators from beginning and end
        $output = mb_trim($output, $options['separator']);

        // Truncate to max length
        if (mb_strlen($output) > $options['maxlength']) {
            $output = mb_substr($output, 0, $options['maxlength']);
            // Find the last separator and truncate there
            $last_separator = mb_strrpos($output, $options['separator']);
            if ($last_separator !== false) {
                $output = mb_substr($output, 0, $last_separator);
            }
        }

        return $output;
    }
}
