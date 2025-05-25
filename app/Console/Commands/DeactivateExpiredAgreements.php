<?php

namespace App\Console\Commands;

use App\Models\Agreement;
use Illuminate\Console\Command;

class DeactivateExpiredAgreements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deactivate-expired-agreements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredAgreements = Agreement::where('status', 'active')
            ->where('end_date', '<', now())
            ->get();

        foreach ($expiredAgreements as $agreement) {
            $agreement->update(['status' => 'inactive']);

            foreach ($agreement->roomShops as $room) {
                $room->update([
                    'availability' => 1,
                    'customer_id' => null
                ]);
            }
        }

        $this->info('Expired agreements deactivated.');
    }

}
