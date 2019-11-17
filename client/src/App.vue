<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
import '@/assets/tailwind.css'
export default {
  created () {
    this.$token = window.sessionStorage.getItem('token')
    if (this.$token == null) {
      this.$router.push({ name: 'login' })
    }
  }
}
</script>
