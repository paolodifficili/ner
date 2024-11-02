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

/*

ApiJobFolder

come parametri prende un Engine e tutte le sue configurazioni per la chiamata ed una cartella
Per ogni file della cartella esegue tante chiamate ed produce tante risposte denominate

fileInput + engine + engine_version . txt ecc.


*/




class ApiJobFolder implements ShouldQueue
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
        Log::debug('ApiJobFolder__construct!', [$jI] );
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

        Log::debug('ApiJobFolder:START:', [$this->job->uuid() ] );
        $this->jobInfo['__UUID__'] = $this->job->uuid();

        Log::debug('ApiJobFolder:info:', [$this->jobInfo] );

        

        $options = json_decode($this->jobInfo['options']);

        // Set default for a 

        Log::debug('ApiJobFolder:options:', [$options] );

        // fileInput viene al momento usato come FOLDER INPUT
        Log::debug('ApiJobFolder:FILE IS FOLDER-INPUT----:', [$options->fileInput ?? false] );
        Log::debug('ApiJobFolder:fileInput:', [$options->fileInput ?? false] );
        Log::debug('ApiJobFolder:fileOutput:', [$options->fileOutput ?? false] );

        Log::debug('ApiJobFolder:contentType:', [$options->contentType ?? false] );
        Log::debug('ApiJobFolder:headers:', [$options->headers ?? false] );
        Log::debug('ApiJobFolder:method:', [$options->method ?? false] );
        Log::debug('ApiJobFolder:dryRun:', [$options->dryRun ?? false] );

        // $url = "https://dummy.restapiexample.com/api/v1/employee/1";
        $status_url = $this->jobInfo['status_url'];
        $api_url = $this->jobInfo['api_url'];
       


        $fileFolderIn = $options->fileInput;
        $fileFolderOut = $options->fileOutput;

        // LEGGE TUTTI IL FILE DELLA CARTELLA INPUT e per ognuno esegue la chiamata ...
        $files = Storage::files($fileFolderIn);
        Log::debug('ApiJobFolder:files:', [$files] );

        foreach($files as $fname)
        {

            Log::debug('ApiJobFolder:CALL!-----------------------:',[]);

            $engine = $this->jobInfo['engine'];
            $engine_version = $this->jobInfo['engine_version'];

            $fileIn = $fname;
            Log::debug('ApiJobFolder:fileIn:', [$fileIn] );
            $path_parts = pathinfo($fileIn);
            
            Log::channel('stack')->debug('ApiJobFolder:fileName:', [$path_parts]);

            $fileOut = $fileFolderOut . "/" . $path_parts['filename'] . "-" . $engine . '-' . $engine_version . ".txt";
            
            Log::debug('ApiJobFolder:fileOut:',[$fileOut]);

            $coda = CodaJob::firstOrCreate([
                'job_uuid' => $this->job->uuid(),
                'uuid_internal' => $this->uuid,
                'batch_uuid' => $this->jobInfo['batch_uuid'],
                'description' => $this->jobInfo['description'],
                'type' => $this->jobInfo['type'],
                'engine' => $this->jobInfo['engine'],
                'engine_version' => $this->jobInfo['engine_version'],
                'api_url' => $this->jobInfo['api_url'],
                'status_url' => $this->jobInfo['status_url'],
                'options' => $this->jobInfo['options'],
                'data_in' => $fileIn,
                'data_out' => $fileOut,
            ]);

            

            $coda->last_run_at = Carbon::now();
            $coda->save();
            
            // save a fake output and exit
            if($options->dryRun ?? false) {

                Log::debug('ApiJobFolder:#####DRY_RUN######:', [] );
                Storage::write($fileOut, 'DRY_RUN');
                $coda->status = 200;
                $coda->save();

            } else {
        
                try {

                    Log::debug('ApiJobFolder:RUN method:', [$options->method] );
                    Log::debug('ApiJobFolder:api:', [$api_url] );
                    Log::debug('ApiJobFolder:fileIn:', [$fileIn] );
                    Log::debug('ApiJobFolder:fileOut:', [$fileOut] );

                    $statusCode = 123;
                    $statusDescription = 'Success!';

                    if($options->method == "POST") {
                        $response = Http::withBody(
                            Storage::get($fileIn), $options->contentType
                        )->post($api_url);    
                        Log::debug('ApiJobFolder:POST:', [$fileOut] );
                        Storage::write($fileOut, $response->body());
                        $statusCode = $response->status();
                    } elseif ($options->method == "POST_FORM") {
                        
                        $response = Http::attach(
                            'file', // Nome del parametro file nella richiesta
                            Storage::get($fileIn), // Contenuto del file
                            'GDPR_PARTE_1.pdf' // Nome del file inviato
                        )
                        ->post($api_url);
                        
                        /*
                        $response = Http::asForm()->post($api_url, [
                            'file' => Storage::get($fileIn),
                        ]);
                        */


                        Log::debug('ApiJobFolder:POST_FORM:', [$fileOut] );
                        Storage::write($fileOut, $response->body());
                        $statusCode = $response->status();
                    } elseif ($options->method == "PUT") {
                        $response = Http::withBody(
                            Storage::get($fileIn), 'application/pdf'
                        )->put($api_url);    
                        $statusCode = $response->status();
                        Log::debug('ApiJobFolder:PUT:', [$fileOut] );
                        Storage::write($fileOut, $response->body());
                    } elseif ($options->method == "GET") {
                        $response = Http::get($api_url, []);
                        Log::debug('ApiJobFolder:api_url:NOT_SAVE_GET:', [] );
                        $statusCode = $response->status();
                    } else {
                        $statusCode = 998;
                        $statusDescription = 'METHOD! NOT! FOUND!';
                        Log::error('ApiJobFolder:METHOD! NOT! FOUND!:', [$response->status()] );
                    
                    }
                    $coda->status = $statusCode;
                    $coda->status_description = $statusDescription; 
                    $coda->save(); 
                             
                }
        
                catch (\Exception $e) {
                    Log::channel('stack')->error('ApiJobFolder Exception:', [$e->getMessage()]);    
                    $coda->status_description = $e->getMessage();
                    $coda->status = 999;
                    $coda->save();
                }
        
            }
        }

        Log::debug('ApiJobFolder:#####FINALE!######:', [] );

        

        
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
        Log::error('ApiJobFolder!failed!', [$this->jobInfo, $exception] );
    }


}
