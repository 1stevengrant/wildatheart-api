<?php

namespace App\Services\Subsplash;

use Statamic\Facades\Term;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SubsplashTopicService
{
    /**
     * Extract topics from a Subsplash tags array and create/find topic taxonomy entries
     *
     * @param  array  $tags  The tags array from Subsplash MediaItem
     * @return array Array of topic taxonomy term IDs
     */
    public function processFromTags(array $tags): array
    {
        $topicIds = [];

        foreach ($tags as $tag) {
            if (str_starts_with($tag, 'topic:')) {
                $topicName = mb_trim(mb_substr($tag, 6));

                if (! empty($topicName)) {
                    $topicTerm = $this->findOrCreate($topicName);
                    if ($topicTerm) {
                        $topicIds[] = $topicTerm->id();
                    }
                }
            }
        }

        return $topicIds;
    }

    /**
     * Create or find a topic taxonomy term
     */
    public function findOrCreate(string $topicName)
    {
        $slug = Str::slug($topicName);

        $existingTerm = Term::query()
            ->where('taxonomy', 'topics')
            ->where('slug', $slug)
            ->first();

        if ($existingTerm) {
            return $existingTerm;
        }

        try {
            $term = Term::make()
                ->taxonomy('topics')
                ->slug($slug)
                ->data([
                    'title' => $topicName,
                ]);

            $term->save();

            return $term;
        } catch (\Exception $e) {
            Log::warning('Failed to create topic term: ' . $e->getMessage(), [
                'topic_name' => $topicName,
                'slug' => $slug,
            ]);

            return null;
        }
    }
}
