<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Faker\Factory as Faker;

use Illuminate\Support\Facades\Bus;

use Illuminate\Support\Facades\Config;


use App\Jobs\NerJob;
use App\Jobs\SpacyJob;

/*

php artisan QMGR add

Gestore della code
d:\xampp\php\php.exe artisan QMGR add

Come gestire una coda

upload del documento in cartella
lanciare la conversione in testo
lanciare diversi recuperi dati

*/


class QMGR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'QMGR {par1?} {par2?} {par3?} {par4?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'QMGR command line queue manager ';

    
    public $soapClientOptions;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $cmd_par1 = $this->argument('par1');
        $cmd_par2 = $this->argument('par2');
        $cmd_par3 = $this->argument('par3');
        $cmd_par4 = $this->argument('par4');

        Log::channel('stack')->info('QMGR:command line:', [
            $cmd_par1,
            $cmd_par2,
            $cmd_par3,
            $cmd_par4, 
            ] );

        // Setup Soap parameters

        switch ($cmd_par1) {

            case "checkConfig":




            break;

            case "merge":

                
                $uuid = "1726138047856";
                $fname = $uuid . "/out.pdf";
                Log::channel('stack')->info('QMGR merge:', [$uuid]);
                
                $files = Storage::files($uuid);
                Log::channel('stack')->info('QMGR merge:', [$files]);


                $stringToRemove = $uuid . "/";

                $onlyFileName = array_map(function($item) use ($stringToRemove) {
                    // Rimuovi la stringa dall'inizio (se presente)
                    $item = preg_replace('/^' . preg_quote($stringToRemove, '/') . '/', '', $item);
                    
                    // Converti l'elemento in double
                    return intval($item);
                }, $files);
                
                // Ordina l'array in maniera crescente
                sort($onlyFileName);

                Log::channel('stack')->info('QMGR merge:', [$onlyFileName]);

                foreach($onlyFileName as $item ) {

                    // read contents
                    $file2get = $uuid . "/" . $item;
                    Log::channel('stack')->info('QMGR read:', [$file2get]);
                    $contents = Storage::get($file2get);

                    if( $item == 0) {
                        Log::channel('stack')->info('QMGR write 2:', [$fname]);
                        Storage::put($fname, $contents);
                    } else {
                        Log::channel('stack')->info('QMGR append 2:', [$fname]);
                        Storage::append($fname, $contents);
                    }


                }


            break;


            case "batch":
                $faker = Faker::create('SeedData');

                $root_folder = Config::get('ner_services.root_folder');

                $engine = 'spacy_ner';
               
                $api = Config::get('ner_services.' . $engine . '.api');
                Log::channel('stack')->info('QMGR config:', [$engine, $root_folder, $api]);

                $batchID =  $faker->uuid();
                

                $job_1 = [];
                $job_1['description'] = strtoupper($faker->firstName());
                $job_1['type'] = $engine;
                $job_1['id'] = $faker->uuid();
                $job_1['batch_uuid'] = $batchID;
                $job_1['failed'] = '';
                $job_1['file'] = $faker->randomElement(['a.pdf','b.pdf','c.pdf']);
                $job_1['root_folder'] = $root_folder;
                $job_1['service_url'] = $api;

                $engine = 'spacy_regexp';

                $api = Config::get('ner_services.' . $engine . '.api');
                Log::channel('stack')->info('QMGR config:', [$engine, $root_folder, $api]);

                $job_2 = [];
                $job_2['description'] = strtoupper($faker->firstName());
                $job_2['type'] = $engine;
                $job_2['id'] = $faker->uuid();
                $job_2['batch_uuid'] = $batchID;
                $job_2['failed'] = '';
                $job_2['file'] = $faker->randomElement(['a.pdf','b.pdf','c.pdf']);
                $job_2['root_folder'] = $root_folder;
                $job_2['service_url'] = $api;



                $batch = Bus::batch([
                    new NerJob($job_1),
                    new NerJob($job_2)
                ])
                ->before(function ($batch) {
                 // The batch has been created but no jobs have been added...
                })
                ->progress(function ($batch) {
                    Log::channel('stack')->info('BATCH PROGRESS:', [$batch]);
                    // A single job has completed successfully...
                })->then(function ($batch) {
                    // All jobs completed successfully...
                })->catch(function ($batch, Throwable $e) {
                    Log::channel('stack')->error('BATCH ERROR:', [$batch->id]);
                    Log::channel('stack')->error('BATCH ERROR:', [$e]);
                })->finally(function ($batch) {
                    Log::channel('stack')->info('BATCH COMPLETED:', [$batch->id]);
                })->allowFailures()->name($batchID)->dispatch();
                                
                Log::channel('stack')->info('BATCH ID:', [$batch->id, $batchID]);

            break;


            case "run":

               # Artisan::call("infyom:scaffold", ['name' => $request['name'], '--fieldsFile' => 'public/Product.json']);
               \Artisan::call("queue:work", ['--max-jobs' => '100']);

            break;


            case "add":
                $faker = Faker::create('SeedData');

                $engine = 'spacy_ner';
                Log::channel('stack')->info('QMGR engine:', [$engine]);

                $root_folder = Config::get('ner_services.root_folder');
                Log::channel('stack')->info('QMGR config root_folder:', [$engine, $root_folder]);

                $api = Config::get('ner_services.spacy_ner.api');
                Log::channel('stack')->info('QMGR config root_folder:', [$api]);

                $job_id = [];
                $job_id['description'] = strtoupper($faker->firstName());
                // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                $job_id['type'] = 'spacy_ner';
                // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                // $job_id['id'] = $faker->uuid();
                $job_id['id'] = 'AAA-BBB-CCCC';
                $job_id['batch_uuid'] = 'AAA-BBB-CCCC';
                $job_id['failed'] = '';
                $job_id['file'] = $faker->randomElement(['a.pdf','b.pdf','c.pdf']);
                $job_id['root_folder'] = $root_folder;
                $job_id['service_url'] = $api;

                Log::channel('stack')->info('QMGR add Job to queue', [$job_id]);
                NerJob::dispatch($job_id);
                
            break;


            case "dryrun":

                $type = "tika";
                $status_url = Config::get('ner_services.tika.status_url');
                $value2 = Config::get('ner_services.tika.status_url2');
                Log::channel('stack')->info('QMGR config info:', [$status_url, $value2]);

                $contents = "TEST00022221";
                Storage::put('1312313/file2.txt', $contents);
                
                /*
                if (! Storage::put('file.jpg', $contents)) {
                    // The file could not be written to disk...
                }

                Personally identifiable information (PII) is any data that can be used to identify someone

                */

            break;
            
            default:
                Log::channel('stack')->info('... command not found');
			break;
        }

        return 0;
    }

  

 


    
    public function clean($string) 
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }


}
