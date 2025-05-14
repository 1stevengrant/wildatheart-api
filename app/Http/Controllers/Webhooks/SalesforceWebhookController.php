<?php

namespace App\Http\Controllers\Webhooks;

use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Salesforce\HandleRecordDelete;
use App\Jobs\Salesforce\HandleRecordInsert;
use App\Jobs\Salesforce\HandleRecordUpdate;

class SalesforceWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $providedSignature = $request->header('X-SF-Signature');
        $expectedSignature = hash_hmac('sha256', $payload, config('services.salesforce.webhook_secret'));

        if (! hash_equals($expectedSignature, $providedSignature)) {

            dispatch(new LogActivityJob(
                logName: 'salesforce',
                description: 'signature mismatch',
                properties: [
                    'expected' => $expectedSignature,
                    'provided' => $providedSignature,
                ],
                event: 'signature mismatch',
            ));
            abort(403, 'Invalid signature');
        }

        $data = json_decode($payload, true);
        $operationType = $data[0]['operationType'] ?? null;

        match (mb_strtolower($operationType)) {
            'insert' => HandleRecordInsert::dispatch($data[0]),
            'update' => HandleRecordUpdate::dispatch($data[0]),
            'delete' => HandleRecordDelete::dispatch($data[0]),
            default => abort(400, 'Invalid operation type'),
        };

        dispatch(new LogActivityJob(
            logName: 'salesforce',
            description: 'handle salesforce webhook',
            properties: ['data' => $data[0]],
        ));

        return response()->json(['ok' => true]);
    }
}
