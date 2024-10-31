<template>
<v-responsive class="border rounded">
<v-app :theme="theme">

<v-app-bar title="Mgr" class="px-3">
<v-spacer></v-spacer>
<RouterLink to="/"><v-btn>Home</v-btn></RouterLink>
<RouterLink to="/upload"><v-btn>Upload</v-btn></RouterLink>
<RouterLink to="/batch"><v-btn>Batch</v-btn></RouterLink>
<RouterLink to="/batchnew"><v-btn>Batch new</v-btn></RouterLink>

<RouterLink to="/crud2">
<v-icon>mdi-heart</v-icon>
</RouterLink>

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

  function onClick () {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
  }
</script>

