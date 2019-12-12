<template>
    <div id="app">
        <router-view/>
    </div>
</template>

<script>
import '@/assets/tailwind.css'
import '@voerro/vue-tagsinput/dist/style.css'

export default {
    name: 'App',
    created () {
        this.$token = window.localStorage.getItem('apollo-token')
        if (this.$token == null) {
            this.$router.push({ name: 'login' })
        }
    }
}
</script>

<style lang="scss">
  #app {
    background: theme('colors.bg.dark')
  }
</style>
