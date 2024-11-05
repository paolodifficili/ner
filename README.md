
php artisan migrate

# API Route
php artisan route:clear
php artisan route:list


# QUEUE
php artisan make:queue-table
php artisan migrate

php artisan queue:work


# Struttura del lavoro su file system


Una sezione del Toolkit è interamente dedicata alla mappatura del percorso ideale di un progetto per l’adozione dell’IA nel settore pubblico. Le sei tappe dovrebbero essere:
- inquadramento del problema
- progettazione
- prototipazione
- sperimentazione
- implementazione
- monitoraggio

Un monito per tutte le organizzazioni del settore pubblico che vogliono iniziare a usare l’IA troppo frettolosamente: 
saltare qualcuno di questi passaggi - oltre a porre problemi di conformità normativa - 
potrebbe determinare il fallimento del progetto.

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


00_File input (PDF sources) copia dei file durante la creazione
-- BATCH --


01_Converter (java, python) : text output JOB + di uno ed anche a pagine ed di diverso tipo (per un file esegue la conversione)


02_Cleaner (stop words, ecc.) : text output JOB (data la conversione ripulisce il test)
i file vengono ripuliti con un cleaner




04_Analyzer (search PII) : json output JOB




05_Report : report JOB


Quando il batch viene creato la cartella,

storage\app\NER_BATCH\BATCH_XXXXXXXXX

nella cartella 

storage\app\NER_BATCH\BATCH_XXXXXXXXX\00_INPUT

viene salvato il file in input ed automaticamente
vengono avviati contro questo file tutti i converter
attivi dal pdf viene subito estratto il testo in 01_CONVERTER

nella cartelle storage\app\NER_BATCH\BATCH_XXXXXXXXX\02_LOAD

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


dumpmysql (già avviato)

git add .
git commit -m "%DATE%-%TIME%"
git push -u -f origin master

# Laravel Migration
php artisan make:migration create_TABLENAME_table
-- edit file
php artisan migrate





# Fpdi composer install laravel  - composer require setasign/fpdi