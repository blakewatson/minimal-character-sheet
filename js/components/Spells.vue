<template>
  <section
    class="border-light-foreground dark:border-dark-foreground border-t pt-4"
  >
    <h1 class="section-label pt-0 font-bold">Spellcasting</h1>

    <div
      class="grid grid-cols-2 items-start justify-between gap-2 sm:flex sm:flex-wrap"
    >
      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <span class="text-center text-sm sm:mb-1">Class</span>
        <field
          :read-only="readOnly"
          :value="spClass"
          @update-value="updateSpellInfo('spClass', $event)"
          class="text-center text-[13px]! min-[500px]:text-sm!"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <span class="text-center text-sm sm:mb-1">Ability</span>
        <select
          v-if="!readOnly"
          @input="updateSpellInfo('spAbility', $event.target.value)"
          class="hover:text-light-accent focus:text-light-accent dark:hover:text-dark-accent dark:focus:text-dark-accent outline-light-accent dark:outline-dark-accent dark:focus:outline-dark-accent rounded-xs border border-transparent px-1 py-0.5 text-center text-sm hover:bg-neutral-100 focus:bg-neutral-100 dark:hover:border-neutral-700 dark:hover:bg-black dark:focus:bg-black dark:focus:outline-2"
        >
          <option
            v-for="(a, idx) in abilities"
            :value="a.name"
            :selected="spAbility === a.name"
          >
            {{ a.name }}: {{ modifiers[idx].val | signedNumString }}
          </option>
        </select>
        <div class="block" style="padding: 0.25em" v-else>{{ spAbility }}</div>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <label for="spell-save-dc" class="text-center text-sm"
          >Spell Save DC</label
        >
        <field
          id="spell-save-dc"
          class="text-center sm:text-2xl"
          :value="spSave"
          :read-only="readOnly"
          @update-value="updateSpellInfo('spSave', $event)"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <label for="spell-attack-bonus" class="text-center text-sm"
          >Attack Bonus</label
        >
        <field
          id="spell-attack-bonus"
          class="text-center sm:text-2xl"
          :value="spAttack"
          :read-only="readOnly"
          @update-value="updateSpellInfo('spAttack', $event)"
        ></field>
      </div>
    </div>

    <div class="my-6">
      <div
        class="border-light-foreground dark:border-dark-foreground relative mb-3 flex items-center justify-center border-t"
      >
        <span
          class="text-reverse bg-reverse absolute top-0 left-0 rounded-b-xs px-2 text-xl"
        >
          <div class="sr-only">Level</div>
          0
        </span>

        <span
          class="mx-auto mt-1 grow text-center text-sm tracking-wider uppercase"
          >Cantrips</span
        >

        <button-collapse
          :collapsed="!shouldCollapseAll"
          @click="updateCantripsCollapsed()"
          class="absolute top-1 right-0"
          collapse-title="Collapse all cantrips"
          expand-title="Expand all cantrips"
          v-if="!readOnly"
        ></button-collapse>
      </div>
      <list list-field="cantripsList" :read-only="readOnly"></list>
    </div>

    <spell-group level="1"></spell-group>
    <spell-group level="2"></spell-group>
    <spell-group level="3"></spell-group>
    <spell-group level="4"></spell-group>
    <spell-group level="5"></spell-group>
    <spell-group level="6"></spell-group>
    <spell-group level="7"></spell-group>
    <spell-group level="8"></spell-group>
    <spell-group level="9"></spell-group>
  </section>
</template>

<script>
import { mapGetters, mapState } from 'vuex';
import ButtonCollapse from './ButtonCollapse';
import Field from './Field';
import List from './List';
import SpellGroup from './SpellGroup';

export default {
  name: 'Spells',

  computed: {
    ...mapState([
      'abilities',
      'className',
      'spClass',
      'spAbility',
      'spSave',
      'spAttack',
      'cantripsList',
      'readOnly',
    ]),
    ...mapGetters(['modifiers']),

    // button should collapse all cantrips if any are expanded
    shouldCollapseAll() {
      return this.cantripsList.some((cantrip) => !cantrip.collapsed);
    },
  },

  methods: {
    updateSpellInfo(field, val) {
      this.$store.commit('updateSpellInfo', { field, val });
    },

    updateCantripsCollapsed() {
      const newState = this.shouldCollapseAll;

      this.cantripsList.forEach((item, i) => {
        this.$store.commit('updateListField', {
          field: 'cantripsList',
          i,
          val: item.val,
          collapsed: newState,
        });
      });
    },
  },

  mounted() {
    if (this.readOnly) {
      return;
    }

    if (this.spClass === '') {
      this.updateSpellInfo('spClass', this.className);
    }
  },

  components: {
    field: Field,
    list: List,
    'spell-group': SpellGroup,
    'button-collapse': ButtonCollapse,
  },
};
</script>
