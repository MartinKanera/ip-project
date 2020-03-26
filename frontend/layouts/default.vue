<template>
  <v-app dark>
    <div>
      <v-toolbar color="secondary">
        <v-toolbar-title>Database browser</v-toolbar-title>
      </v-toolbar>
    </div>
    <v-content>
      <v-container class="container-auth pa-5 mt-5" fluid>
        <v-row no-gutters justify="center" align="center" class="min-h-screen">
          <v-sheet
            width="512"
            class="container-content rounded-4"
            :class="$vuetify.breakpoint.xsOnly ? 'min-h-screen min-w-screen' : ''"
            :elevation="$vuetify.breakpoint.xsOnly ? 0 : 4"
          >
            <v-container class="pa-0" fluid>
              <nuxt></nuxt>
            </v-container>
          </v-sheet>
        </v-row>
      </v-container>
    </v-content>
  </v-app>
</template>

<style lang="sass">
</style>

<script></script>
