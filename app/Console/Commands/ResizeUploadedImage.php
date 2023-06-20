<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ResizeUploadedImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resize-uploaded-image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resizes the uploaded image and stores it in the optimized_image column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resizing uploaded image...');

        $users = \App\Models\User::query()
            ->whereNotNull('image')
            ->whereNull('optimized_image')
            ->get();

        $bar = $this->output->createProgressBar(count($users));

        foreach ($users as $user) {
            $image = Storage::get($user->image);
            $image = Image::make($image)->fit(100, 100, function ($constraint) {
                $constraint->upsize();
            });
            $imageName = $user->uuid.'.png';
            $image->save('/tmp/'.$imageName);

            Storage::put('users/'.$imageName, $image, 'public');

            unlink('/tmp/'.$imageName);
            $user->update(['optimized_image' => 'users/'.$imageName]);
            $bar->advance();
        }

        $bar->finish();

        $this->info('');
        $this->info('Done!');
    }
}
