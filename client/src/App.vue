<template>
  <div id="app">
<!--    <div id="nav">
      <router-link to="/">Home</router-link> |
      <router-link to="/about">About</router-link>
    </div>
    <router-view/>-->
    <Login/>
  </div>
</template>

<script>
import Login from '@/views/Login'
export default {
  components: {
    Login
  }
}
</script>
