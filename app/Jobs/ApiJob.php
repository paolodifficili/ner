<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


use App\Models\CodaJob;

use Carbon\Carbon;

class ApiJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 10;
    protected $jobInfo;
    protected $uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jI)
    {
        Log::debug('ApiJob__construct!', [$jI] );
        $this->jobInfo = $jI;
        $this->uuid = Str::uuid();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        Log::debug('ApiJob->handle:', [$this->job->uuid() ] );

        $this->jobInfo['__UUID__'] = $this->job->uuid();

        Log::debug('ApiJob->info:', [$this->jobInfo] );

        $coda = CodaJob::firstOrCreate([
            'job_uuid' => $this->job->uuid(),
            'uuid_internal' => $this->uuid,
            'description' => $this->jobInfo['description'],
            'type' => $this->jobInfo['type'],
            'file' => $this->jobInfo['file'],
            'root_folder' => $this->jobInfo['root_folder'],
            'api_url' => $this->jobInfo['api_url'],
            'status_url' => $this->jobInfo['status_url'],
        ]);

        $city = "Rome";
        $apiKey = 'la-tua-api-key';


        // $url = "https://dummy.restapiexample.com/api/v1/employee/1";
        $status_url = $this->jobInfo['status_url'];
        $api_url = $this->jobInfo['api_url'];


        Log::debug('ApiJob->check_url:with no verify!', [$status_url] );
          
        // CALL URL
        $response = Http::withOptions(['verify' => false])->get($status_url);
        $statusCode = $response->status();
        Log::debug('ApiJob->response:', [$statusCode] );


        Log::debug('ApiJob->check_url:PUT!', [$api_url] );
        $response = Http::attach(
            'attachment', file_get_contents('D:/tmp/simple.pdf'), 'simple.pdf', ['Content-Type' => 'application/pdf']
        )
        ->withOptions(['verify' => false])
        ->put($api_url);
        Log::debug('ApiJob->response:', [$response] );
        $statusCode = $response->status();
        Log::debug('ApiJob->response:', [$statusCode] );


        // curl --location --request PUT '10.10.6.25:9998/tika' \
        // --header 'Content-Type: application/pdf' \
        // --data '@/c:/userData/M05831/Documenti/GDPR_PARTE_2.pdf'


        // SAVE RESPONSE to file
        

   
            

    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed($exception)
    {

        // Log::error('NerJob!failed!', [$this->job->uuid()] );
        Log::error('NerJob!failed!', [$this->jobInfo, $exception] );
    }


}
