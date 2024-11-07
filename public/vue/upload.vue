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

      startBatch () {
        this.addLog('Batch starting .....');
        this.updateLog(2, "Batch started!")
        this.stopLog(2)

      },

      createBatch() {

        console.log('runBatch .....' , this.execBatch);
        this.addLog('Creating a batch .....');

        this.file_uuid = '1730979689955';

        const BATCH_UUID = 'BATCH____' + Date.now();
        console.log('runBatch .....' , BATCH_UUID);

        const ops = {
          action_selected : 'RUN_ENGINE',
          engines_selected : [],
          files_selected : [],
          files_uuid : [this.file_uuid]
        };

        const obj = {
            batch_uuid:  BATCH_UUID,
            batch_description : BATCH_UUID,
            batch_action: 'RUN_ENGINE',
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
            }

              console.log(data);
              console.log(data.data.batch_uuid);

              this.batch_id = data.data.batch_uuid;

              this.updateLog(1, "Batch created!")
              this.stopLog(1)

              this.startBatch()

            
            
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

        console.log('uploadFile');
        console.log(this.files.name);

        this.file_uuid = Date.now();

        const ea = {
            'X-UP-FILENAME': this.files.name,
            'X-UP-UUID': this.file_uuid,
        };

        this.resetLog();
        this.addLog('Caricamento ...');

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
            console.error('💥 🙀', err.detail);
            console.log(err, true, new Date());

            this.dialogInfo.title = "Upload error";
            this.dialogInfo.text = err.message;
            this.dialog = true;
        });

        upload.on('progress', (progress) => {
            console.log(progress);
            console.log(`So far we've uploaded ${progress.detail}% of this file.`);
            // myBar.style.width = progress.detail + "%";
            this.power = Math.floor(progress.detail);

            this.updateLog(0, 'Loading ' + this.power + "%")

            console.log(this.power);
        });
    
        upload.on('success', (msg) => {
            console.log(msg);
            console.log("Upload success!");

            this.updateLog(0, "Upload success!")
            this.stopLog(0)

            console.log('FileUUID:', this.file_uuid);

            // this.dialogInfo.title = "Upload status";
            // this.dialogInfo.text = "Upload success!";
            // this.dialog = true;

            this.createBatch();

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
      this.overlay = false
      clearInterval(this.interval)
      this.stopLastItem()
    },

    updateLog(id, text) {
      this.items[id].title = text;
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

<v-toolbar  color="blue-grey"  dark  flat>
<v-toolbar-title>Upload file (chunk) - MAX 20MB</v-toolbar-title>
</v-toolbar>

<v-sheet class="mx-auto">

<v-file-input clearable id="picker" show-size label="File input" v-model="files"></v-file-input>

<v-btn  block color="primary" elevation="8" size="large" @click="uploadFile">Upload</v-btn>
<v-switch v-model="execBatch"
              color="indigo"
              label="Run Batch"
              hide-details
            ></v-switch>

<v-btn  block color="primary" elevation="8" @click="createBatch">Test</v-btn>
<v-btn  block color="primary" elevation="8" @click="resetLog">resetLog</v-btn>
<v-btn  block color="primary" elevation="8" @click="addLog('ccc')">addLog</v-btn>
<v-btn  block color="primary" elevation="8" @click="stopLog(0)">stopLog(0) </v-btn>
<v-btn  block color="primary" elevation="8" @click="stopLastLog">stopLastLog</v-btn>
<v-btn  block color="primary" elevation="8" @click="updateLogRandom(0)">updateLogRandom(0)</v-btn>
<v-btn  block color="primary" elevation="8" @click="createBatch()">createBatch</v-btn>

<v-divider thickness="10"  inset></v-divider>

<v-progress-linear 
color="blue-grey" height="20" v-model="power">
<strong>{{ Math.ceil(power) }}%</strong>
</v-progress-linear>
<v-list :items="listItems"></v-list>
<p>https://github.com/muxinc/upchunk</p>

</v-sheet>

 <v-list>
    <v-list-subheader>REPORTS</v-list-subheader>

    <v-list-item
      v-for="(item, i) in items"
      :key="i"
      :value="item"
      color="primary"
      rounded="xl"
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
      <v-icon :icon="item.icon" :color="item.color"></v-icon>
    </template>

    <v-list-item-title v-text="item.title"></v-list-item-title>

    </v-list-item>
    <v-divider inset></v-divider>
  </v-list>

 <v-dialog  v-model="dialog"  width="auto" >
      <v-card
        max-width="400"
        prepend-icon="mdi-update"
        :text="dialogInfo.text"
        :title="dialogInfo.title"
      >

      
        <template v-slot:actions>
          <v-btn
            class="ms-auto"
            text="Ok"
            @click="dialog = false"
          ></v-btn>
        </template>
      </v-card>
    </v-dialog>

</template>
