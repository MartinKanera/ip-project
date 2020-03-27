<template>
  <v-form
    ref="form"
    :class="$vuetify.breakpoint.name !== 'xs' ? 'relative' : ''"
    class="pa-4 pa-sm-8"
    lazy-validation
  >
    <v-row no-gutters justify="center" align="center">
      <v-col class="pa-0" cols="12">
        <h1 class="title font-weight-bold text-center mt-8">Please log in</h1>
        <v-text-field
          v-model="username"
          class="mt-4"
          label="LOGIN"
          color="accent"
          type="text"
          validate-on-blur
          required
        ></v-text-field>
        <v-text-field
          v-model="password"
          class="mt-4"
          label="PASSWORD"
          color="accent"
          type="password"
          required
        ></v-text-field>
        <v-btn class="mt-8 normal-case" color="accent" @click="login" block>Log in</v-btn>
      </v-col>
    </v-row>
    <v-overlay color="black" absolute opacity="0.65" :value="loading">
      <v-progress-circular size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
    <v-snackbar
      v-model="snackbar"
      color="red"
      :timeout="2000"
      :vertical="$vuetify.breakpoint.name === 'xs'"
    >
      <div>{{ error }}</div>
      <v-btn icon @click="snackbar = false"></v-btn>
    </v-snackbar>
  </v-form>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from '@vue/composition-api';
import axios from 'axios';

export default defineComponent({
  setup(_, setupContext) {
    const username = ref('');
    const password = ref('');
    const loading = ref(false);
    const snackbar = ref(false);
    const error = ref('Your session has expired');

    async function login() {
      loading.value = true;

      try {
        const response = await axios.post(
          process.env.API_URL + '/api/auth/login.php',
          {
            data: {
              login: username.value,
              password: password.value
            }
          }
        );

        const data = await response.data;

        localStorage.setItem('jwt', data.jwt);

        setupContext.root.$root.$router.push('/users');
      } catch (e) {
        error.value = 'Login or password is incorrect';
        snackbar.value = true;
      }

      loading.value = false;
    }

    onMounted(async () => {
      const jwt = localStorage.getItem('jwt') ?? false;

      if (jwt) {
        try {
          loading.value = true;
          await setupContext.root.$store.dispatch('fetchUserData', jwt);

          setupContext.root.$root.$router.push('/users');
        } catch (e) {
          loading.value = false;
          snackbar.value = true;

          localStorage.removeItem('jwt');
        }
      }
    });

    return {
      login,
      username,
      password,
      snackbar,
      loading,
      error
    };
  }
});
</script>
