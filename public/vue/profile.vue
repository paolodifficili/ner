<script>


  export default {
    data() {
      return {
        firstname: "ciao",
        firstname: "saluti",
        overlay: false,
        listItems: []
      }
    },
    methods: {
      async getData(id) {
        this.overlay = true;  
        const route = VueRouter.useRoute();
        console.log(route.params.id)
        const res = await fetch("https://jsonplaceholder.typicode.com/posts/" + route.params.id );
        const finalRes = await res.json();
        this.listItems = finalRes;
        console.log(this.listItems);
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
    <v-toolbar-title>Profile info - {{ $route.params.id }}</v-toolbar-title>
</v-toolbar>


<v-form v-model="valid">
<v-container fluid>
  <v-row>
  
      <v-text-field
        v-model="listItems.title"
        :counter="10"
        :rules="nameRules"
        label="First name"
        hide-details
        required
      ></v-text-field>
  </v-row>
<v-row>
  
      <v-text-field
        v-model="listItems.body"
        :counter="10"
        :rules="nameRules"
        label="Last name"
        hide-details
        required
      ></v-text-field>
    </v-row>

    <v-row      
    >
      <v-text-field
        v-model="email"
        :rules="emailRules"
        label="E-mail"
        hide-details
        required
      ></v-text-field>

  </v-row>
</v-container>
</v-form>




















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