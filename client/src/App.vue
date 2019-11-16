<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
export default {
  created () {
    if (this.$token == null) {
      // this.$router.push({ name: 'login' })
    }
  }
}
</script>
