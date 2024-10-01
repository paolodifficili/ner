<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\CodaJob;

use Carbon\Carbon;

class NerJob implements ShouldQueue
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
        Log::debug('NerJob!__construct!', [$jI] );
        $this->jobInfo = $jI;
        $this->uuid = $jI['id'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        if ($this->batch()) {
            // Ottieni il batch ID
            $batchId = $this->batch()->id;
            Log::debug('NerJob->BATCHID*******:', [$batchId] );
        }

/*
        if ($this->batch()->cancelled()) {
            Log::debug('BATCH ARE CANCELLED! NerJob->handle:', [$this->job->uuid() ] );
             return;
        }
*/

        Log::debug('NerJob->handle:', [$this->job->uuid() ] );

        $this->jobInfo['__UUID__'] = $this->job->uuid();

        Log::debug('NerJob->info:', [$this->jobInfo] );

        $coda = CodaJob::firstOrCreate([
            'uuid' => $this->job->uuid(),
            'uuid_internal' => $this->uuid,
            'batch_uuid' => $this->jobInfo['batch_uuid'],
            'description' => $this->jobInfo['description'],
            'type' => $this->jobInfo['type'],
            'file' => $this->jobInfo['file'],
            'root_folder' => $this->jobInfo['root_folder'],
            'service_url' => $this->jobInfo['service_url'],
        ]);

        // $job_id['root_folder'] = $root_folder;
        // $job_id['service_url'] = $api;

        $city = "Rome";
        $apiKey = 'la-tua-api-key';


        // $url = "https://dummy.restapiexample.com/api/v1/employee/1";
        $url = $this->jobInfo['service_url'];


        Log::debug('NerJob->service_url:', [$url] );
  
        // Http::withOptions([          'debug' => true,      ]

        $response = Http::withOptions(['verify' => false])->get($url);

        $statusCode = $response->status();


        $randNum = rand(1,100);

        Log::debug('NerJob->statuscode:', [$statusCode , $randNum] );
        // Genera un errore random ...


        if($randNum > 70){
            $coda->last_run_at = Carbon::now();
            $coda->status = '999';
            $coda->save();
            Log::error('NerJob->errore RANDOM:', [$randNum] );
            throw new \Exception('NerJob->Errore RANDOM! :');
            return;
        }


        // Controlla lo stato della risposta
        if (!$response->successful()) {
            $coda->last_run_at = Carbon::now();
            $coda->status = $statusCode;
            $coda->save();
            Log::error('NerJob->errore http:', [$statusCode] );
            throw new \Exception('NerJob->Errore http generico :' . $statusCode );
        } else {
            
            $weatherData = $response->json();
            Log::info('NerJob->OK http:', [$weatherData] );
            $coda->last_run_at = Carbon::now();
            $coda->status_description = Carbon::now();
            $coda->status = $statusCode;
            $coda->save();

            // salvataggio dati nella cartella 
            $fileDest = $this->jobInfo['root_folder'] . "/" .  $this->jobInfo['batch_uuid'] . "/" . $this->jobInfo['type'] .   "/out.json";
            Log::info('NerJob write to :', [$fileDest] );
            Storage::put($fileDest, json_encode($weatherData));
            
        /*    
        // Puoi manipolare o trasformare i dati se necessario
        return response()->json([
            'city' => $city,
            'temperature' => $weatherData['main']['temp'],
            'description' => $weatherData['weather'][0]['description'],
        ]);
        */

        }
            

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
