
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

##### UN BATCH GESTISCE UN FILE SOLO ###############


una cartella per ogni ENGINE !!! 

- input da passaggio precedente
- input prima della chiamata
- output 
- altri dati (metadati ecc.)

01_Converter/d (java, python) : text output JOB + di uno ed anche a pagine ed di diverso tipo (per un file esegue la conversione) risultato della conversione da PDF -> testo


02_Cleaner/d (stop words, ecc.) : text output JOB (data la conversione ripulisce il test)
i file vengono ripuliti con un cleaner. Il risultato della pulizia


04_Analyzer/d (search PII) : il risultato delle analisi

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



GET
https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css
[HTTP/1.1 200 OK 0ms]

GET
https://cdn.jsdelivr.net/npm/vuetify@3.7.1/dist/vuetify.min.css
[HTTP/1.1 200 OK 0ms]

GET
http://localhost:8000/js/mgr/app.js
[HTTP/1.1 200 OK 13ms]

GET
http://localhost:8000/js/mgr/echo.iife.js
[HTTP/1.1 200 OK 12ms]

GET
https://unpkg.com/vue@latest
[HTTP/2 302  111ms]

GET
https://unpkg.com/vue-router@4
[HTTP/2 302  167ms]

GET
https://cdn.jsdelivr.net/npm/vue3-sfc-loader
[HTTP/2 200  0ms]

GET
https://cdn.jsdelivr.net/npm/vuetify@3.7.1/dist/vuetify.min.js
[HTTP/2 200  0ms]

Il layout è stato forzato prima che la pagina fosse completamente caricata. Se questo avviene prima del caricamento dei fogli di stile, potrebbe causare la visualizzazione di contenuti privi di stile (“flash of unstyled content”). markup.js:250:53
GET
https://unpkg.com/@mux/upchunk@3
[HTTP/2 302  146ms]

GET
https://unpkg.com/axios/dist/axios.min.js
[HTTP/2 302  142ms]

GET
https://cdnjs.cloudflare.com/ajax/libs/pusher/7.0.3/pusher.min.js
[HTTP/2 200  0ms]

GET
https://unpkg.com/axios@1.7.7/dist/axios.min.js
[HTTP/2 200  0ms]

GET
https://unpkg.com/vue-router@4.4.5
[HTTP/2 302  0ms]

GET
https://unpkg.com/@mux/upchunk@3.4.0
[HTTP/2 302  0ms]

GET
https://unpkg.com/vue-router@4.4.5/dist/vue-router.global.js
[HTTP/2 200  0ms]

GET
https://unpkg.com/@mux/upchunk@3.4.0/dist/upchunk.js
[HTTP/2 200  0ms]

GET
https://unpkg.com/vue@3.5.12
[HTTP/2 302  0ms]

GET
https://unpkg.com/vue@3.5.12/dist/vue.global.js
[HTTP/2 200  0ms]

You are running a development build of Vue.
Make sure to use the production build (*.prod.js) when deploying for production. vue@latest:12238:17
INIT axios app.js:56:9
INIT Reverb Push app.js:62:9
localhost app.js:81:9
Object { options: {…}, connector: {…} }
app.js:96:9
XHRGET
http://localhost:8000/vue/app.vue
[HTTP/1.1 200 OK 8ms]

[Vue Router warn]: Component "default" in record with path "/batch/:id" is defined using "defineAsyncComponent()". Write "() => import('./MyPage.vue')" instead of "defineAsyncComponent(() => import('./MyPage.vue'))". vue-router@4:51:20
GET
http://localhost:8000/favicon.ico
[HTTP/1.1 200 OK 0ms]

GET
ws://localhost:7888/app/my-app-key?protocol=7&client=js&version=7.0.3&flash=false
[HTTP/1.1 101 Switching Protocols 4ms]

app.vue initi GotMessage vue3-sfc-loader line 118 > Function:3:609
XHRGET
http://localhost:8000/vue/batch_item.vue
[HTTP/1.1 200 OK 6ms]

BATCH____1731417560323




sZ3iVS~]P?x9Zh4