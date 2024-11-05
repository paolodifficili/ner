<template>
<v-responsive class="border rounded">
<v-app :theme="theme">

<v-app-bar title="Mgr" class="px-3">
<v-spacer></v-spacer>
<RouterLink to="/"><v-btn>Home</v-btn></RouterLink>
<RouterLink to="/upload"><v-btn>Upload</v-btn></RouterLink>
<RouterLink to="/batch"><v-btn>Batch</v-btn></RouterLink>
<RouterLink to="/batchnew"><v-btn>Batch new</v-btn></RouterLink>

<v-btn stacked>
      <v-badge
        :content="counter"
        color="error"
      >
        <v-icon icon="mdi-newspaper-variant-outline"></v-icon>
      </v-badge>
 
</v-btn>



<v-btn
  :prepend-icon="theme === 'light' ? 'mdi-weather-sunny' : 'mdi-weather-night'"
  text="TT"
  slim
  @click="onClick"
></v-btn>

</v-app-bar>

  <v-main>
    <v-container fluid>
      <RouterView />
    </v-container>
  </v-main>
</v-app>

</v-responsive>
</template>


<script setup>
  import { ref } from 'vue'

  const theme = ref('light')
  const counter = ref(0);


  var messages = [];
  var cntMessages = 1;

  function onClick () {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
  }

  console.log('app.vue initi GotMessage')
  window.Echo.channel('chat')
    .listen('GotMessage', (e) => {
      console.log('app.vue GotMessage');
      console.log(e);
      counter.value++;
  });


</script>

