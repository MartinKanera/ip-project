<template>
  <div>
    <v-overlay color="black" opacity="1" :value="loading">
      <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
    <v-container fluid>
      <!-- :headers="" :items="" -->

      <v-data-table
        :items="users"
        :headers="headers"
        sort-by="first_name"
        class="elevation-4"
        hide-default-footer
        :loading="tableLoading"
      >
        <template v-slot:top>
          <v-toolbar flat color="secondary">
            <v-toolbar-title>Users</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-btn color="accent" dark>New User</v-btn>

            <v-dialog v-model="dialog" max-width="500px">
              <v-card>
                <v-card-text>
                  <v-container>
                    <v-form ref="form" v-model="valid">
                      <v-row>
                        <v-col cols="6">
                          <span class="headline">Edit user</span>
                        </v-col>
                        <v-col cols="6">
                          <v-switch
                            color="accent"
                            v-model="editedItem.admin"
                            label="Admin"
                            style="margin-top: 0; margin-left: 45%"
                          ></v-switch>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                          <v-text-field
                            color="accent"
                            v-model="editedItem.first_name"
                            label="First name"
                            :rules="nameRules"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                          <v-text-field
                            color="accent"
                            v-model="editedItem.last_name"
                            label="Last name"
                            :rules="nameRules"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                          <v-text-field
                            color="accent"
                            required
                            v-model="editedItem.position"
                            label="Positon"
                            :rules="[
                              v => !!v || 'Position is required']"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                          <v-text-field
                            color="accent"
                            required
                            type="number"
                            v-model="editedItem.salary"
                            label="Salary"
                            :rules="[
                              v => !!v || 'Salary is required',
                              ]"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="8">
                          <v-select
                            :items="rooms"
                            label="Room"
                            v-model="editedItem.text"
                            color="accent"
                            item-color="accent"
                            required
                          ></v-select>
                        </v-col>
                      </v-row>
                      <v-row>
                        <v-col cols="12" sm="6">
                          <v-text-field
                            color="accent"
                            required
                            v-model="editedItem.login"
                            label="Login"
                            :rules="[
                              v => !!v || 'Login is required']"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6">
                          <v-text-field
                            type="password"
                            color="accent"
                            v-model="editedItem.password"
                            label="Password"
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-form>
                    <v-row>
                      <v-col md="4" v-for="(room, index) in rooms">
                        <v-checkbox
                          color="accent"
                          v-model="editedItem.selected_rooms_id"
                          :value="room.room_id"
                          :label="room.text"
                          :key="index"
                        ></v-checkbox>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="red" text @click="dialog = false">Cancel</v-btn>
                  <v-btn color="green" text @click="saveChanges">Save</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-toolbar>
        </template>
        <template v-slot:item="{ item }">
          <tr>
            <td>{{ item.first_name }}</td>
            <td>{{ item.last_name }}</td>
            <td>{{ item.room }}</td>
            <td>{{ item.telephone }}</td>
            <td>{{ item.position }}</td>
            <td>
              <v-content justify="center" align="center">
                <v-icon v-if="isAdmin" small class="mr-2" @click="editItemDialog(item)">mdi-pencil</v-icon>
                <v-icon
                  v-if="isAdmin"
                  small
                  class="mr-2"
                  @click="() => {
                  selectedUserId = item.id
                  deleteDialog = true;
                  }"
                >mdi-delete</v-icon>
                <nuxt-link :to="'/user?id=' + item.id">
                  <v-icon small>mdi-folder-information</v-icon>
                </nuxt-link>
              </v-content>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-container>
    <v-dialog v-model="deleteDialog" max-width="310">
      <v-card>
        <v-card-title class="headline" style="margin-left: -5px">Delete user?</v-card-title>
        <v-card-actions>
          <v-btn color="accent" text @click="deleteDialog = false">Don't delete user</v-btn>
          <v-spacer></v-spacer>

          <v-btn color="red" text @click="deleteUser">Delete user</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<style lang="sass">
</style>
<style>
</style>

<script lang="ts">
import {
  defineComponent,
  ref,
  watchEffect,
  onMounted,
  Ref
} from '@vue/composition-api';
import axios from 'axios';
import { VForm } from '../types';

export type EditedItem = {
  id: number;
  first_name: string;
  last_name: string;
  text: string;
  room_id?: number;
  position: string;
  admin: number;
  login: string;
  password: string;
  selected_rooms_id: Number[];
  salary: number;
};

export type User = {
  id: number;
  first_name: string;
  last_name: string;
  room: string;
  room_id: number;
  position: string;
  admin: number;
  login: string;
  password: string;
  selected_rooms_id: Number[];
  salary: number;
};

export type Room = {
  room_id: number;
  text: string;
};

type Key = {
  user_id: number;
  room_id: number;
};

export default defineComponent({
  layout: 'browser',
  setup(_, setupContext) {
    const headers = ref([
      {
        text: 'First name',
        value: 'first_name'
      },
      {
        text: 'Last name',
        value: 'last_name'
      },
      {
        text: 'Room',
        value: 'room'
      },
      {
        text: 'Telephone',
        value: 'telephone'
      },
      {
        text: 'Position',
        value: 'position'
      },
      {
        text: 'Actions',
        align: 'center',
        sortable: false
      }
    ]);
    const loading = ref(true);
    const editedItem: Ref<EditedItem> = ref({
      id: 0,
      first_name: '',
      last_name: '',
      text: '',
      room_id: 0,
      position: '',
      admin: 0,
      login: '',
      password: '',
      selected_rooms_id: []
    });
    const dialog = ref(false);
    const isAdmin = ref(false);
    const tableLoading = ref(true);
    const users = ref([]);
    const rooms: Ref<Array<Room>> = ref([]);
    const keys = ref([]);
    const selectValue = ref('');
    const selectedUserId = ref(0);
    const deleteDialog = ref(false);

    watchEffect(() => {
      editedItem.value.room_id = rooms.value.find(
        (room: Room) => room.text == editedItem.value.text
      )?.room_id;
    });

    async function fetchUsers(jwt: string) {
      tableLoading.value = true;

      console.log('Fetching users');

      try {
        const response = await axios({
          method: 'get',
          url: process.env.API_URL + '/api/users.php',
          headers: {
            Authorization: `Bearer ${jwt}`
          }
        });

        const data = response.data;

        users.value = data.users;
        rooms.value = data.rooms;
        keys.value = data.keys;

        tableLoading.value = false;
      } catch (e) {}
    }

    async function validateUserData() {
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

      loading.value = false;

      isAdmin.value = setupContext.root.$store.getters.isAdmin;

      if (jwt) fetchUsers(jwt);
    }

    validateUserData();

    function editItemDialog(user: User) {
      editedItem.value = {
        id: user.id,
        first_name: user.first_name,
        last_name: user.last_name,
        text: user.room,
        position: user.position,
        room_id: user.room_id,
        salary: user.salary,
        login: user.login,
        admin: user.admin, //This is int
        password: '',
        selected_rooms_id: keys.value
          .filter((key: Key) => key.user_id == user.id)
          .map((key: Key) => key.room_id)
      };
      dialog.value = true;
    }
    const valid = ref(true);

    async function saveChanges() {
      const data = editedItem.value;
      const changes = {
        id: data.id,
        first_name: data.first_name,
        last_name: data.last_name,
        position: data.position,
        salary: data.salary,
        room_id: data.room_id,
        login: data.login,
        password: data.password,
        admin: data.admin ? 1 : 0,
        selected_rooms_id: data.selected_rooms_id
      };

      // @ts-ignore
      if ((setupContext.refs.form as VForm).validate()) {
        const jwt = localStorage.getItem('jwt');
        try {
          const response = await axios({
            method: 'POST',
            url: process.env.API_URL + '/api/user/update.php',
            headers: {
              Authorization: `Bearer ${jwt}`
            },
            data: {
              data: changes
            }
          });
        } catch (e) {
          console.log('Failed to update user');
        }

        dialog.value = false;

        if (changes.id === setupContext.root.$store.getters.userId)
          validateUserData();

        if (jwt) fetchUsers(jwt);
      } else valid.value = false;
    }

    async function deleteUser() {
      deleteDialog.value = false;
      try {
        const jwt = localStorage.getItem('jwt');
        await axios({
          method: 'POST',
          url: process.env.API_URL + '/api/user/delete.php',
          headers: {
            Authorization: `Bearer ${jwt}`
          },
          data: {
            data: { id: selectedUserId.value }
          }
        });

        if (jwt) fetchUsers(jwt);
      } catch (e) {
        console.log('Something went wrong');
      }
    }

    //RULES
    const nameRules = ref([
      (v: string) => !!v || 'Name is required',
      (v: string) => (v && v.length >= 2) || 'Minimum 2 chars'
    ]);

    return {
      loading,
      headers,
      users,
      editedItem,
      dialog,
      editItemDialog,
      isAdmin,
      tableLoading,
      rooms,
      keys,
      selectValue,
      saveChanges,
      nameRules,
      valid,
      deleteUser,
      selectedUserId,
      deleteDialog
    };
  },
  head: {
    title: 'Users'
  }
});
</script>