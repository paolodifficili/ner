<script>
  export default {
    data() {
      return {
        dialog: false,
        power: 10,
        files: [],
        listItems: []
      }
    },
    methods: {
      async getData() {
        const res = await fetch("https://jsonplaceholder.typicode.com/posts");
        const finalRes = await res.json();
        this.listItems = finalRes;
      },

      uploadBar() {
        console.log(this.power);
        this.power = this.power + 10;
      },

      uploadFile() {

        console.log('uploadFile');
        console.log(this.files.name);

        const ea = {
            'X-UP-FILENAME': this.files.name,
            'X-UP-UUID': Date.now(),
        };


        const upload = UpChunk.createUpload({
          method : 'PUT',
          endpoint: '/api/mux',
          headers : ea,
          file: this.files,
          chunkSize: 2048, // Uploads the file in ~30 MB chunks
        });

        upload.on('error', (err) => {
            console.error('💥 🙀', err.detail);
            console.log(err, true, new Date());
        });

        upload.on('progress', (progress) => {
            console.log(`So far we've uploaded ${progress.detail}% of this file.`);
            // myBar.style.width = progress.detail + "%";
            this.power = Math.floor(progress.detail);

            console.log(this.power);
        });
    
        upload.on('success', (msg) => {
            console.log(msg);
            console.log("Upload success!");
            this.dialog = true;
        });
        
      },
    },
    mounted() {
      
    }
  }
</script>

<template>

<v-toolbar  color="blue-grey"  dark  flat>
<v-toolbar-title>Upload file (chunk)</v-toolbar-title>
</v-toolbar>

<v-file-input clearable id="picker" label="File input" v-model="files"></v-file-input>
<v-btn  color="primary" elevation="8" size="large" @click="uploadFile">Upload</v-btn>

<v-progress-linear 
color="blue-grey" height="20" v-model="power">
<strong>{{ Math.ceil(power) }}%</strong>
</v-progress-linear>
<v-list :items="listItems"></v-list>
<p>https://github.com/muxinc/upchunk</p>


 <v-dialog
      v-model="dialog"
      width="auto"
    >
      <v-card
        max-width="400"
        prepend-icon="mdi-update"
        text="Upload complete."
        title="Upload complete."
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
