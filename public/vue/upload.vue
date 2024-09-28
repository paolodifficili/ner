<!-- js/vue-components/vue-footer.vue -->

<template>


<p>Upload.</p>



<v-file-input clearable id="picker" label="File input" v-model="files"></v-file-input>

<v-btn  color="primary" elevation="8" size="large" @click="goToProfile">Upload</v-btn>




<v-progress-linear
color="light-blue"
height="10"
model-value="10"
striped
></v-progress-linear>

<p>https://github.com/muxinc/upchunk</p>

</template>

<script>
  export default {
    data() {
      return {
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

      goToProfile() {

        console.log(this.files);

        const upload = UpChunk.createUpload({
          method : 'GET',
          endpoint: 'https://www.ruggeri.info/mypd/flow-upload.php',
          file: this.files,
          chunkSize: 30720, // Uploads the file in ~30 MB chunks
        });
        
      },
    },
    mounted() {
      
    }
  }
</script>