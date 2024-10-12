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
use Illuminate\Support\Facades\Storage;


use App\Models\CodaJob;
use App\Models\CodaFile;


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

        Log::debug('ApiJob:START:', [$this->job->uuid() ] );
        $this->jobInfo['__UUID__'] = $this->job->uuid();

        Log::debug('ApiJob:info:', [$this->jobInfo] );

        $coda = CodaJob::firstOrCreate([
            'job_uuid' => $this->job->uuid(),
            'uuid_internal' => $this->uuid,
            'batch_uuid' => $this->jobInfo['batch_uuid'],
            'description' => $this->jobInfo['description'],
            'type' => $this->jobInfo['type'],
            'engine' => $this->jobInfo['engine'],
            'api_url' => $this->jobInfo['api_url'],
            'status_url' => $this->jobInfo['status_url'],
            'options' => $this->jobInfo['options'],
        ]);

        $options = json_decode($this->jobInfo['options']);

        Log::debug('ApiJob:options:', [$options] );
        Log::debug('ApiJob:fileInput:', [$options->fileInput] );
        Log::debug('ApiJob:fileOutput:', [$options->fileOutput] );
        Log::debug('ApiJob:contentType:', [$options->contentType] );
        Log::debug('ApiJob:headers:', [$options->headers] );
        Log::debug('ApiJob:method:', [$options->method] );

        // $url = "https://dummy.restapiexample.com/api/v1/employee/1";
        $status_url = $this->jobInfo['status_url'];
        $api_url = $this->jobInfo['api_url'];

        $coda->last_run_at = Carbon::now();
        $coda->save();

        
        Log::debug('ApiJob:api:', [$api_url] );


        try {

            if($options->method == "POST") {

                $response = Http::withBody(
                    Storage::get($options->fileInput), $options->contentType
                )->post($api_url);    
                Log::debug('ApiJob:api_url:SAVE_POST:', [$options->fileOutput] );
                Storage::write($options->fileOutput, $response->body());

            }

            if($options->method == "PUT") {
                $response = Http::withBody(
                    Storage::get($options->fileInput), 'application/pdf'
                )->put($api_url);    
                Log::debug('ApiJob:api_url:SAVE_PUT:', [$options->fileOutput] );
                Storage::write($options->fileOutput, $response->body());
            }

            if($options->method == "GET") {
                $response = Http::get($api_url, []);
                Log::debug('ApiJob:api_url:NOT_SAVE_GET:', [] );
            }
 
            $statusCode = $response->status();
            Log::debug('ApiJob:response:', [$response->status()] );
            
    
            $coda->status = 200;
            $coda->save();
        }

        catch (\Exception $e) {
            Log::channel('stack')->error('ApiJob Exception:', [$e->getMessage()]);    
            $coda->status_description = $e->getMessage();
            $coda->status = 999;
            $coda->save();
        }
        


        // write to $options->fileOutput
        
        // Log::debug('ApiJob->response:', [$response] );
     

        // TODO GESTIONE ERRORI    

        // curl --location --request PUT '10.10.6.25:9998/tika' \
        // --header 'Content-Type: application/pdf' \
        // --data '@/c:/userData/M05831/Documenti/GDPR_PARTE_2.pdf'

       

        // Update Job Status
        
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
