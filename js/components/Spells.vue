<template>
  <section>
    <h1 class="label centered">Spellcasting</h1>

    <div class="row vert-after" style="gap: 0.5rem">
      <div class="box box-lite">
        <span class="label centered">Class</span>
        <field
          class="centered block"
          :value="spClass"
          :read-only="readOnly"
          @update-value="updateSpellInfo('spClass', $event)"
        ></field>
      </div>

      <div class="box box-lite">
        <span class="label centered">Ability</span>
        <select
          v-if="!readOnly"
          @input="updateSpellInfo('spAbility', $event.target.value)"
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

      <div class="box">
        <label for="spell-save-dc" class="label centered reverse"
          >Spell Save DC</label
        >
        <field
          id="spell-save-dc"
          class="centered block padded huge"
          :value="spSave"
          :read-only="readOnly"
          @update-value="updateSpellInfo('spSave', $event)"
        ></field>
      </div>
      <div class="box">
        <label for="spell-attack-bonus" class="label centered reverse"
          >Attack Bonus</label
        >
        <field
          id="spell-attack-bonus"
          class="centered block padded huge"
          :value="spAttack"
          :read-only="readOnly"
          @update-value="updateSpellInfo('spAttack', $event)"
        ></field>
      </div>
    </div>

    <div>
      <div class="box box-lite row row-vert-centered mb-sm">
        <span class="slot-label reverse">0</span>
        <span class="label centered">Cantrips</span>
        <button-collapse
          :collapsed="cantripsCollapsed"
          @click="updateCantripsCollapsed()"
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

  data() {
    return {
      cantripsCollapsed: false,
    };
  },

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
  },

  methods: {
    updateSpellInfo(field, val) {
      this.$store.commit('updateSpellInfo', { field, val });
    },

    updateCantripsCollapsed() {
      this.cantripsCollapsed = !this.cantripsCollapsed;

      this.cantripsList.forEach((item, i) => {
        this.$store.commit('updateListField', {
          field: 'cantripsList',
          i,
          val: item.val,
          collapsed: this.cantripsCollapsed,
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
