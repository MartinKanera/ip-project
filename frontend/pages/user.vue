<template>
  <div>
    <v-overlay color="black" opacity="1" :value="loading">
      <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
    <v-content>
      <v-container class="container-auth pa-5" fluid>
        <v-row no-gutters justify="center" align="center" class="min-h-screen">
          <v-sheet max-width="512" class="pa=5">
            <v-container class="pa-5" fluid>
              <v-row>
                <v-col class="title">{{ user.first_name }} {{ user.last_name }}</v-col>
              </v-row>
              <v-row>
                <v-col cols="6" align="end" class="body-2 field pb-0">First name</v-col>
                <v-col cols="6" class="body-2 pb-0">{{ user.first_name }}</v-col>
                <v-col cols="6" align="end" class="py-0 body-2 field">Last name</v-col>
                <v-col cols="6" class="body-2 py-0">{{ user.last_name }}</v-col>
                <v-col cols="6" align="end" class="py-0 body-2 field">Position</v-col>
                <v-col cols="6" class="body-2 py-0">{{ user.position }}</v-col>
                <v-col cols="6" align="end" class="py-0 body-2 field">Salary</v-col>
                <v-col
                  cols="6"
                  class="body-2 py-0"
                >{{ user.salary.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + ' Kč' }}</v-col>
                <v-col cols="6" align="end" class="py-0 body-2 field">Room</v-col>
                <v-col cols="6" class="body-2 py-0 link">
                  <nuxt-link class="link" :to="`/room?=id${user.room_id}`">{{ user.room }}</nuxt-link>
                </v-col>
                <v-col cols="6" align="end" class="py-0 body-2 field">Keys:</v-col>
                <v-col cols="6"></v-col>
              </v-row>
              <v-row v-if="user.keys.length <= 0">
                <v-col cols="6"></v-col>
                <v-col cols="6 body-2 py-0">No keys</v-col>
              </v-row>
              <v-row v-else v-for="key in user.keys">
                <v-col cols="6"></v-col>
                <v-col cols="6" class="body-2 py-0 link">
                  <nuxt-link class="link" :to="`/room?=id${key.id}`">{{ key.name }}</nuxt-link>
                </v-col>
              </v-row>
              <v-row>
                <v-col class="mt-5">
                  <nuxt-link to="/users" class="link">
                    <v-btn text>
                      <v-icon>mdi-arrow-left</v-icon>
                      <span>Go back</span>
                    </v-btn>
                  </nuxt-link>
                </v-col>
              </v-row>
            </v-container>
          </v-sheet>
        </v-row>
      </v-container>
    </v-content>
  </div>
</template>

<style scoped lang="sass" scoped>
.field 
  color: #29a19c

.link
  color: #b030b0
  text-decoration: none

  &:hover
    color: #ff00ff

.container-content 
  display: flex
  justify-content: center
  align-items: center
  flex-grow: 1

</style>

<script lang="ts">
import { defineComponent, ref, onBeforeMount } from '@vue/composition-api';
import axios from 'axios';

export default defineComponent({
  layout: 'browser',
  setup(_, setupContext) {
    const loading = ref(true);
    const user = ref({
      first_name: '',
      last_name: '',
      position: '',
      salary: 0,
      room: '',
      room_id: 0,
      keys: []
    });

    (async () => {
      loading.value = true;
      const jwt = localStorage.getItem('jwt') ?? false;

      if (jwt) {
        try {
          await setupContext.root.$store.dispatch('fetchUserData', jwt);
        } catch (e) {
          setupContext.root.$root.$router.replace('/');

          localStorage.removeItem('jwt');
        }
      } else {
        setupContext.root.$root.$router.replace('/');
      }

      const params = setupContext.root.$router.currentRoute.query;
      //@ts-ignore
      if (!isNaN(params.id)) {
        try {
          const response = await axios.get(
            process.env.API_URL + '/api/user/card.php',
            {
              params: params
            }
          );

          user.value = response.data;

          console.log(user.value);

          loading.value = false;

          document.title = `${response.data.first_name} ${response.data.last_name}`;
        } catch (e) {
          setupContext.root.$root.$router.replace('/error');
        }
      } else {
        setupContext.root.$root.$router.replace('/badrequest');
      }
    })();

    return {
      loading,
      user
    };
  },
  head: {
    title: 'loading...'
  }
});
</script>