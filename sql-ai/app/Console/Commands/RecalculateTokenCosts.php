<?php

namespace App\Console\Commands;

use App\Support\Utils\LlmConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateTokenCosts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tokens:recalculate-costs {--chunk=1000}';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate and update cost column in token_usages table';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');

        $this->info("Starting cost recalculation...");
        $this->info("Chunk size: {$chunkSize}");

        $total = DB::table('token_usages')->count();
        $this->info("Total rows: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $pricingCache = [];

        DB::table('token_usages')
            ->orderBy('id')
            ->chunk($chunkSize, function ($rows) use (&$pricingCache, $bar) {

                foreach ($rows as $row) {

                    $provider = $row->provider;
                    $model = $row->model;

                    $inputTokens = $row->input_tokens ?? 0;
                    $outputTokens = $row->output_tokens ?? 0;

                    // Cache pricing (important optimization)
                    $key = $provider . '_' . $model;

                    if (!isset($pricingCache[$key])) {
                        $pricingCache[$key] = LlmConfig::pricing($provider, $model);
                    }

                    $pricing = $pricingCache[$key];

                    $inputCostPer1k = $pricing['input'] ?? 0;
                    $outputCostPer1k = $pricing['output'] ?? 0;

                    // Calculate cost
                    $inputCost = ($inputTokens / 1000) * $inputCostPer1k;
                    $outputCost = ($outputTokens / 1000) * $outputCostPer1k;

                    $cost = round($inputCost + $outputCost, 6);

                    DB::table('token_usages')
                        ->where('id', $row->id)
                        ->update([
                            'cost' => $cost,
                            'updated_at' => now(),
                        ]);

                    $bar->advance();
                }
            });

        $bar->finish();

        $this->newLine(2);
        $this->info("Cost recalculation completed successfully.");

        return Command::SUCCESS;
    }
}