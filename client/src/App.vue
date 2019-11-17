<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
export default {
  created () {
    this.$token = window.sessionStorage.getItem('token')
    if (this.$token == null) {
      this.$router.push({ name: 'login' })
    }
  }
}
</script>
