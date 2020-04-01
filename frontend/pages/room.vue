<template>
  <div>
    <v-overlay color="black" opacity="1" :value="loading">
      <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
    <v-content>
      <v-container class="container-auth pa-5" fluid>
        <v-row no-gutters justify="center" align="center" class="min-h-screen">
          <v-sheet width="512" class="pa=5">
            <v-container class="pa-5" fluid>
              <v-row>
                <v-col class="title">Room number {{ room.number }}</v-col>
              </v-row>
              <v-row>
                <v-col cols="6" align="end" class="body-2 field pb-0">Number</v-col>
                <v-col cols="6" class="body-2 pb-0">{{ room.number }}</v-col>
                <v-col cols="6" align="end" class="body-2 field py-0">Name</v-col>
                <v-col cols="6" class="body-2 py-0">{{ room.name }}</v-col>
                <v-col cols="6" align="end" class="body-2 field py-0">Average salary</v-col>
                <v-col cols="6" class="body-2 py-0">{{ room.average + ' Kč' }}</v-col>
                <v-col cols="6" align="end" class="py-0 body-2 field">Telephone</v-col>
                <v-col cols="6" class="body-2 py-0">{{ room.telephone }}</v-col>
              </v-row>
              <v-row class="mt-2"></v-row>
              <v-row v-if="room.people.length <= 0">
                <v-col cols="6" align="end" class="py-0 body-2 field">People:</v-col>
                <v-col cols="6" class="body-2 py-0">Nobody here</v-col>
              </v-row>
              <v-row v-else v-for="(user, index) in room.people">
                <v-col v-if="index === 0" cols="6" align="end" class="py-0 body-2 field">People</v-col>
                <v-col v-else cols="6"></v-col>
                <v-col class="py-0 body-2">
                  <nuxt-link
                    class="link"
                    :to="`/user?id=${user.id}`"
                  >{{ user.first_name }} {{ user.last_name }}</nuxt-link>
                </v-col>
              </v-row>
              <v-row class="mt-2"></v-row>
              <v-row v-if="room.keys.length <= 0">
                <v-col cols="6" align="end" class="py-0 body-2 field">Keys:</v-col>
                <v-col cols="6" class="body-2 py-0">No keys</v-col>
              </v-row>
              <v-row v-else v-for="(key, index) in room.keys">
                <v-col v-if="index === 0" cols="6" align="end" class="py-0 body-2 field">Keys:</v-col>
                <v-col v-else cols="6"></v-col>
                <v-col class="py-0 body-2">
                  <nuxt-link
                    class="link"
                    :to="`/user?id=${key.id}`"
                  >{{ key.first_name }} {{ key.last_name }}</nuxt-link>
                </v-col>
              </v-row>
              <v-row>
                <v-col class="mt-5">
                  <nuxt-link to="/rooms" class="link">
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

<style lang="sass">
.field 
  color: #29a19c

.link
  color: #b030b0 !important
  text-decoration: none

  &:hover
    color: #ff00ff !important

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
    const room = ref({
      number: 0,
      name: '',
      telephone: 0,
      people: [],
      average: '',
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
      if (params.id && !isNaN(params.id)) {
        try {
          const response = await axios.get(
            process.env.API_URL + '/api/room/card.php',
            {
              params: params
            }
          );

          room.value = response.data;
          loading.value = false;

          document.title = `Room #${response.data.number}`;
        } catch (e) {
          setupContext.root.$root.$router.replace('/error');
        }
      } else {
        setupContext.root.$root.$router.replace('/badrequest');
      }
    })();

    return {
      loading,
      room
    };
  },
  head: {
    title: 'loading...'
  }
});
</script>