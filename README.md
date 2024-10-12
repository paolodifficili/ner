
php artisan migrate

# API Route
php artisan route:clear
php artisan route:list


# QUEUE
php artisan make:queue-table
php artisan migrate


# Struttura del lavoro su file system

# Flusso di lavoro

## Creazione di un batch [RUN, CHECK]

### Batch di CHECK_CONFIG

CREATE Viene generato un batch di check che al "RUN" esegue le chiamate 
per tutti gli engines configurati alla url api_status in GET per 
controllare se sono attivi.


### Batch di RUN_ENGINE

Viene creato un Batch con queste opzioni:
Engine da usare Spacy, HF, Ollama ecc. ecc.
File da lavorare 1) PDF (poi tanti) già caricato (UPLOAD)

Quando il batch viene creato la cartella,

storage\app\NER_BATCH\BATCH_XXXXXXXXX

nella cartella 

storage\app\NER_BATCH\BATCH_XXXXXXXXX\INPUT

viene salvato il file in input ed automaticamente
vengono avviati contro questo file tutti i converter
attivi dal pdf viene subito estratto il testo

nella cartelle storage\app\NER_BATCH\BATCH_XXXXXXXXX\WORK_LOAD

Preparazione dei dati 

- Estrazione METADATI
- Eliminazione Stop Words
- Preparazione Prompt per LLM
- Preparazione File di Input

Un file di input per ogni motore di analisi a eseguire con ApiJob



# CONFIG su tabella DB

type : folder, ner, converter,
engine : tipo di configurazione
engine_version : info
api : 
api_status : da richiamare per una verifica del funzionamento (ner,coverter,folder) 
api_service : per utilizzare il servizio
prompt : per LLM

CheckConfigJob esegue un test della configurazione


## Workflow

/upload OK MuxController.php


CMD
D:\mariadb-11.5.2-winx64\RUN_MYSQL.bat

CMD
D:\PROGETTI\LARAVEL>START_ENV_SET_PATH.bat
D:\PROGETTI\LARAVEL>ner_app\php artisan queue:work

CMD
D:\PROGETTI\LARAVEL>ner_app\php artisan queue:work


# Post di un batch
- Upload del file con creazione del batch Id
- Creazione del batch : 
      . selezione file + 
      . selezione dell'azione
      . selezione motori di analisi
      . salvataggio

- Visualizzazione stato di elaborazione di un batch 
    . avvio elaborazione
    . visualizzazione dei risultati
    . cancellazione dei dati
 
- Esecuzione dei valori

# ApiJob::dispatch($job_id);

La logica è un JOB base di una chiamata rest GENERICA dove si passano tutti i parametri per effettuare

- method
- url
- file / body ecc.
- viene salvata la risposta


# https://github.com/paolodifficili/ner.git

# CLONE TO GITHUB


dumpmysql
git add .
git commit -m "%DATE%-%TIME%"
git push -u -f origin master

# Laravel Migration
php artisan make:migration create_TABLENAME_table
-- edit file
php artisan migrate





# Fpdi composer install laravel  - composer require setasign/fpdi