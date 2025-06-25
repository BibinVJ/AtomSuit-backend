<?php

namespace App\Jobs;

use App\Contracts\Stock\CreateStockMovementsActionInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class CreateStockMovementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Model $model
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CreateStockMovementsActionInterface $action): void
    {
        $action->execute($this->model);
    }
}
