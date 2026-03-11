<template>
  <router-view />
</template>

<script setup>
// App.vue just renders the current route
// Login.vue will show full page
// Layout.vue will show with sidebar/header/footer
</script>

<style>
/* Global styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Optional: Add any global styles you need */
</style>