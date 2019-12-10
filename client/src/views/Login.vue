<template>
  <div class="bg flex">
    <div class="left">
      <div class="left__container">
        <p class="text-white">
          Throughout my time as a student at Cassiopeia I have multiple times managed to pick the same
          disgusting beverages over and over again. Big thanks to the guys at <strong>Refrigerator Supreme!</strong> Now
          I can always keep track of, which beverages to avoid.
        </p>
        <div class="testimonial">
          <img src="../assets/login-testimonial-person.png" style="max-width: 40px"/>
          <div class="testimonial__person flex flex-col ml-4">
            <p>Anders Brams</p>
            <p>Head of F-Klubben</p>
          </div>
        </div>
      </div>
    </div>
    <div class="right">
      <div v-on:keydown.enter.capture="handleSubmit" class="flex max-w-3xl justify-center items-center">
        <div class="flex flex-col">
          <h1 class="heading" v-html="titleText" />
          <div class="controls flex flex-row align-center items-center mt-16">
            <input v-model="email" type="email" placeholder="Email" required class="input__email" />
            <p class="ml-4">@gmail.com</p>
          </div>
          <input v-model="password" type="password" placeholder="Password" required class="input__password" />
          <Button v-on:click="handleSubmit" class="w-1/6 mt-8" :text="submitText" />
          <p class="inline ml-3">or <button v-on:click="toggleView" class="text-blue-600 hover:underline hover:text-blue-500" v-text="alternativeText"></button></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Button from '../components/Button'
import CREATE_USER from '../graphql/CreateUser.gql'
import LOGIN from '../graphql/Login.gql'

export default {
  name: 'Login',
  components: {
    Button
  },
  data: () => ({
    email: '',
    password: '',
    type: 'login',
    errorMessage: ''
  }),
  beforeCreate () {
    let token = window.localStorage.getItem('apollo-token')
    if (token && token.split('.').length === 3) {
      this.$router.push({ name: 'home' })
    }
  },
  methods: {
    handleSubmit () {
      this.mutate()
    },
    toggleView () {
      this.type = this.type === 'login' ? 'create' : 'login'
    },
    mutate () {
      this.clearErrors()
      this.$apollo.mutate({
        mutation: this.type === 'login' ? LOGIN : CREATE_USER,
        variables: {
          email: this.email + '@gmail.com',
          password: this.password
        }
      }).then(this.handleLoginResponse).catch(this.handleError)
    },
    saveToken (token) {
      this.$token = token
      window.localStorage.setItem('apollo-token', token)
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
      return this.type === 'login' ? 'Sign in' : 'Create account'
    },
    alternativeText: function () {
      return this.type === 'login' ? 'create account' : 'sign in'
    },
    titleText: function () {
      return this.type === 'login' ? 'Sign in to start collaborating' : 'Create account to start collaborating'
    }
  }
}
</script>

<style lang="scss" scoped>
  .bg {
    background-color: #080808;
  }

  p {
    color: #fff;
  }

  .left {
    background-image: url(../assets/login-bg.svg);
    background-repeat: no-repeat;
    background-size: cover;
    @apply flex w-1/2 justify-center items-center h-screen;

    &__container {
      background-color: #080808;
      @apply flex flex-col justify-center items-center py-10 px-10 w-2/4;
    }
  }

  .testimonial {
    @apply flex flex-row w-full items-center mt-5;

    &__person {

    }
  }

  .right {
    @apply w-1/2 p-20 flex w-1/2 justify-center h-screen;
  }

  .heading {
    @apply text-6xl font-light text-white;
  }

  .input {
    &__email {
      @apply block shadow py-2 px-3 border-gray-200 border-solid border-b rounded rounded w-1/4;
    }

    &__password {
      @apply block shadow my-2 py-2 px-3 rounded rounded w-1/4;
    }
  }
</style>
