import { GetterTree, ActionTree, MutationTree } from 'vuex';
import axios from 'axios';

export const state = () => ({
  id: 0,
  firstName: '',
  lastName: '',
  isAdmin: false
});

export type RootState = ReturnType<typeof state>;

export const mutations: MutationTree<RootState> = {
  HYDRATE: (
    state,
    {
      id,
      firstName,
      lastName,
      isAdmin
    }: { id: number; firstName: string; lastName: string; isAdmin: number }
  ) => {
    state.id = id;
    state.firstName = firstName;
    state.lastName = lastName;
    state.isAdmin = isAdmin === 1;
  }
};

export const actions: ActionTree<RootState, RootState> = {
  async fetchUserData({ commit }, jwt) {
    const response = await axios({
      method: 'post',
      url: process.env.API_URL + '/api/user/data.php',
      headers: {
        Authorization: `Bearer ${jwt}`
      }
    });

    const { user_id, first_name, last_name, admin } = response.data;

    commit('HYDRATE', {
      id: user_id,
      firstName: first_name,
      lastName: last_name,
      isAdmin: admin
    });
  }
};

export const getters = () => {};
