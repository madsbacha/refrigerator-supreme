<template>
  <div class="bg-gray-200 flex justify-center h-screen content-center flex-shrink">
    <div class="w-9/12 sm:w-2/3 md:w-1/2 lg:w-1/3 xl:w-1/4 pt-64">
      <h1 class="font-sans text-3xl mb-5 font-bold tracking-wide" v-html="titleText"></h1>
      <input v-model="email" type="email" placeholder="Email" class="block shadow py-2 px-3 border-gray-200 border-solid border-b rounded rounded-b-none w-full" />
      <input v-model="password" type="password" placeholder="Password" class="block shadow mb-4 py-2 px-3 rounded rounded-t-none w-full" />
      <button v-on:click="handleSubmit" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500" v-text="submitText"></button>
      <p class="inline ml-3">or <button v-on:click="toggleView" class="text-blue-600 hover:underline hover:text-blue-500" v-text="alternativeText"></button></p>
    </div>
  </div>
</template>

<script>
import '@/assets/tailwind.css'
import gql from 'graphql-tag'
export default {
  name: 'Login',
  data: () => ({
    email: '',
    password: '',
    type: 'login'
  }),
  methods: {
    handleSubmit () {
      if (this.type === 'login') {
        this.login()
      } else {
        this.create()
      }
    },
    toggleView () {
      this.type = this.type === 'login' ? 'create' : 'login'
    },
    async login () {
      const result = await this.$apollo.mutate({
        mutation: gql`mutation($email: String!, $password: String!) {
          Login(email: $email, password: $password) {
            success
            token
          }
        }`,
        variables: {
          email: this.email,
          password: this.password
        }
      })

      if (result['data']['Login']['success']) {
        this.saveToken(result['data']['Login']['token'])
        await this.$router.push({ name: 'home' })
      }
    },
    async create () {
      const result = await this.$apollo.mutate({
        mutation: gql`mutation($email: String!, $password: String!) {
          CreateUser(email: $email, password: $password) {
            success
            token
          }
        }`,
        variables: {
          email: this.email,
          password: this.password
        }
      })

      if (result['data']['CreateUser']['success']) {
        this.saveToken(result['data']['CreateUser']['token'])
        await this.$router.push({ name: 'home' })
      }
    },
    saveToken (token) {
      this.$token = token
    }
  },
  computed: {
    submitText: function () {
      return this.type === 'login' ? 'Login' : 'Create'
    },
    alternativeText: function () {
      return this.type === 'login' ? 'create account' : 'login'
    },
    titleText: function () {
      return this.type === 'login' ? 'Login to<br>Refrigerator Supreme' : 'Create account for<br>Refrigerator Supreme'
    }
  }
}
</script>
