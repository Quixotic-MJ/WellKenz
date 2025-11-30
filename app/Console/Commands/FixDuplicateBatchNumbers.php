<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixDuplicateBatchNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batches:fix-duplicates {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate batch numbers by generating unique ones for problematic records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning for problematic batch numbers...');

        // Find all batches with problematic patterns
        $problematicBatches = Batch::where(function ($query) {
                $query->where('batch_number', 'LIKE', 'N/A-%')
                      ->orWhere('batch_number', 'LIKE', 'NA-%-%')
                      ->orWhere('batch_number', '=', 'N/A-251130');
            })
            ->orderBy('id')
            ->get();

        if ($problematicBatches->isEmpty()) {
            $this->info('✅ No problematic batch numbers found!');
            return 0;
        }

        $this->warn("Found {$problematicBatches->count()} problematic batch records:");
        $this->table(
            ['ID', 'Current Batch Number', 'Item ID', 'Quantity', 'Created At'],
            $problematicBatches->map(function ($batch) {
                return [
                    $batch->id,
                    $batch->batch_number,
                    $batch->item_id,
                    $batch->quantity,
                    $batch->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        );

        // Check for duplicates
        $duplicates = $problematicBatches->groupBy('batch_number')
            ->filter(function ($group) {
                return $group->count() > 1;
            });

        if ($duplicates->isNotEmpty()) {
            $this->error('❌ Found duplicate batch numbers:');
            foreach ($duplicates as $batchNumber => $group) {
                $this->line("   {$batchNumber}: " . $group->count() . " records (IDs: " . $group->pluck('id')->implode(', ') . ")");
            }
            $this->line('');
        }

        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            
            $this->info('📝 Proposed new batch numbers:');
            foreach ($problematicBatches as $batch) {
                $newBatchNumber = $this->generateUniqueBatchNumber($batch);
                $this->line("   ID {$batch->id}: {$batch->batch_number} → {$newBatchNumber}");
            }
            
            $this->info("💡 Run without --dry-run to apply these changes");
            return 0;
        }

        // Confirm with user
        if (!$this->confirm('Do you want to proceed with fixing these batch numbers?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('🔧 Fixing batch numbers...');
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($problematicBatches as $batch) {
                $newBatchNumber = $this->generateUniqueBatchNumber($batch);
                
                // Double-check the new number doesn't exist
                $exists = Batch::where('batch_number', $newBatchNumber)->exists();
                if ($exists) {
                    $errors[] = "Generated batch number {$newBatchNumber} already exists for batch ID {$batch->id}";
                    continue;
                }

                $oldBatchNumber = $batch->batch_number;
                $batch->batch_number = $newBatchNumber;
                $batch->save();

                // Update related stock movements
                DB::table('stock_movements')
                    ->where('batch_id', $batch->id)
                    ->update(['batch_number' => $newBatchNumber]);

                $updated++;
                $this->line("   ✅ ID {$batch->id}: {$oldBatchNumber} → {$newBatchNumber}");
            }

            DB::commit();

            if (!empty($errors)) {
                $this->warn('⚠️  Some errors occurred:');
                foreach ($errors as $error) {
                    $this->error("   {$error}");
                }
            }

            $this->info("🎉 Successfully updated {$updated} batch records!");
            
            Log::info('Batch numbers fixed', [
                'updated_count' => $updated,
                'errors' => $errors,
                'user_id' => auth()->id() ?? null
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ Error during update: ' . $e->getMessage());
            Log::error('Batch number fix failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Generate a unique batch number for a given batch
     */
    private function generateUniqueBatchNumber(Batch $batch): string
    {
        $date = $batch->created_at->format('Ymd');
        $itemId = str_pad($batch->item_id, 6, '0', STR_PAD_LEFT);
        $batchId = str_pad($batch->id, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(uniqid(), -4));
        
        return "BATCH-{$itemId}-{$date}-{$batchId}{$random}";
    }
}