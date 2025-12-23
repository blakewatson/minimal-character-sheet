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
          @quill-text-change="updateItem(i, $event)"
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
            @click="sortItems(item.id, 'up')"
            class="button-icon"
            title="Move up"
            type="button"
            v-if="!readOnly && i > 0"
          >
            <span class="sr-only">Move up</span>
            <i class="fa-sharp fa-regular fa-arrow-up" role="presentation"></i>
          </button>

          <button
            :disabled="readOnly"
            @click="sortItems(item.id, 'down')"
            class="button-icon"
            title="Move down"
            type="button"
            v-if="!readOnly && i < items.length - 1"
          >
            <span class="sr-only">Move down</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>

          <button
            :disabled="readOnly"
            @click="deleteItem(i)"
            class="button-icon hover:border-red-600 hover:text-red-600"
            title="Delete"
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
        @click="addToList"
        class="button-icon"
        title="Add list item"
        type="button"
      >
        <span class="sr-only">Add list item</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>
  </div>
</template>

<script>
import ButtonCollapse from './ButtonCollapse';
import QuillEditor from './QuillEditor';

export default {
  name: 'List',

  props: ['listField', 'readOnly'],

  computed: {
    items() {
      return this.$store.state[this.listField];
    },
  },

  methods: {
    updateItem(i, val, collapsed) {
      this.$store.commit('updateListField', {
        field: this.listField,
        i: i,
        val: val,
        collapsed: collapsed,
      });
    },

    addToList() {
      this.$store.commit('addToListField', {
        field: this.listField,
        val: '',
      });
    },

    deleteItem(i) {
      this.$store.commit('deleteFromListField', {
        field: this.listField,
        i: i,
      });

      window.sheetEvent.$emit('autosave', 1);
    },

    sortItems(id, direction) {
      this.$store.commit('sortListField', {
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
