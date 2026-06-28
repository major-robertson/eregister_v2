<?php

namespace App\Domains\Esign\Console;

use App\Domains\Esign\Actions\VerifySignatureChain;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Console\Command;

class VerifyEsignChain extends Command
{
    protected $signature = 'esign:verify-chain {request : The signature request public_id}';

    protected $description = 'Verify the integrity of a signature request audit-trail hash chain.';

    public function handle(VerifySignatureChain $verifier): int
    {
        $request = SignatureRequest::query()
            ->where('public_id', $this->argument('request'))
            ->first();

        if ($request === null) {
            $this->error('Signature request not found.');

            return self::FAILURE;
        }

        $result = $verifier->execute($request);

        if ($result->valid) {
            $this->info("Audit chain valid — {$result->eventCount} events, intact.");

            return self::SUCCESS;
        }

        $this->error("Audit chain INVALID at event #{$result->brokenAtEventId}: {$result->reason}");

        return self::FAILURE;
    }
}
