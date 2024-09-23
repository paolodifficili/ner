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

use App\Models\Coda;
use App\Models\Config;

use Carbon\Carbon;

/**
 * Esegue un controllo di una configurazione
 * Se api esegue una chiamata a status
 * Se cartella scrive e legge un file sulla cartella
 * ....
 */


class CheckConfigJob implements ShouldQueue
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
        Log::debug('CheckConfigJob!__construct!', [$jI] );
        $this->jobInfo = $jI;
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
            Log::debug('CheckConfigJob->BATCHID*******:', [$batchId] );
        }


        Log::debug('CheckConfigJob->handle:', [$this->job->uuid() ] );

        $this->jobInfo['__UUID__'] = $this->job->uuid();

        Log::debug('CheckConfigJob->info:', [$this->jobInfo] );

        // Geo_Postal_us::where('postal', $postal)->firstOrFail();

        $config = Config::where([
            'type' => $this->jobInfo['type'],
            'engine' => $this->jobInfo['engine'],
        ])->findOrFail();

        Log::debug('CheckConfigJob->config:', [$config] );
    
        $coda = Coda::firstOrCreate([
            'uuid' => $this->job->uuid(),
            'uuid_internal' => $this->uuid,
            'batch_uuid' => $this->jobInfo['batch_uuid'],
            'description' => $this->jobInfo['description'],
            'type' => $this->jobInfo['type'],
            'file' => $this->jobInfo['file'],
            'root_folder' => $this->jobInfo['root_folder'],
            'service_url' => $this->jobInfo['service_url'],
        ]);

        if ($config['type'] == 'folder') {
            // write and read file to folder
            $folder = $config['api'];
            $fname = $folder . "/" . Str::uuid() . ".txt";
            Log::debug('CheckConfigJob->CHECK FOLDER:', [$fname] );
            $content  = 'TO_CHECK!';
            $content_2_verify = '';

            try {
                Storage::put($fname, $content);
                $content_2_verify = Storage::get($fname);
            } catch (Exception $e) {
                Log::debug('CheckConfigJob->CHECK FOLDER ERROR:', [$e->toString()] );
                $coda->last_run_at = Carbon::now();
                $coda->status_description = 'WRITE or READ file ERROR!';
                $coda->status = '900';
                $coda->save();    
            }

            if ( $content == $content_2_verify) {
                $coda->last_run_at = Carbon::now();
                $coda->status_description = 'Folder check ok!' . $fname;
                $coda->status = '200';
                $coda->save();    
            } else {
                $coda->last_run_at = Carbon::now();
                $coda->status_description = 'WRITE or READ file ERROR : ' . $fname;
                $coda->status = '900';
                $coda->save();  
            }
 
 
            // Storage::delete($fname);

        } elseif ($config['type'] == 'folder') {

            $url = $config['api_status'];

            Log::debug('CheckConfigJob->CHECK API STATUS:', [$url] );

            $response = Http::withOptions(['verify' => false])->get($url);
            $coda->last_run_at = Carbon::now();
            $coda->status = $response->status();
            $coda->save();

        } else {
            Log::error('CheckConfigJob->TYPE NOT FOUND!:', [] );    

            $response = Http::withOptions(['verify' => false])->get($url);
            $coda->last_run_at = Carbon::now();
            $coda->status_description = 'config type not found';
            $coda->status = '900';
            $coda->save();
        }

        /*
  
        $randNum = rand(1,100);

        Log::debug('CheckConfigJob->statuscode:', [$statusCode , $randNum] );
        // Genera un errore random ...


        // Controlla lo stato della risposta
        if (!$response->successful()) {
            $coda->last_run_at = Carbon::now();
            $coda->status = $statusCode;
            $coda->save();
            Log::error('CheckConfigJob->errore http:', [$statusCode] );
            throw new \Exception('CheckConfigJob->Errore http generico :' . $statusCode );
        } else {
            
            $weatherData = $response->json();
            Log::info('CheckConfigJob->OK http:', [$weatherData] );
            $coda->last_run_at = Carbon::now();
            $coda->status_description = Carbon::now();
            $coda->status = $statusCode;
            $coda->save();

            // salvataggio dati nella cartella 
            $fileDest = $this->jobInfo['root_folder'] . "/" .  $this->jobInfo['batch_uuid'] . "/" . $this->jobInfo['type'] .   "/out.json";
            Log::info('CheckConfigJob write to :', [$fileDest] );
            Storage::put($fileDest, json_encode($weatherData));

        }
        */
            

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
        Log::error('CheckConfigJob!failed!', [$this->jobInfo, $exception] );
    }



}

