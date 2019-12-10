<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
import '@/assets/tailwind.css'
export default {
  created () {
    this.$token = window.localStorage.getItem('apollo-token')
    if (this.$token == null) {
      this.$router.push({ name: 'login' })
    }
  }
}
</script>
