<script>
  export default {
    data() {
      return {
        dialog: false,
        dialogInfo : {
          title : '___TITOLO___',
          text : '___TESTO____',
        },
        power: 0,
        files: [],
        file_uuid: '',
        listItems: [],
        execBatch: true,
        items: [   ],
      }
    },
    methods: {

      async getData() {
        const res = await fetch("https://jsonplaceholder.typicode.com/posts");
        const finalRes = await res.json();
        this.listItems = finalRes;
      },

 

      gotoConfig() {
        console.log('gotoConfig .....');
      },

   

      async startBatch (BATCH_UUID) {

        console.log('startBatch', BATCH_UUID);
        this.addItem(BATCH_UUID, 'Starting... ' + BATCH_UUID + " ... ");
        
        const obj = {
           "BATCH_UUID" : BATCH_UUID
        };

        console.log(obj);

        const requestOptions = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(obj)
        };

        try {
          const response = await fetch('/api/qmgr', requestOptions);
          if (!response.ok) {
            console.error('Errore nel recuperare i dati');
            this.updateItem(BATCH_UUID, 'Starting ' + BATCH_UUID + " ERROR!");
            this.stopItem(BATCH_UUID, "red");
          } else {
            console.log('Batch avviato!');
            const jsonData = await response.json();
            console.log(jsonData);
            this.updateItem(BATCH_UUID, 'Starting ' + BATCH_UUID + " success!");
            this.stopItem(BATCH_UUID, "green");
          }
      
        } catch (err) {
            this.updateItem(BATCH_UUID, 'Creating ' + BATCH_UUID + " ERROR!" + err.message);
            this.stopItem(BATCH_UUID, "red");
            console.error(err.message);
        } 


      },

      createBatchConfig()
      {
        console.log('createBatchConfig .....');
        const BATCH_UUID = 'BATCH____' + Date.now();
        this.createBatch(BATCH_UUID, 'CHECK_CONFIG', '');
      },

      createBatchAnalyze()
      {
        console.log('createBatchAnalyze .....');
        const BATCH_UUID = 'BATCH____' + Date.now();
        this.createBatch(BATCH_UUID, 'RUN_ENGINE', this.file_uuid);
      },


      createBatch(BATCH_UUID, action, file_uuid) {

        // action RUN_ENGINE, CHECK_CONFIG

        
        // da prendere dopo l'upload ...
        // this.file_uuid = '1730979689955';
        // const BATCH_UUID = 'BATCH____' + Date.now();

        this.addItem('BATCH', 'Creating ' + BATCH_UUID + " ... ");

        console.log('createBatch .....' , BATCH_UUID, action, file_uuid);

        const ops = {
          action_selected : action,
          engines_selected : [],
          files_selected : [],
          files_uuid : [this.file_uuid]
        };

        const obj = {
            batch_uuid:  BATCH_UUID,
            batch_description : BATCH_UUID,
            batch_action: action,
            batch_options: JSON.stringify(ops)
        };

        console.log(obj);

        const requestOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(obj)
        };

        fetch('/api/batch', requestOptions).then(async response => 
          {
            const data = await response.json();

            // check for error response
            if (!response.ok) {
                // get error message from body or default to response status
                const error = (data && data.message) || response.status;
                return Promise.reject(error);
                this.updateItem('BATCH', "Creating Batch error!");
                this.stopItem('BATCH', "red");
            }

              console.log(data);
              console.log(data.data.batch_uuid);

              this.batch_id = data.data.batch_uuid;

              this.updateItem('BATCH', 'Creating ' + BATCH_UUID + " success!");
              this.stopItem('BATCH', "green");

              this.startBatch(BATCH_UUID);
    
            })

            .catch(error => {
              this.overlay = false;
              this.errorMessage = error;
              this.txtSnackbar = error;
              this.snackbar = true;
              console.error('There was an error!', error);
            });

      },


      uploadBar() {
        console.log(this.power);
        this.power = this.power + 10;
      },

      showDialog() {
        this.dialogInfo.title = "MARIO!!!!!!!!";
        this.dialog = true;
      },

      uploadFile() {

        if(!this.files.name) {
          alert('Manca il file!');
          return;
        }

        console.log('uploadFile');
        console.log(this.files.name);

        this.file_uuid = Date.now();

        const ea = {
            'X-UP-FILENAME': this.files.name,
            'X-UP-UUID': this.file_uuid,
        };

        this.resetLog();
        this.addItem('UPLOAD', 'Uploading .....');
        // https://github.com/muxinc/upchunk 
        const upload = UpChunk.createUpload({
          method : 'PUT',
          endpoint: '/api/mux',
          headers : ea,
          file: this.files,
          chunkSize: 2048, // Uploads the file in ~30 MB chunks
        });

        upload.on('chunkSuccess', (msg) => {
            console.log('chunkSuccess');
            console.log(msg);
            
        });

        upload.on('error', (err) => {
            console.error(err);
            console.log(err, true, new Date());

            this.updateItem('UPLOAD', 'ERROR!' + err.message);
            this.stopItem('UPLOAD','red');

            
        });

        upload.on('progress', (progress) => {
            console.log(progress);
            console.log(`Progress ${progress.detail}% .`);
            // myBar.style.width = progress.detail + "%";
            this.power = Math.floor(progress.detail);

            this.updateItem('UPLOAD', 'Loading ' + this.power + "%")

            console.log(this.power);
        });
    
        upload.on('success', (msg) => {
            console.log(msg);
            console.log("Upload success!");

            this.updateItem('UPLOAD',"Upload success!");
            this.stopItem('UPLOAD','green');

            console.log('FileUUID:', this.file_uuid);

            // this.dialogInfo.title = "Upload status";
            // this.dialogInfo.text = "Upload success!";
            // this.dialog = true;

            const BATCH_UUID = 'BATCH____' + Date.now();
            this.createBatch(BATCH_UUID, 'RUN_ENGINE', this.file_uuid);

        });
        
      },

    updateLogRandom(id) {
      let c = Date.now();
      this.updateLog(id, c);
    },

    resetLog() {
      this.items = []
    },

    stopLog() {
      console.log('stopLog')
      this.overlay = false
      clearInterval(this.interval)
      this.stopLastItem()
    },

    updateLog(id, text) {
      this.items[id].title = text;
    },

    addItem(key, text) {
    // this.stopLastItem()
    // this.updateLastItem('Done!')
      console.log('addItem', key, text)
      this.items.push({
        key: key,
        title: text, 
        color : "green",
        running: true})
     },

     stopItem(key, color) {

      console.log('stopItem:', key, color);
      var index = this.items.findIndex(function(item) {
        return item.key == key
      });

      this.items[index].color = color;
      this.items[index].running = false;
      
    },

    stopLastItem() {
      console.log('stopLastItem:', j);
      let j = this.items.length
      if (j > 0 ) {
        this.items[this.items.length - 1].color = "green";
        this.items[this.items.length - 1].running = false;
      }
      
    },

    updateItem(key, text) {
      console.log('updateItem:', key, text)
      var index = this.items.findIndex(function(item) {
        return item.key == key
      });

      this.items[index].title = text;
      

   },

    updateLastItem(t) {
      let j = this.items.length
      if (j > 0 ) {
        this.items[this.items.length -1].title =  t;
      }
      console.log('updateLastItem:', j, t)
    },

    execListItem(i) {
      console.log('execListItem .....', i);
      console.log(this.items[i]);
      console.log(this.items[i].key);
      this.$router.push('/batch/' + this.items[i].key);
    },

    gotoBatch(item) {
      console.log('gotoBatch');
      this.$router.push('/batch/' + item.batch_uuid);
    },

    addLog(text) {
      this.stopLastLog()
      var currentDateWithFormat = Date.now();
      console.log(currentDateWithFormat);
      this.items.push(
        {       
          title: text, 
          value : "ok", 
          color: "green", 
          icon: 'mdi-check-circle-outline', 
          running: true
        })
    },

    stopLog(id) {
      this.items[id].color = "green";
      this.items[id].running = false;
    },


    stopLastLog() {
      let j = this.items.length
      if (j > 0 ) {
        this.items[this.items.length -1].color = "green";
        this.items[this.items.length -1].running = false;
      }
      console.log(j)
    },



    },
    mounted() {
      console.log('upload.vue mounted')
    }
  }
</script>

<template>

<v-container class="pa-4" fill-height>

<!-- Logo e Titolo -->

<div align="center" justify="center">

<v-img
:width="100"
src="./vue/logo.png"
></v-img>
</div>



<v-row justify="center" align="center">

  <v-col cols="12" md="8" lg="6">

    <div class="text-center mb-4">
      <h2 class="font-weight-bold mt-2">Cosa devo analizzare ?</h2>
    </div>
  </v-col>

</v-row>




<v-file-input clearable id="picker" show-size label="File input" v-model="files"></v-file-input>


<v-row align="center" justify="center">

<v-col cols="auto">
  <v-btn   prepend-icon="mdi-cloud-upload" rounded="xl"  size="large" variant="tonal" @click="uploadFile">
  <template v-slot:prepend><v-icon color="blue"></v-icon></template>
    Upload</v-btn>
</v-col>

<v-col cols="auto">
  <v-btn  prepend-icon="mdi-delete-forever"  rounded="xl" size="large" variant="tonal" @click="resetLog">
  <template v-slot:prepend><v-icon color="red"></v-icon></template>
    Reset</v-btn>
</v-col>

<v-col cols="auto">
  <v-btn  prepend-icon="mdi-stop-circle-outline"  rounded="xl" size="large" variant="tonal" @click="createBatchAnalyze">
  <template v-slot:prepend><v-icon color="green"></v-icon></template>
    New Batch</v-btn>
</v-col>

<v-col cols="auto">
  <v-btn  prepend-icon="mdi-play-circle-outline"  rounded="xl" size="large" variant="tonal" @click="createBatchConfig">
  <template v-slot:prepend><v-icon color="yellow"></v-icon></template>
    Config</v-btn>
</v-col>

</v-row>



 <v-list>
   

    <v-list-item
      v-for="(item, i) in items"
      :key="i"
      :value="item"
      color="primary"
      rounded="xl"
      @click="execListItem(i)"
    >
    <template v-slot:append v-if="item.running">
       <v-progress-circular
        :color="item.color"
        size="24"
        :indeterminate="item.running"
        >
        </v-progress-circular>
      </template>

      <template v-slot:append v-if="!item.running">
      <v-icon icon="mdi-checkbox-marked-outline" :color="item.color"></v-icon>
    </template>

    <v-list-item-title v-text="item.title"></v-list-item-title>

    </v-list-item>
    <v-divider inset></v-divider>
  </v-list>

</v-container>


</template>
