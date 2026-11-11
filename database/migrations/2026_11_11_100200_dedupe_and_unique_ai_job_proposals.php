<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Highest priority wins when collapsing duplicates.
     */
    protected array $statusPriority = [
        'failed' => 1,
        'pending' => 2,
        'queued' => 3,
        'generating' => 4,
        'completed' => 5,
    ];

    public function up(): void
    {
        $this->removeDuplicateProposals();

        Schema::table('ai_job_proposals', function (Blueprint $table) {
            $table->unique(
                ['job_id', 'provider'],
                'ai_job_proposals_job_provider_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ai_job_proposals', function (Blueprint $table) {
            $table->dropUnique('ai_job_proposals_job_provider_unique');
        });
    }

    protected function removeDuplicateProposals(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                DELETE duplicate
                FROM ai_job_proposals AS duplicate
                INNER JOIN ai_job_proposals AS keeper
                    ON duplicate.job_id = keeper.job_id
                   AND duplicate.provider = keeper.provider
                   AND (
                        FIELD(keeper.status, 'failed', 'pending', 'queued', 'generating', 'completed')
                            > FIELD(duplicate.status, 'failed', 'pending', 'queued', 'generating', 'completed')
                        OR (
                            keeper.status = duplicate.status
                            AND keeper.id > duplicate.id
                        )
                   )
            SQL);

            return;
        }

        $duplicates = DB::table('ai_job_proposals')
            ->select('job_id', 'provider')
            ->groupBy('job_id', 'provider')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $pair) {
            $rows = DB::table('ai_job_proposals')
                ->where('job_id', $pair->job_id)
                ->where('provider', $pair->provider)
                ->get(['id', 'status']);

            $keep = $rows->sortByDesc(fn ($row) => [
                $this->statusPriority[$row->status] ?? 0,
                $row->id,
            ])->first();

            DB::table('ai_job_proposals')
                ->where('job_id', $pair->job_id)
                ->where('provider', $pair->provider)
                ->where('id', '!=', $keep->id)
                ->delete();
        }
    }
};
