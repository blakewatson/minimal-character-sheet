<template>
  <div class="spell-list mb-6">
    <ul class="mb-4">
      <li
        v-for="(item, i) in spellItems"
        :key="item.id"
        class="mb-2 rounded border border-neutral-400 p-1 dark:border-neutral-600"
      >
        <div class="flex items-start gap-2">
          <input
            :checked="item.prepared"
            :disabled="readOnly"
            :id="`spell-prepared-${item.id}`"
            :title="$t('Prepared')"
            @change="updateSpellPrepared(i, $event)"
            type="checkbox"
          />
          <label :for="`spell-prepared-${item.id}`" class="sr-only">
            {{ $t('Prepared') }}
          </label>

          <div class="size-full">
            <label class="sr-only">{{
              $t('Spell name and description')
            }}</label>
            <quill-editor
              :collapsed="readOnly ? false : item.collapsed"
              :initial-contents="item.name"
              :read-only="readOnly"
              @update-collapsed="updateSpellCollapsed(i, $event)"
              @quill-text-change="updateSpellName(i, $event)"
            ></quill-editor>
          </div>
        </div>

        <div class="mt-1 flex items-center justify-end gap-1">
          <button-collapse
            :collapsed="item.collapsed"
            @click="updateSpellCollapsed(i, !item.collapsed)"
            v-if="!readOnly"
          ></button-collapse>

          <button
            :disabled="readOnly"
            :title="$t('Move up')"
            @click="sortSpells(item.id, 'up')"
            class="button-icon"
            type="button"
            v-if="!readOnly && i > 0"
          >
            <span class="sr-only">{{ $t('Move up') }}</span>
            <i class="fa-sharp fa-regular fa-arrow-up" role="presentation"></i>
          </button>

          <button
            :disabled="readOnly"
            :title="$t('Move down')"
            @click="sortSpells(item.id, 'down')"
            class="button-icon"
            type="button"
            v-if="!readOnly && i < spellItems.length - 1"
          >
            <span class="sr-only">{{ $t('Move down') }}</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>

          <button
            :disabled="readOnly"
            :title="$t('Delete spell')"
            @click="deleteSpell(i)"
            class="button-icon hover:border-light-danger hover:text-light-danger dark:hover:border-dark-danger dark:hover:text-dark-danger"
            type="button"
            v-if="!readOnly"
          >
            <span class="sr-only">{{ $t('Delete') }}</span>
            <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
          </button>
        </div>
      </li>
    </ul>

    <p class="text-center" v-if="!readOnly">
      <button
        :disabled="readOnly"
        :title="$t('Add spell')"
        @click="addSpell"
        class="button-icon"
        type="button"
      >
        <span class="sr-only">{{ $t('Add spell') }}</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>
  </div>
</template>

<script>
import { state } from '../store';
import ButtonCollapse from './ButtonCollapse.vue';
import QuillEditor from './QuillEditor.vue';

/** @typedef {import('../store').SpellGroupKey} SpellGroupKey */

export default {
  name: 'SpellList',

  props: {
    spellGroup: /** @type {import('vue').PropType<SpellGroupKey>} */ (String),
    readOnly: Boolean,
  },

  computed: {
    spellItems() {
      if (!this.spellGroup) {
        return [];
      }

      return state[this.spellGroup].spells;
    },
  },

  methods: {
    /**
     * @param {number} i
     * @param {object | null} name
     */
    updateSpellName(i, name) {
      if (!this.spellGroup) {
        return;
      }

      state[this.spellGroup].spells = state[this.spellGroup].spells.map(
        (spell, idx) => {
          if (i === idx) {
            return {
              ...spell,
              name,
            };
          }
          return spell;
        },
      );
    },

    /**
     * @param {number} i
     * @param {Event} e
     */
    updateSpellPrepared(i, e) {
      if (!this.spellGroup) {
        return;
      }

      const prepared = /** @type {HTMLInputElement} */ (e.target).checked;

      state[this.spellGroup].spells = state[this.spellGroup].spells.map(
        (spell, idx) => {
          if (i === idx) {
            return {
              ...spell,
              prepared,
            };
          }
          return spell;
        },
      );
    },

    /**
     * @param {number} i
     * @param {boolean} collapsed
     */
    updateSpellCollapsed(i, collapsed) {
      if (!this.spellGroup) {
        return;
      }

      state[this.spellGroup].spells = state[this.spellGroup].spells.map(
        (spell, idx) => {
          if (i === idx) {
            return {
              ...spell,
              collapsed,
            };
          }
          return spell;
        },
      );
    },

    addSpell() {
      if (!this.spellGroup) {
        return;
      }

      state[this.spellGroup].spells.push({
        id: crypto.randomUUID(),
        name: null,
        prepared: false,
        collapsed: false,
      });
    },

    /** @param {number} i */
    deleteSpell(i) {
      if (!this.spellGroup) {
        return;
      }

      state[this.spellGroup].spells.splice(i, 1);
    },

    /**
     * @param {string} id
     * @param {'up' | 'down'} direction
     */
    sortSpells(id, direction) {
      if (!this.spellGroup) {
        return;
      }

      var field = this.spellGroup;
      var direction = direction;
      var curIndex = state[field].spells.findIndex((spell) => spell.id === id);

      if (curIndex === -1) {
        return;
      }

      if (direction === 'up') {
        if (curIndex === 0) {
          return;
        }
        var deletedSpells = state[field].spells.splice(curIndex, 1);
        var spellToMove = deletedSpells[0];
        state[field].spells.splice(curIndex - 1, 0, spellToMove);
        return;
      }

      if (direction === 'down') {
        if (curIndex === state[field].spells.length - 1) {
          return;
        }
        var deletedSpells = state[field].spells.splice(curIndex, 1);
        var spellToMove = deletedSpells[0];
        state[field].spells.splice(curIndex + 1, 0, spellToMove);
        return;
      }
    },
  },

  components: {
    'quill-editor': QuillEditor,
    'button-collapse': ButtonCollapse,
  },
};
</script>
