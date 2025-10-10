<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class UpdateExistingOrdersWithUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-user-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing orders with created_by_id and updated_by_id fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating existing orders with user IDs...');

        $orders = Order::whereNull('created_by_id')->orWhereNull('updated_by_id')->get();

        if ($orders->isEmpty()) {
            $this->info('No orders need to be updated.');
            return;
        }

        $this->info("Found {$orders->count()} orders to update.");

        $bar = $this->output->createProgressBar($orders->count());

        foreach ($orders as $order) {
            // Use the user_id as created_by_id if created_by_id is null
            if (is_null($order->created_by_id) && !is_null($order->user_id)) {
                $order->created_by_id = $order->user_id;
            }

            // Use the user_id as updated_by_id if updated_by_id is null
            if (is_null($order->updated_by_id) && !is_null($order->user_id)) {
                $order->updated_by_id = $order->user_id;
            }

            $order->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All orders have been updated successfully!');
    }
}
