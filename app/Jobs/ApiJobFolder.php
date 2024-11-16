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

Per ogni file della cartella JSON è un file strutturato

Il nome del file engine+engine_version+TIME_STAMP
{
    "inputFile" : "Folder ecc. nome_file_input.",
    "inputFileContent" : "???",
    "options" : [],
    "output" : "",

}

esegue tante chiamate API ed produce tante risposte denominate

fileInput + engine + engine_version . txt ecc.

Inoltre il file che viene preso in input viene pre-elaborato e post-elaborato con due parametri

preAction
postAction



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

        Log::debug('ApiJobFolder:START: >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>       ', [$this->job->uuid() ] );

        $this->jobInfo['__UUID__'] = $this->job->uuid();

        Log::debug('ApiJobFolder:info:          ', [$this->jobInfo] );
        Log::debug('ApiJobFolder:engine:        ', [$this->jobInfo['engine']] );
        Log::debug('ApiJobFolder:engine_version:', [$this->jobInfo['engine_version']] );

        
        $options = json_decode($this->jobInfo['options']);

        // Set default for a 

        Log::debug('ApiJobFolder:options:     ', [$options] );

        // fileInput viene al momento usato come FOLDER INPUT
        Log::debug('ApiJobFolder:fileInput :FOLDER IN :', [$options->fileInput ?? false] );
        Log::debug('ApiJobFolder:fileOutput:FOLDER OUT:', [$options->fileOutput ?? false] );

        Log::debug('ApiJobFolder:contentType: ', [$options->contentType ?? false] );
        Log::debug('ApiJobFolder:headers:     ', [$options->headers ?? false] );
        Log::debug('ApiJobFolder:method:      ', [$options->method ?? false] );
        Log::debug('ApiJobFolder:dryRun:      ', [$options->dryRun ?? false] );
        Log::debug('ApiJobFolder:preAction:   ', [$options->preAction ?? false] );
        Log::debug('ApiJobFolder:postAction:  ', [$options->postAction ?? false] );
        Log::debug('ApiJobFolder:inType:      ', [$options->inType ?? false] );
        Log::debug('ApiJobFolder:outType:     ', [$options->outType ?? false] );



        // $url = "https://dummy.restapiexample.com/api/v1/employee/1";
        $status_url = $this->jobInfo['status_url'];
        $api_url = $this->jobInfo['api_url'];

        $inType = $options->inType ?? false;
        $ouType = $options->outType ?? false;
       

        $fileFolderIn = $options->fileInput;
        $fileFolderOut = $options->fileOutput;

        // LEGGE TUTTI IL FILE DI TESTO .txt         DELLA CARTELLA INPUT e per ognuno esegue la chiamata ...
        
        // $files = Storage::files($fileFolderIn);

        $files = collect(Storage::files($fileFolderIn))->filter(function ($file) use ($inType) {
            return (pathinfo($file, PATHINFO_EXTENSION) === $inType);
        });
        
        
        Log::debug('ApiJobFolder:files:     ', [$files] );

        foreach($files as $fname)
        {

            
            $engine = $this->jobInfo['engine'];
            $engine_version = $this->jobInfo['engine_version'];

            $fileIn = $fname;
            Log::debug('ApiJobFolder:fileIn:', [$fileIn] );
            $path_parts = pathinfo($fileIn);
            
            Log::channel('stack')->debug('ApiJobFolder:fileName:', [$path_parts]);

            $fileOut = $fileFolderOut . "/" . $path_parts['filename'] . "-" . $engine . '-' . $engine_version . ".txt";
            
            Log::debug('ApiJobFolder:fileOut:',[$fileOut]);

            $fileJIN = $fileFolderOut . "/" . $path_parts['filename'] . "-" . $engine . '-' . $engine_version . "_IN.json";

            $fileJOUT = $fileFolderOut . "/" . $path_parts['filename'] . "-" . $engine . '-' . $engine_version . "_OUT.json";

            $fileJSON_OUT = $fileFolderOut . "/" . $engine . '-' . $engine_version . Carbon::now()  .  ".json";


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

                Log::debug('ApiJobFolder:#####DRY_RUN###################################:', [] );
                Storage::write($fileOut, 'DRY_RUN');
                $coda->status = 200;
                $coda->save();

            } else {
        
                try {

                    Log::debug('ApiJobFolder:engine:', [$this->jobInfo['engine']]);
                    Log::debug('ApiJobFolder:engine_version:', [$this->jobInfo['engine_version']]);
  

                    Log::debug('ApiJobFolder:method:    ', [$options->method] );
                    Log::debug('ApiJobFolder:api:       ', [$api_url] );
                    Log::debug('ApiJobFolder:fileIn:    ', [$fileIn] );
                    Log::debug('ApiJobFolder:fileOut:   ', [$fileOut] );

                    Log::debug('ApiJobFolder:preAction: ', [$options->preAction ?? false] );
                    Log::debug('ApiJobFolder:postAction:', [$options->postAction ?? false] );

                    $content2analyze = Storage::get($fileIn);
                    

                    // Esegue il wrapper 
                    if ($options->preAction ?? false) {
                        Log::debug('ApiJobFolder:preAction:', [$options->preAction] );
                        $content2analyze = $this->dataWrapper($content2analyze, $options->preAction, $options );
                    }

                    Storage::write($fileJIN, $content2analyze);


                    $statusCode = 123;
                    $statusDescription = 'Success!';
                    $statusBody = '';

                    if($options->method == "POST") {

                        $response = Http::withBody( $content2analyze, $options->contentType )->post($api_url);    
                        // Log::debug('ApiJobFolder:POST:', [$fileOut] );
                        // Storage::write($fileOut, $response->body());
                        $statusBody = $response->body(); 
                        $statusCode = $response->status();

                    } elseif ($options->method == "POST_FORM") {
                        

                        Log::debug('ApiJobFolder:POST_FORM: ', [$path_parts['basename']]);

                        $response = Http::attach(
                            'file', // Nome del parametro file nella richiesta
                            $content2analyze, // Contenuto del file
                            $path_parts['basename'] // Nome del file inviato
                        )
                        ->post($api_url);

                        $statusBody = $response->body(); 
                        $statusCode = $response->status();

                    } elseif ($options->method == "PUT") {
                        $response = Http::withBody(
                            $content2analyze, 'application/pdf'
                        )->put($api_url);    
                        $statusBody = $response->body(); 
                        $statusCode = $response->status();
                    } elseif ($options->method == "GET") {
                        $response = Http::get($api_url, []);
                        $statusBody = $response->body(); 
                        $statusCode = $response->status();
                    } else {
                        $statusBody = 'METHOD! NOT! FOUND!'; 
                        $statusCode = 998;
                        $statusDescription = 'METHOD! NOT! FOUND!';

                        Log::error('ApiJobFolder:METHOD! NOT! FOUND!:', [$response->status()] );
                    }


                    Storage::write($fileJOUT, $statusBody);

                    if ($options->postAction ?? false) {
                        $statusBody = $this->dataWrapper($statusBody, $options->postAction, $options );
                    } 
                    
                    Storage::write($fileOut, $statusBody);


                    $coda->status = $statusCode;
                    $coda->status_description = $statusDescription; 
                    $coda->save(); 


                    // wrapper out

                             
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


    public function dataWrapper($content, $action, $options)
    {

        Log::debug('ApiJobFolder:dataWrapper:action', [$action] );
        Log::debug('ApiJobFolder:dataWrapper:options', [$options] );
        Log::debug('ApiJobFolder:dataWrapper:content', [$content] );

        $newContent = $content;
       
        if ($action === "ollamaIN") {

            $promptTxt = $options->promptTxt;
            $promptTag = $options->promptTag;
            $model = $options->model;

            Log::debug('ApiJobFolder:dataWrapper:promptTxt', [$promptTxt] );
            Log::debug('ApiJobFolder:dataWrapper:promptTag', [$promptTag] );
            Log::debug('ApiJobFolder:dataWrapper:model', [$model] );

            /*
                    {
                    "model": "llama3.2",
                    "prompt": "Why is the sky blue?",
                    "stream": false
                    }'
            */
            
            // str_replace("world", "Peter", "Hello world!"); // Outputs: Hello Peter!
            $prompt = str_replace($promptTag, $content, $promptTxt);
            Log::debug('ApiJobFolder:dataWrapper:PROMPT!', [$newContent] );

            $data = [];
            $data['model'] = $model;
            $data['prompt'] = $prompt;
            $data['stream'] = false;

            $newContent =  json_encode($data);
            Log::error('ApiJobFolder:dataWrapper:PROMPT!', [$newContent] );
            
            

        } elseif ( $action === "ollamaOUT" ) {

            Log::debug('ApiJobFolder:dataWrapper:ollamaOUT:', [$content] );
            $jd =  json_decode($newContent);
            Log::debug('ApiJobFolder:dataWrapper:ollamaOUT:', [$jd] );
            Log::debug('ApiJobFolder:dataWrapper:ollamaOUT:', [$jd->response] );

            $newContent = $jd->response;

        
        } else {
            Log::error('ApiJobFolder:dataWrapper:ACTION NOT FOUND!', [$action] );
        }

        return $newContent;
    }

}
