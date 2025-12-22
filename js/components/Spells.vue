<template>
  <section>
    <h1 class="section-label pt-0 font-bold">Spellcasting</h1>

    <div
      class="grid grid-cols-2 items-start justify-between gap-2 sm:flex sm:flex-wrap"
    >
      <div class="flex flex-col items-center border-t border-neutral-950">
        <span class="text-center text-sm sm:mb-1">Class</span>
        <field
          :read-only="readOnly"
          :value="spClass"
          @update-value="updateSpellInfo('spClass', $event)"
          class="text-center text-[13px]! min-[500px]:text-sm!"
        ></field>
      </div>

      <div class="flex flex-col items-center border-t border-neutral-950">
        <span class="text-center text-sm sm:mb-1">Ability</span>
        <select
          v-if="!readOnly"
          @input="updateSpellInfo('spAbility', $event.target.value)"
          class="px-1 py-0.5 text-center hover:bg-neutral-100 hover:text-blue-700 focus:bg-neutral-100 focus:text-blue-700"
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

      <div class="flex flex-col items-center border-t border-neutral-950">
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

      <div class="flex flex-col items-center border-t border-neutral-950">
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
        class="mb-2 flex items-center justify-between border-t border-neutral-950"
      >
        <span class="bg-neutral-950 px-2 text-xl text-neutral-50">0</span>
        <span class="text-center text-sm tracking-wider uppercase"
          >Cantrips</span
        >
        <button-collapse
          :collapsed="!shouldCollapseAll"
          @click="updateCantripsCollapsed()"
          class="mt-1"
          collapse-title="Collapse all cantrips"
          expand-title="Expand all cantrips"
          v-if="!readOnly"
        ></button-collapse>

        <!-- else empty element to maintain layout -->
        <span v-else></span>
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
