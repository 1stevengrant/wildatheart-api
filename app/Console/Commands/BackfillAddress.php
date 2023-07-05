<?php

namespace App\Console\Commands;

use App\Models\Campfire;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BackfillAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-address';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill the street_address column in addresses table';

    public function handle(): void
    {
        $campfires = Campfire::all();

        $orders = $this->getOrders();

        $bar = $this->output->createProgressBar($campfires->count());

        $campfires->each(function ($campfire) use ($orders, $bar) {
            $this->info('');
            $this->info("Processing {$campfire->name} Fire");

            foreach ($orders as $order) {
                if ($order['Email'] === $campfire->email && $campfire->address) {

                    $this->info("Updating {$campfire->name} Fire");

                    $campfire->address->update([
                        'street_address' => $order['Address 1'],
                        'address2' => $order['Address 2'],
                        'city' => $order['City'],
                        'state' => $order['State'],
                        'zip' => $order['ZIP'],
                    ]);

                    $this->info("Updated {$campfire->name} Fire");
                }
            }
            $bar->advance();
        });

        $bar->finish();

    }

    protected function getOrders()
    {
        return Http::get('https://sheetdb.io/api/v1/py5bleo0g6vlm')->json();
    }
}
