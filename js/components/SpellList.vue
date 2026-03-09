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
            <label class="sr-only">{{ $t('Spell name and description') }}</label>
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
import ButtonCollapse from './ButtonCollapse';
import QuillEditor from './QuillEditor';

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
