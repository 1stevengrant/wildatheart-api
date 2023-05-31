<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportCampfires extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-campfires';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fires from the Google Sheet';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Importing Campfires...');

        if (cache()->has('googleCampfires')) {
            $campfires = cache()->get('googleCampfires');
        } else {
            $response = Http::get('https://sheetdb.io/api/v1/3o8sfndd339b9');

            $campfires = $response->json();

            cache()->put('googleCampfires', $campfires, now()->addWeek());
        }

        $campfires = collect($campfires);

        $campfires = $campfires->filter(function ($campfire) {
            return $campfire['Primary Contact Email'] !== '';
        });

        $bar = $this->output->createProgressBar(count($campfires));

        $bar->start();

        $campfires->each(function ($campfire) use ($bar) {
            $user = User::create([
                'name' => $campfire['First Name'].' '.$campfire['Last Name'],
                'given_name' => $campfire['First Name'],
                'family_name' => $campfire['Last Name'],
                'email' => $this->sanitizeEmail(($campfire['Primary Contact Email'])),
                'phone' => $campfire['Phone'] !== '' ? $campfire['Phone'] : null,
            ]);

            $user->assignRole(User::ROLE_HOST);

            $newCampfire = $user->campfires()->create([
                'name' => $campfire['Organization (Optional)'] !== '' ? $campfire['Organization (Optional)'] : null,
                'organization' => $campfire['Organization (Optional)'] !== '' ? $campfire['Organization (Optional)'] : null,
                'street_address' => $campfire['Address'] !== '' ? $campfire['Address'] : null,
                'city' => $campfire['City'] !== '' ? $campfire['City'] : null,
                'state' => $campfire['State'] !== '' ? getStateName($campfire['State']) : null,
                'zip' => $campfire['Zip'] !== '' ? $campfire['Zip'] : null,
                'phone' => $campfire['Phone'] !== '' ? $campfire['Phone'] : null,
                'is_private' => ! ($campfire['Public Listing'] === 'x'),
                'approved_at' => now(),
            ]);

            if ($campfire['Books Shipped'] !== '') {
                $newCampfire->update([
                    'book_request_count' => 1,
                ]);
                $newCampfire->save();
                $newCampfire->bookShipments()->create([
                    'created_at' => Carbon::parse($campfire['Books Shipped']),
                    'tracking_number' => $campfire['Tracking'] !== '' ? $campfire['Tracking'] : null,
                ]);
            }

            $bar->advance();

        });

        $bar->finish();

        $this->line('');

        $this->info('All done!');
    }

    protected function sanitizeEmail($email): string
    {
        return strtolower(str_replace(' ', '', $email));
    }
}
