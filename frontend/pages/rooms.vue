<template>
  <div>
    <v-overlay color="black" opacity="1" :value="loading">
      <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
    <v-container fluid>
      <v-data-table
        :headers="headers"
        :items="rooms"
        sort-by="name"
        class="elevation-4"
        hide-default-footer
        loading="tableLoading"
      >
        <template v-slot:top>
          <v-toolbar color="secondary">
            <v-toolbar-title>Rooms</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-btn color="accent" dark @click="openCreateDialog">New Room</v-btn>

            <v-dialog v-model="editDialog" max-width="500px">
              <v-card>
                <v-card-text>
                  <v-container>
                    <v-form ref="editForm" v-model="editValid">
                      <v-row>
                        <v-col cols="12">
                          <span class="headline">Edit user</span>
                        </v-col>
                        <v-col cols="12">
                          <v-text-field
                            color="accent"
                            v-model="editedItem.name"
                            label="Name"
                            :rules="nameRules"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                          <v-text-field
                            color="accent"
                            v-model="editedItem.number"
                            type="number"
                            label="Number"
                            :rules="numRules"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                          <v-text-field
                            color="accent"
                            v-model="editedItem.telephone"
                            type="number"
                            label="Telephone"
                            :rules="telRules"
                            :error-messages="telephoneError"
                            required
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-form>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="red" text @click="editDialog = false">Cancel</v-btn>
                  <v-btn color="green" text @click="saveChanges">Save</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>

            <v-dialog v-model="createDialog" max-width="500px">
              <v-card>
                <v-card-text>
                  <v-container>
                    <v-form ref="createForm" v-model="createValid">
                      <v-row>
                        <v-col cols="12">
                          <span class="headline">Create Room</span>
                        </v-col>
                        <v-col cols="12">
                          <v-text-field
                            color="accent"
                            v-model="newRoom.name"
                            label="Name"
                            :rules="nameRules"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                          <v-text-field
                            color="accent"
                            v-model="newRoom.number"
                            type="number"
                            label="Number"
                            :rules="numRules"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                          <v-text-field
                            color="accent"
                            v-model="newRoom.telephone"
                            type="number"
                            label="Telephone"
                            :rules="telRules"
                            :error-messages="telephoneError"
                            required
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-form>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="red" text @click="createDialog = false">Cancel</v-btn>
                  <v-btn color="green" text @click="createRoom">Create</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-toolbar>
        </template>
        <template v-slot:item="{ item }">
          <tr>
            <td>{{ item.name }}</td>
            <td>{{ item.number }}</td>
            <td>{{ item.telephone }}</td>
            <td>
              <v-content justify="center" align="center">
                <v-btn v-if="isAdmin" icon small class="mr=2" @click="openEditDialog(item)">
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
                <v-btn v-if="isAdmin" icon small class="mr=2">
                  <v-icon small>mdi-delete</v-icon>
                </v-btn>
                <v-btn icon small>
                  <v-icon small>mdi-folder-information</v-icon>
                </v-btn>
              </v-content>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-container>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, Ref, watchEffect } from '@vue/composition-api';
import axios from 'axios';

type Room = {
  id?: number;
  name: string;
  number: number;
  telephone?: string;
};

export default defineComponent({
  layout: 'browser',
  setup(_, setupContext) {
    const loading = ref(true);
    const tableLoading = ref(true);
    const isAdmin = ref(false);

    const headers = ref([
      {
        text: 'Name',
        value: 'name'
      },
      {
        text: 'Number',
        value: 'number'
      },
      {
        text: 'Telephone',
        value: 'telephone'
      },
      {
        text: 'Actions',
        align: 'center',
        sortable: false
      }
    ]);
    const rooms: Ref<Array<Room>> = ref([]);

    async function fetchRooms(jwt: string) {
      tableLoading.value = true;

      console.log('Fetching rooms');

      try {
        const response = await axios({
          method: 'get',
          url: process.env.API_URL + '/api/rooms.php',
          headers: {
            Authorization: `Bearer ${jwt}`
          }
        });

        rooms.value = response.data;

        console.log(rooms.value);

        tableLoading.value = false;
      } catch (e) {}
    }

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

      loading.value = false;

      isAdmin.value = setupContext.root.$store.getters.isAdmin;

      if (jwt) fetchRooms(jwt);
    })();

    //RULES
    const nameRules = ref([
      (v) => !!v || 'Name is required',
      (v) => v.length >= 2 || 'Min 2 chars'
    ]);

    const numRules = ref([
      (v) => !!v || 'Name is required',
      (v) => v.toString().length === 3 || 'Has to be 3 chars long'
    ]);

    const telRules = ref([
      (v) =>
        (v ?? '').length === 0 ||
        (v ?? '').length === 4 ||
        'Has to be 4 chars long or empty'
    ]);

    const telephoneError = ref('');

    function validateTelephone(telephone: number, id?: number) {
      if (telephone === null || telephone.toString() === '') {
        telephoneError.value = '';
        return false;
      }
      const telephones: Array<Room> = rooms.value.filter(
        (room: Room) => Number(room.telephone) === telephone
      );

      if (telephones.length === 1 && telephones[0].id !== id) {
        telephoneError.value = 'Telephone already taken';
        return true;
      } else {
        telephoneError.value = '';
        return false;
      }
    }

    //EDIT
    const editValid = ref(true);
    const editDialog = ref(false);
    const editedItem: Ref<Room> = ref({
      name: '',
      number: 0,
      telephone: ''
    });

    function openEditDialog(item: Room) {
      editedItem.value = {
        id: item.id,
        name: item.name,
        number: item.number,
        telephone: item.telephone
      };

      editDialog.value = true;
    }

    async function saveChanges() {
      if (
        validateTelephone(
          Number(editedItem.value.telephone),
          editedItem.value.id
        )
      )
        return;

      //@ts-ignore
      if (setupContext.refs.editForm.validate()) {
        const jwt = localStorage.getItem('jwt');
        try {
          const response = await axios({
            method: 'POST',
            url: process.env.API_URL + '/api/room/update.php',
            headers: {
              Authorization: `Bearer ${jwt}`
            },
            data: {
              data: editedItem.value
            }
          });

          console.log(response.data);

          if (jwt) fetchRooms(jwt);

          editDialog.value = false;
        } catch (e) {}
      } else {
        return;
      }
    }

    //CREATE
    const createDialog = ref(false);
    const createValid = ref(true);
    const newRoom: Ref<Room> = ref({});

    function openCreateDialog() {
      newRoom.value = {
        name: '',
        number: 0,
        telephone: ''
      };

      createDialog.value = true;
    }

    async function createRoom() {
      if (validateTelephone(Number(editedItem.value.telephone), null)) return;
      //@ts-ignore
      if (setupContext.refs.createForm.validate()) {
        const jwt = localStorage.getItem('jwt');
        try {
          console.log(newRoom.value);

          const response = await axios({
            method: 'POST',
            url: process.env.API_URL + '/api/room/new.php',
            headers: {
              Authorization: `Bearer ${jwt}`
            },
            data: {
              data: newRoom.value
            }
          });

          console.log(response.data);

          if (jwt) fetchRooms(jwt);

          newRoom.value = {
            name: '',
            number: 0,
            telephone: ''
          };

          createDialog.value = false;
        } catch (e) {}
      } else {
        return;
      }
    }

    return {
      loading,
      tableLoading,
      nameRules,
      numRules,
      telRules,
      telephoneError,
      headers,
      rooms,
      isAdmin,
      editValid,
      editedItem,
      editDialog,
      openEditDialog,
      saveChanges,
      createDialog,
      createValid,
      openCreateDialog,
      newRoom,
      createRoom
    };
  },
  head: {
    title: 'Rooms'
  }
});
</script>