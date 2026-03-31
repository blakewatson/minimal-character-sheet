<template>
  <section>
    <div
      class="grid grid-cols-[auto_auto_auto] justify-center justify-items-center gap-4 py-4 min-[590px]:flex min-[590px]:justify-between"
    >
      <ability
        v-for="(a, i) in abilities"
        :ability="a"
        :modifier="modifiers[i].val"
        :key="a.name"
      ></ability>
    </div>
  </section>
</template>

<script>
import { state, modifiers as storeModifiers } from '../store';
import Ability from './Ability.vue';

export default {
  name: 'Abilities',

  computed: {
    abilities() { return state.abilities; },
    modifiers() { return storeModifiers.value; },
  },

  components: {
    ability: Ability,
  },
};
</script>
