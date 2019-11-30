<template>
  <div class="bg-gray-200 flex justify-center h-screen content-center flex-shrink">
    <div v-on:keydown.enter.capture="handleSubmit" class="w-9/12 sm:w-2/3 md:w-1/2 lg:w-1/3 xl:w-1/4 pt-64">
      <h1 class="font-sans text-3xl mb-5 font-bold tracking-wide" v-html="titleText"></h1>
      <input v-model="email" type="email" placeholder="Email" required class="block shadow py-2 px-3 border-gray-200 border-solid border-b rounded rounded-b-none w-full" />
      <input v-model="password" type="password" placeholder="Password" required class="block shadow mb-2 py-2 px-3 rounded rounded-t-none w-full" />
      <p class="text-sm text-red-600">{{ errorMessage }}</p>
      <button v-on:click="handleSubmit" class="bg-green-600 text-white mt-2 py-2 px-4 rounded hover:bg-green-500" v-text="submitText"></button>
      <p class="inline ml-3">or <button v-on:click="toggleView" class="text-blue-600 hover:underline hover:text-blue-500" v-text="alternativeText"></button></p>
    </div>
  </div>
</template>

<script>
import gql from 'graphql-tag'
export default {
  name: 'Login',
  data: () => ({
    email: '',
    password: '',
    type: 'login',
    errorMessage: ''
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
    login () {
      this.clearErrors()
      this.$apollo.mutate({
        mutation: gql`mutation($email: String!, $password: String!) {
          Login(email: $email, password: $password) {
            token
          }
        }`,
        variables: {
          email: this.email,
          password: this.password
        }
      }).then(this.handleLoginResponse).catch(this.handleError)
    },
    create () {
      this.clearErrors()
      this.$apollo.mutate({
        mutation: gql`mutation($email: String!, $password: String!) {
        CreateUser(email: $email, password: $password) {
          token,
          user { id, email }
        }
      }`,
        variables: {
          email: this.email,
          password: this.password
        }
      }).then(this.handleLoginResponse).catch(this.handleError)
    },
    saveToken (token) {
      this.$token = token
      window.sessionStorage.setItem('token', token)
    },
    clearErrors () {
      this.errorMessage = ''
    },
    handleError (errors) {
      if (typeof errors['graphQLErrors'] === 'undefined') {
        this.errorMessage = 'An unknown error occurred'
        console.log(errors)
      } else {
        for (let i = 0; i < errors['graphQLErrors'].length; i++) {
          const error = errors['graphQLErrors'][i]
          if (error.extensions.category === 'Authentication') {
            this.errorMessage = 'Wrong Email and/or password.'
          } else if (error.extensions.category === 'BusinessLogic' || error.extensions.category === 'ArgumentError') {
            this.errorMessage = error.message
          }
        }
      }
    },
    handleLoginResponse (response) {
      let datakey = typeof response['data']['Login'] === 'undefined' ? 'CreateUser' : 'Login'
      this.saveToken(response['data'][datakey]['token'])
      this.$router.push({ name: 'home' })
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
