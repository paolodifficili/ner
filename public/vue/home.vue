<!-- js/vue-components/vue-main-component.vue -->

<template>





<v-row align="center" justify="center" dense>


    <v-col cols="12" md="6">
      <v-card class="mx-auto" title="Upload">
        <v-card-text>Load file to check</v-card-text>
        <v-card-actions>
          <v-btn @click="goToUpload" size="large" variant="outlined">Upload</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>

     <v-col cols="12" md="6">
      <v-card class="mx-auto" title="Config">
        <v-card-text>Show configuration search engine</v-card-text>
        <v-card-actions>
          <v-btn @click="goToConfig" size="large" variant="outlined">Show config</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>

    <v-col cols="12" md="6">
      <v-card class="mx-auto" title="Batch">
        <v-card-text>Show batch list management</v-card-text>
        <v-card-actions>
          <v-btn @click="goToBatch" size="large" variant="outlined">Show batch</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>

  <v-col cols="12" md="6">
      <v-card class="mx-auto" title="New Batch">
        <v-card-text>Crate a new batch ...</v-card-text>
        <v-card-actions>
          <v-btn @click="goToNewBatch" size="large" variant="outlined">New Batch</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>

    <v-col cols="12" md="6">
      <v-card class="mx-auto" title="File list">
        <v-card-text>Manage uploaded files</v-card-text>
        <v-card-actions>
          <v-btn  size="large" variant="outlined">File list</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>

    <v-col cols="12" md="6">
      <v-card class="mx-auto" title="File list">
        <v-card-text>Manage uploaded files</v-card-text>
        <v-card-actions>
          <v-btn  size="large" variant="outlined">File list</v-btn>
        </v-card-actions>
      </v-card>
    </v-col>


  </v-row>


<div>
  <strong>Current route path:</strong> {{ $route.fullPath }}

  <p>https://vuetifyjs.com/en/getting-started/installation/#using-laravel-mix</p>
 
 
</div>



</template>

<script>
export default {
data() {
  return {
    headline: 'Vue Queue Main Component Headline Mgr'
  };
},

mounted() {
  console.log('Vue Main Component mounted');
},

methods: {
  goToAbout() {
    console.log('goToAbout');
    this.$router.push('/upload')
  },
  goToUpload() {
    console.log('goToUpload');
    this.$router.push('/upload')
  },
  goToConfig() {  console.log('goToConfig');  this.$router.push('/config') },
  goToBatch() {  console.log('goToBatch');  this.$router.push('/batch') },
  goToNewBatch() {  console.log('batchnew');  this.$router.push('/batchnew') },
},

};
</script>