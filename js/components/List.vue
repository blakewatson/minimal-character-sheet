<template>
  <div class="list-field">
    <ul>
      <li
        v-for="(item, i) in items"
        :key="item.id"
        :k="item.id"
        class="mb-2 rounded border border-neutral-400 p-1 dark:border-neutral-600"
      >
        <quill-editor
          :collapsed="readOnly ? false : item.collapsed"
          :initial-contents="item.val"
          :read-only="readOnly"
          @update-collapsed="updateItem(i, item.val, $event)"
          @quill-text-change="updateItem(i, $event, item.collapsed)"
        ></quill-editor>

        <div
          class="mt-1"
          style="display: flex; justify-content: flex-end; gap: 0.25rem"
        >
          <button-collapse
            :collapsed="item.collapsed"
            @click="updateItem(i, item.val, !item.collapsed)"
            v-if="!readOnly"
          ></button-collapse>

          <button
            :disabled="readOnly"
            :title="$t('Move up')"
            @click="sortItems(item.id, 'up')"
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
            @click="sortItems(item.id, 'down')"
            class="button-icon"
            type="button"
            v-if="!readOnly && i < items.length - 1"
          >
            <span class="sr-only">{{ $t('Move down') }}</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>

          <button
            :disabled="readOnly"
            :title="$t('Delete')"
            @click="deleteItem(i)"
            class="button-icon hover:border-red-600 hover:text-red-600"
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
        :title="$t('Add list item')"
        @click="addToList"
        class="button-icon"
        type="button"
      >
        <span class="sr-only">{{ $t('Add list item') }}</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>
  </div>
</template>

<script>
import { state } from '../store';
import ButtonCollapse from './ButtonCollapse.vue';
import QuillEditor from './QuillEditor.vue';

/**
 * @typedef {(
 *   'cantripsList'
 * )} ListField
 */

export default {
  name: 'List',

  props: {
    listField: /** @type import('vue').PropType<ListField> */ (String),
    readOnly: Boolean,
  },

  computed: {
    items() {
      if (!this.listField) {
        return [];
      }
      return state[this.listField];
    },
  },

  methods: {
    /**
     * @param {number} i
     * @param {object | null} val
     * @param {boolean} collapsed
     */
    updateItem(i, val, collapsed) {
      if (!this.listField) {
        return;
      }

      state[this.listField][i].val = val;
      state[this.listField][i].collapsed = collapsed;
    },

    addToList() {
      if (!this.listField) {
        return;
      }

      state[this.listField].push({
        id: crypto.randomUUID(),
        val: null,
        collapsed: false,
      });
    },

    /** @param {number} i */
    deleteItem(i) {
      if (!this.listField) {
        return;
      }

      state[this.listField].splice(i, 1);
    },

    /**
     * @param {string} id
     * @param {'up' | 'down'} direction
     */
    sortItems(id, direction) {
      if (!this.listField) {
        return;
      }

      var field = this.listField;
      var direction = direction;
      var curIndex = state[field].findIndex((item) => item.id === id);

      if (curIndex === -1) {
        return;
      }

      if (direction === 'up') {
        if (curIndex === 0) {
          return;
        }
        var deletedItems = state[field].splice(curIndex, 1);
        var itemToMove = deletedItems[0];
        state[field].splice(curIndex - 1, 0, itemToMove);
        return;
      }

      if (direction === 'down') {
        if (curIndex === state[field].length - 1) {
          return;
        }
        var deletedItems = state[field].splice(curIndex, 1);
        var itemToMove = deletedItems[0];
        state[field].splice(curIndex + 1, 0, itemToMove);
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
