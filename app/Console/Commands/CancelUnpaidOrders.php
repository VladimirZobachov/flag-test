<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-unpaid-orders';

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
        $orders = Order::where('status', 'На оплату')
            ->where('created_at', '<', now()->subMinutes(2))
            ->update(['status' => 'Отменен']);
    }
}
