<!-- js/vue-components/vue-main-component.vue -->

<template>
<div>
  <h2>{{ headline }}</h2>
  <strong>Current route path:</strong> {{ $route.fullPath }}

  <p>https://vuetifyjs.com/en/getting-started/installation/#using-laravel-mix</p>

  <p>Manager Q</p>

  <v-btn size="x-large" variant="tonal" @click="goToAbout">Go to Upload</v-btn>

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
},

};
</script>