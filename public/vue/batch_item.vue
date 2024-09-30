<script>
  export default {
    data() {
      return {
        firstname: "ciao",
        firstname: "saluti",
        overlay: false,
        batch_info: [],
        batch_jobs: [],
        batch_item: {}
      }
    },
    methods: {
      async getData(id) {
        this.overlay = true;  
        const route = VueRouter.useRoute();
        console.log(route.params.id)
        const res = await fetch("/api/batch/" + route.params.id );
        const finalRes = await res.json();
        this.batch_jobs = finalRes.batch_jobs;
        this.batch_info = finalRes.batch_info;
        this.batch_item = finalRes.batch_info[0];
        console.log(this.batch_info);
        console.log(this.batch_jobs);
        console.log(this.batch_item);
        this.overlay = false;
      },


    },
    mounted() {
      
      const route = VueRouter.useRoute();
      console.log(route.params.id)
      this.getData(route.params.id)
      
    }
  }
</script>


<template>

<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Batch info - {{ $route.params.id }}</v-toolbar-title>
</v-toolbar>


<v-form v-model="valid">
<v-container fluid>
  <v-row><v-text-field v-model="batch_item.id" label="ID"></v-text-field></v-row>
  <v-row><v-text-field v-model="batch_item.batch_uuid" label="batch_uuid"></v-text-field></v-row>
  <v-row><v-text-field v-model="batch_item.batch_options" label="batch_options"></v-text-field></v-row>
</v-container>
</v-form>


<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Jobs list</v-toolbar-title>
</v-toolbar>

<v-data-table :items="batch_jobs"></v-data-table>





<v-overlay
:model-value="overlay"
class="align-center justify-center"
>
<v-progress-circular
  color="primary"
  size="64"
  indeterminate
></v-progress-circular>
</v-overlay>

<pre>{{ listItems }}</pre>


</template>