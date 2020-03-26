<template>
  <div>
    <v-overlay color="black" opacity="1" :value="loading">
      <v-progress-circular z-index="200" size="40" color="secondary" indeterminate></v-progress-circular>
    </v-overlay>
  </div>
</template>

<style lang="sass">
</style>

<script lang="ts">
import { defineComponent, ref, onMounted } from '@vue/composition-api';

export default defineComponent({
  layout: 'browser',
  setup(_, context) {
    const loading = ref(true);

    onMounted(async () => {
      const jwt = localStorage.getItem('jwt') ?? false;

      if (jwt) {
        try {
          await context.root.$store.dispatch('fetchUserData', jwt);
          loading.value = false;
        } catch (e) {
          context.root.$root.$router.replace('/');

          localStorage.removeItem('jwt');
        }
      } else {
        context.root.$root.$router.replace('/');
      }
    });

    return {
      loading
    };
  }
});
</script>