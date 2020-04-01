<template>
  <v-app dark>
    <div>
      <v-toolbar color="secondary">
        <v-toolbar-title>Database browser</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-app-bar-nav-icon @click="openDrawer"></v-app-bar-nav-icon>
      </v-toolbar>
      <v-navigation-drawer
        v-model="drawer"
        app
        right
        dark
        floating
        :temporary="true"
        width="300px"
        color="secondary"
      >
        <v-list style="margin-top: 65px">
          <v-list-item>
            <v-list-item-content align="center" justify="center">
              <v-list-item-title class="title">{{ fullName }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item style="margin-top: auto">
            <v-list-item-content align="center" justify="center">
              <v-btn outlined color="#ffd31d" @click="openSettings">Settings</v-btn>
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <v-list-item-content align="center" justify="center">
              <v-btn outlined color="accent" @click="signOut">Sign out</v-btn>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-spacer></v-spacer>
        <v-list>
          <v-list-item>
            <v-list-item-content>
              <v-btn text block :color="route == 'users' ? 'accent' : ''" to="/users">USERS</v-btn>
            </v-list-item-content>
            <v-list-item-content>
              <v-btn text block :color="route == 'rooms' ? 'accent' : ''" to="/rooms">ROOMS</v-btn>
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <v-list-item-content></v-list-item-content>
          </v-list-item>
        </v-list>
      </v-navigation-drawer>
    </div>
    <v-content>
      <v-container class="pa-5 mt-5" fluid>
        <nuxt></nuxt>
      </v-container>
    </v-content>
    <v-overlay v-model="overlay" opacity="0.5" color="black">
      <v-container fluid>
        <v-card width="300px" height="350px">
          <v-form v-model="valid" ref="form">
            <v-row>
              <v-col></v-col>
              <v-col align="right" cols="10">
                <v-btn class="mx-2" icon fab dark small color="primary" @click="overlay = false">
                  <v-icon>mdi-close-thick</v-icon>
                </v-btn>
              </v-col>
            </v-row>
            <v-form>
              <v-row>
                <v-col></v-col>
                <v-col cols="10" align="right">
                  <v-text-field
                    v-model="oldPassword"
                    class="mt-4"
                    label="OLD PASSWORD"
                    color="accent"
                    type="password"
                    validate-on-blur
                    required
                    :rules="
                      [
                        v => !!v || 'Old password is required'
                      ]"
                  ></v-text-field>
                </v-col>
                <v-col></v-col>
              </v-row>
            </v-form>
            <v-row>
              <v-col></v-col>
              <v-col cols="10" align="right">
                <v-text-field
                  v-model="newPassword"
                  class="mt-4"
                  label="NEW PASSWORD"
                  color="accent"
                  type="password"
                  validate-on-blur
                  required
                  :rules="
                      [
                        v => !!v || 'New password is required',
                        v => v.length >= 6 || 'Min 6 chars no whitespaces'
                      ]"
                ></v-text-field>
              </v-col>
              <v-col></v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-btn
                  class="mt-8 normal-case"
                  color="accent"
                  @click="changePassword"
                  block
                >Change password</v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card>
        <v-overlay v-model="chagePasswordOverlay" opacity="1" color="black" absolute>
          <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
        </v-overlay>
      </v-container>
    </v-overlay>
    <v-snackbar
      v-model="snackbar"
      :color="color"
      :timeout="2000"
      :vertical="$vuetify.breakpoint.name === 'xs'"
    >
      <div>{{ message }}</div>
      <v-btn icon @click="snackbar = false"></v-btn>
    </v-snackbar>
  </v-app>
</template>

<script lang="ts">
import { defineComponent, ref, watchEffect } from '@vue/composition-api';
import axios from 'axios';
import { VForm } from '../types';

export default defineComponent({
  setup(_, setupContext) {
    const fullName = ref('');
    const drawer = ref(false);
    const overlay = ref(false);
    const chagePasswordOverlay = ref(false);
    const oldPassword = ref('');
    const newPassword = ref('');

    const color = ref('');
    const message = ref('');
    const snackbar = ref(false);

    watchEffect(() => {
      fullName.value = setupContext.root.$store.getters.fullName;
    });

    function signOut() {
      localStorage.removeItem('jwt');
      setupContext.root.$router.replace('/');
    }

    function openDrawer() {
      drawer.value = !drawer.value;
    }

    function openSettings() {
      overlay.value = true;
      drawer.value = false;
    }

    const route = ref('');
    const valid = ref(true);

    watchEffect(() => (route.value = setupContext.root.$route.name ?? ''));

    async function changePassword() {
      const jwt = localStorage.getItem('jwt') ?? false;
      // @ts-ignore
      if ((setupContext.refs.form as VForm).validate()) {
        valid.value = true;
        if (jwt) {
          try {
            chagePasswordOverlay.value = true;

            const response = await axios({
              method: 'POST',
              url: process.env.API_URL + '/api/auth/new-password.php',
              data: {
                data: {
                  id: setupContext.root.$store.getters.userId,
                  old_password: oldPassword.value,
                  new_password: newPassword.value
                }
              },
              headers: {
                Authorization: `Bearer ${jwt}`
              }
            });

            color.value = 'green';
            overlay.value = false;
            message.value = 'Password changed successfully';
            oldPassword.value = '';
            newPassword.value = '';
          } catch (e) {
            color.value = 'red';
            message.value = 'Old password is incorrect';
          }

          snackbar.value = true;
          chagePasswordOverlay.value = false;
        }
      } else {
        valid.value = false;
      }
    }

    return {
      fullName,
      signOut,
      drawer,
      openDrawer,
      overlay,
      openSettings,
      oldPassword,
      newPassword,
      changePassword,
      chagePasswordOverlay,
      color,
      message,
      snackbar,
      route,
      valid
    };
  }
});
</script>
