<template>
  <div>
    <v-overlay color="black" opacity="1" :value="loading">
      <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
    <v-container fluid>
      <v-data-table
        :items="users"
        :headers="headers"
        sort-by="first_name"
        class="elevation-4"
        hide-default-footer
        loading="tableLoading"
      >
        <template v-slot:top>
          <v-toolbar color="secondary">
            <v-toolbar-title>Users</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-btn color="accent" dark @click="openCreateDialog">New User</v-btn>

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
                            :disabled="editedItem.id === userId"
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
                              v => !!v || 'Position is required',
                              v => v.length >= 2 || 'Min 2 char including decimals'
                              ]"
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
                            :rules="[
                              v => !!v || 'Room is required'
                            ]"
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
                            :rules="loginRules"
                            :error-messages="loginError"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6">
                          <v-text-field
                            type="password"
                            color="accent"
                            v-model="editedItem.password"
                            label="Password"
                            :rules="editPasswordRules"
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
                <v-btn v-if="isAdmin" icon small class="mr-2" @click="editItemDialog(item)">
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                  v-if="isAdmin"
                  icon
                  small
                  class="mr-2"
                  @click="() => {
                  selectedUserId = item.id
                  deleteDialog = true;
                  }"
                >
                  <v-icon small>mdi-delete</v-icon>
                </v-btn>
                <v-btn icon small :to="`/user?id=${item.id}`">
                  <v-icon small>mdi-folder-information</v-icon>
                </v-btn>
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
          <v-btn color="accent" text @click="deleteDialog = false">Cancel</v-btn>
          <v-spacer></v-spacer>

          <v-btn
            color="red"
            text
            :disabled="selectedUserId === userId"
            @click="deleteUser"
          >Delete user</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="createDialog" max-width="500px">
      <v-card>
        <v-card-text>
          <v-container>
            <v-form ref="createForm" v-model="createValid">
              <v-row>
                <v-col cols="6">
                  <span class="headline">Create user</span>
                </v-col>
                <v-col cols="6">
                  <v-switch
                    color="accent"
                    v-model="newUser.admin"
                    label="Admin"
                    style="margin-top: 0; margin-left: 45%"
                  ></v-switch>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field
                    color="accent"
                    v-model="newUser.first_name"
                    label="First name"
                    :rules="nameRules"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field
                    color="accent"
                    v-model="newUser.last_name"
                    label="Last name"
                    :rules="nameRules"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field
                    color="accent"
                    required
                    v-model="newUser.position"
                    label="Positon"
                    :rules="[
                      v => !!v || 'Position is required',
                      v => v.length >= 2 || 'Min 2 char including decimals'
                    ]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="4">
                  <v-text-field
                    color="accent"
                    required
                    type="number"
                    v-model="newUser.salary"
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
                    v-model="newUser.text"
                    color="accent"
                    item-color="accent"
                    required
                    :rules="[
                      v => !!v || 'Room is required']"
                  ></v-select>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    color="accent"
                    required
                    v-model="newUser.login"
                    label="Login"
                    :error-messages="loginError"
                    :rules="loginRules"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    type="password"
                    color="accent"
                    v-model="newUser.password"
                    label="Password"
                    :rules="createPasswordRules"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
            <v-row>
              <v-col md="4" v-for="(room, index) in rooms">
                <v-checkbox
                  color="accent"
                  v-model="newUser.selected_rooms_id"
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
          <v-btn color="red" text @click="createDialog = false">Cancel</v-btn>
          <v-btn color="green" text @click="createUser">Save</v-btn>
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
  computed,
  Ref
} from '@vue/composition-api';
import axios from 'axios';
import { VForm } from '../types';

type EditedItem = {
  id?: number;
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

type User = {
  id?: number;
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

type Room = {
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
    const loading = ref(true);
    const isAdmin = ref(false);
    const users = ref([]);
    const rooms: Ref<Array<Room>> = ref([]);
    const keys = ref([]);
    const tableLoading = ref(true);

    const selectValue = ref('');
    const selectedUserId = ref(0);
    const userId = ref(0);

    //RULES
    const nameRules = ref([
      (v: string) => !!v || 'Name is required',
      (v: string) => (v && v.length >= 2) || 'Minimum 2 chars'
    ]);

    const loginRules = ref([
      (v: string) => !!v || 'Login is required',
      (v: string) => (v && v.length >= 4) || 'Minimum 4 chars'
    ]);

    const createPasswordRules = ref([
      (v: string) => (v ?? '').length >= 6 || 'Min 6 chars'
    ]);
    const editPasswordRules = ref([
      (v: string) =>
        (v ?? '').length === 0 ||
        (v ?? '').length >= 6 ||
        'Min 6 chars long or empty'
    ]);

    function validateLogin(login: string, id?: number) {
      login = login.toLowerCase();

      const logins: Array<User> = users.value.filter(
        (user: User) => user.login === login && user.id !== id
      );

      if (logins.length === 1) {
        loginError.value = 'Login already taken';
        return true;
      } else {
        loginError.value = '';
        return false;
      }
    }

    watchEffect(() => {
      editedItem.value.room_id = rooms.value.find(
        (room: Room) => room.text == editedItem.value.text
      )?.room_id;
    });

    watchEffect(() => {
      newUser.value.room_id = rooms.value.find(
        (room: Room) => room.text == newUser.value.text
      )?.room_id;
    });

    watchEffect(() => {
      userId.value = setupContext.root.$store.getters.userId;
    });

    async function fetchUsers(jwt: string) {
      tableLoading.value = true;

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

    const dialog = ref(false);

    function editItemDialog(user: User) {
      loginError.value = '';
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
    const loginError = ref('');

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

      if (validateLogin(changes.login, changes.id)) {
        return;
      }
      // @ts-ignore
      if (setupContext.refs.form.validate()) {
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

          dialog.value = false;

          if (jwt) fetchUsers(jwt);
        } catch (e) {}
      } else valid.value = false;
    }

    const deleteDialog = ref(false);

    async function deleteUser() {
      deleteDialog.value = false;
      try {
        const jwt = localStorage.getItem('jwt');
        const response = await axios({
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
      } catch (e) {}
    }

    const createDialog = ref(false);
    const createValid = ref(true);
    const newUser: Ref<EditedItem> = ref({});

    async function openCreateDialog() {
      loginError.value = '';
      createDialog.value = true;
      newUser.value = {
        first_name: '',
        last_name: '',
        position: '',
        salary: 0,
        room_id: 0,
        login: '',
        password: '',
        admin: 0,
        text: '',
        selected_rooms_id: []
      };
    }

    async function createUser() {
      const data = newUser.value;

      const newUserData = {
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
      if (validateLogin(newUserData.login)) return;
      // @ts-ignore
      if ((setupContext.refs.createForm as VForm).validate()) {
        try {
          const jwt = localStorage.getItem('jwt');
          const response = await axios({
            method: 'POST',
            url: process.env.API_URL + '/api/user/new.php',
            headers: {
              Authorization: `Bearer ${jwt}`
            },
            data: {
              data: newUserData
            }
          });

          createDialog.value = false;

          if (jwt) fetchUsers(jwt);
        } catch (e) {}
      } else {
        createValid.value = false;
      }
    }

    return {
      loading,
      headers,
      editPasswordRules,
      createPasswordRules,
      users,
      userId,
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
      deleteDialog,
      openCreateDialog,
      createDialog,
      createUser,
      createValid,
      newUser,
      loginRules,
      loginError
    };
  },
  head: {
    title: 'Users'
  }
});
</script>