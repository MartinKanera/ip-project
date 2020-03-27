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
        loading="tableLoading"
      >
        <template v-slot:top>
          <v-toolbar flat color="secondary">
            <v-toolbar-title>Users</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-btn color="accent" dark>New User</v-btn>

            <v-dialog v-model="dialog" max-width="500px">
              <v-card>
                <v-card-title>
                  <span class="headline">Edit user</span>
                </v-card-title>

                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          color="accent"
                          v-model="editedItem.first_name"
                          label="First name"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          color="accent"
                          v-model="editedItem.last_name"
                          label="Last name"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field color="accent" v-model="editedItem.room" label="Room"></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          color="accent"
                          v-model="editedItem.telephone"
                          label="Telephone"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field color="accent" v-model="editedItem.position" label="Positon"></v-text-field>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="red" text @click="dialog = false">Cancel</v-btn>
                  <v-btn color="green" text>Save</v-btn>
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
                <v-icon v-if="isAdmin" small class="mr-2">mdi-delete</v-icon>
                <nuxt-link :to="'/user?id=' + item.id">
                  <v-icon small>mdi-folder-information</v-icon>
                </nuxt-link>
              </v-content>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-container>
  </div>
  <!--
    
  -->
</template>

<style lang="sass">
</style>

<script lang="ts">
import {
  defineComponent,
  ref,
  onMounted,
  onBeforeMount,
  watchEffect
} from '@vue/composition-api';
import axios from 'axios';

export default defineComponent({
  layout: 'browser',
  setup(_, setupContext) {
    const loading = ref(true);
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
    const users = ref([]);
    const editedItem = ref({
      firstName: '',
      lastName: '',
      room: 0,
      telephone: 0,
      position: ''
    });
    const dialog = ref(false);
    const isAdmin = ref(false);
    const tableLoading = ref(true);

    (async () => {
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

      try {
        const response = await axios.get(
          process.env.API_URL + '/api/users.php'
        );
        users.value = response.data;
      } catch (e) {}

      tableLoading.value = false;
    })();

    function editItemDialog(item) {
      editedItem.value = item;
      dialog.value = true;
    }

    return {
      loading,
      headers,
      users,
      editedItem,
      dialog,
      editItemDialog,
      isAdmin,
      tableLoading
    };
  }
});
</script>