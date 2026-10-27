<?php

namespace App\Actions\ActivityLog;

use Closure;
use RuntimeException;
use App\Models\Activity;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Moves activity older than a given moment into gzipped NDJSON files (one or
 * more per month) and deletes the archived rows in id-bounded batches.
 *
 * The activity log has no index on created_at, so month boundaries are found
 * by bisecting the primary key, relying on ids growing with created_at.
 */
class ArchiveActivityLog
{
    use AsAction;

    /**
     * @param  ?Closure(string, int, int): void  $beforeMonth
     */
    public function handle(CarbonInterface $before, string $disk, int $chunkSize = 5000, ?Closure $beforeMonth = null): int
    {
        $lastArchivableId = $this->lastIdCreatedBefore($before);

        if ($lastArchivableId === null) {
            return 0;
        }

        $archived = 0;
        $firstId = $this->query()->min('id');

        while ($firstId !== null && $firstId <= $lastArchivableId) {
            $month = CarbonImmutable::parse($this->query()->where('id', $firstId)->value('created_at'))->startOfMonth();

            $lastId = max($firstId, min($lastArchivableId, $this->lastIdCreatedBefore($month->addMonth()) ?? $firstId));

            if ($beforeMonth) {
                $beforeMonth($month->format('Y-m'), $firstId, $lastId);
            }

            $archived += $this->archiveRange($firstId, $lastId, $month, $disk, $chunkSize);

            $firstId = $this->query()->where('id', '>', $lastId)->min('id');
        }

        return $archived;
    }

    private function archiveRange(int $firstId, int $lastId, CarbonImmutable $month, string $disk, int $chunkSize): int
    {
        $archive = fopen('php://temp', 'w+b');

        try {
            $written = $this->writeRangeAsGzip($firstId, $lastId, $archive, $chunkSize);

            $path = "activity-log-archive/{$month->format('Y')}/{$month->format('Y-m')}/{$firstId}-{$lastId}.ndjson.gz";

            $this->upload($archive, $path, $disk);
        } finally {
            fclose($archive);
        }

        for ($start = $firstId; $start <= $lastId; $start += $chunkSize) {
            $this->query()
                ->whereBetween('id', [$start, min($start + $chunkSize - 1, $lastId)])
                ->delete();
        }

        return $written;
    }

    /**
     * @param  resource  $archive
     */
    private function writeRangeAsGzip(int $firstId, int $lastId, $archive, int $chunkSize): int
    {
        $deflate = deflate_init(ZLIB_ENCODING_GZIP, ['level' => 6]);
        $written = 0;

        $this->query()
            ->whereBetween('id', [$firstId, $lastId])
            ->lazyById($chunkSize)
            ->each(function (object $row) use ($archive, $deflate, &$written): void {
                if (is_string($row->properties) && json_validate($row->properties)) {
                    $row->properties = json_decode($row->properties);
                }

                $line = json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";

                fwrite($archive, deflate_add($deflate, $line, ZLIB_NO_FLUSH));
                $written++;
            });

        fwrite($archive, deflate_add($deflate, '', ZLIB_FINISH));

        return $written;
    }

    /**
     * @param  resource  $archive
     */
    private function upload($archive, string $path, string $disk): void
    {
        $size = fstat($archive)['size'];

        rewind($archive);

        if (! Storage::disk($disk)->writeStream($path, $archive, ['visibility' => 'private'])) {
            throw new RuntimeException("Could not store the activity log archive at {$path}.");
        }

        if (Storage::disk($disk)->size($path) !== $size) {
            throw new RuntimeException("The stored activity log archive at {$path} does not match the local file.");
        }
    }

    private function lastIdCreatedBefore(CarbonInterface $moment): ?int
    {
        $low = 0;
        $high = (int) $this->query()->max('id');

        while ($low < $high) {
            $middle = intdiv($low + $high + 1, 2);

            $row = $this->query()->where('id', '>=', $middle)->orderBy('id')->first(['id', 'created_at']);

            if ($row->created_at < $moment->toDateTimeString()) {
                $low = $row->id;

                continue;
            }

            $high = $middle - 1;
        }

        return $low === 0 ? null : $low;
    }

    private function query(): Builder
    {
        return Activity::query()->toBase();
    }
}
