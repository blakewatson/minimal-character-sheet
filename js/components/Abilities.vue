<template>
  <section>
    <div
      class="grid grid-cols-[65px_65px_65px] justify-center justify-items-center gap-4 border-b border-neutral-300 py-4 min-[530px]:flex min-[530px]:justify-between"
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
import { mapGetters, mapState } from 'vuex';
import Ability from './Ability';

export default {
  name: 'Abilities',

  computed: {
    ...mapState(['abilities']),
    ...mapGetters(['modifiers']),
  },

  components: {
    ability: Ability,
  },
};
</script>
