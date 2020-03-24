<template>
  <div class="login-wrap">
    <v-card class="login" color="secondary" flat>
      <form>
        <v-text-field
          v-model="username"
          class="mb-6"
          :counter="12"
          label="Login"
          required
        ></v-text-field>
        <v-text-field
          v-model="password"
          class="mb-6"
          label="Password"
          type="password"
          required
        ></v-text-field>
        <v-btn class="custom-btn" color="accent" @click="login">Login</v-btn>
      </form>
    </v-card>
  </div>
</template>

<style lang="sass" scoped>
.login-wrap
  width: 33%
  margin: auto

  .login
    padding: 50px
    width: 100%
</style>

<style scoped>
@media only screen and (max-width: 978px) {
  .login-wrap {
    width: 75%;
  }
}
</style>

<script lang="ts">
import { defineComponent, reactive, ref } from '@vue/composition-api';
import axios from 'axios';

export default defineComponent({
  setup() {
    const username = ref('');
    const password = ref('');
    const idk = ref(null);

    async function login() {
      try {
        const response = await axios.post(
          'http://a2017kanema.delta-studenti.cz/api/auth/login.php',
          {
            data: {
              login: 'franta',
              password: 'Heslo123'
            }
          }
        );

        console.log(response.data);
      } catch (e) {
        console.log(e);
      }
    }

    return {
      login,
      username,
      password
    };
  }
});
</script>
