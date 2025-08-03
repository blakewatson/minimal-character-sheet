<template>
  <div class="spell-list">
    <ul>
      <li
        v-for="(item, i) in spellItems"
        :key="item.id"
        class="list-item deletable"
      >
        <div class="row">
          <input
            :checked="item.prepared"
            :disabled="readOnly"
            :id="`spell-prepared-${item.id}`"
            @change="updateSpellPrepared(i, $event)"
            title="Prepared"
            type="checkbox"
          />
          <label :for="`spell-prepared-${item.id}`" class="sr-only"
            >Prepared</label
          >
          <div class="size-full">
            <label class="sr-only">Spell name and description</label>
            <quill-editor
              :collapsed="item.collapsed"
              :initial-contents="item.name"
              :read-only="readOnly"
              @quill-text-change="updateSpellName(i, $event)"
            ></quill-editor>
          </div>
        </div>

        <div
          class="mt-sm"
          style="display: flex; justify-content: flex-end; gap: 0.25rem"
        >
          <button-collapse
            :collapsed="item.collapsed"
            @click="updateSpellCollapsed(i, !item.collapsed)"
            v-if="!readOnly"
          ></button-collapse>

          <button
            :disabled="readOnly"
            @click="sortSpells(item.id, 'up')"
            class="button button-sort"
            title="Move up"
            type="button"
            v-if="!readOnly && i > 0"
          >
            <span class="sr-only">Move up</span>
            <i class="fa-sharp fa-regular fa-arrow-up" role="presentation"></i>
          </button>

          <button
            :disabled="readOnly"
            @click="sortSpells(item.id, 'down')"
            class="button button-sort"
            title="Move down"
            type="button"
            v-if="!readOnly && i < spellItems.length - 1"
          >
            <span class="sr-only">Move down</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>

          <button
            :disabled="readOnly"
            @click="deleteSpell(i)"
            class="button button-delete"
            title="Delete spell"
            type="button"
            v-if="!readOnly"
          >
            <span class="sr-only">Delete</span>
            <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
          </button>
        </div>
      </li>
    </ul>

    <p class="text-center" v-if="!readOnly">
      <button
        :disabled="readOnly"
        @click="addSpell"
        class="button-add"
        title="Add spell"
        type="button"
      >
        <span class="sr-only">Add list item</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>
  </div>
</template>

<script>
import QuillEditor from './QuillEditor';
import ButtonCollapse from './ButtonCollapse';

export default {
  name: 'SpellList',

  props: ['listField', 'readOnly'],

  computed: {
    spellItems() {
      return this.$store.state[this.listField].spells.map((spell) => {
        spell.id = Math.random().toString();
        return spell;
      });
    },
  },

  methods: {
    updateSpellName(i, name) {
      this.$store.commit('updateSpellName', {
        field: this.listField,
        i: i,
        name: name,
      });
    },

    updateSpellPrepared(i, e) {
      this.$store.commit('updateSpellPrepared', {
        field: this.listField,
        i: i,
        prepared: e.target.checked,
      });

      window.sheetEvent.$emit('autosave', 1);
    },

    updateSpellCollapsed(i, collapsed) {
      this.$store.commit('updateSpellCollapsed', {
        field: this.listField,
        i: i,
        collapsed: collapsed,
      });
    },

    addSpell() {
      this.$store.commit('addSpell', {
        field: this.listField,
        item: { prepared: false, name: '', url: '', id: Date.now() },
      });
    },

    deleteSpell(i) {
      this.$store.commit('deleteSpell', {
        field: this.listField,
        i: i,
      });

      window.sheetEvent.$emit('autosave', 1);
    },

    sortSpells(id, direction) {
      this.$store.commit('sortSpells', {
        field: this.listField,
        id,
        direction,
      });
    },
  },

  components: {
    'quill-editor': QuillEditor,
    'button-collapse': ButtonCollapse,
  },
};
</script>
