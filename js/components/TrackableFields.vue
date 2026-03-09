<template>
  <details
    open
    class="border-light-foreground dark:border-dark-foreground relative border-t pb-8"
  >
    <summary class="section-label">{{ $t('Trackable Fields') }}</summary>

    <!-- Info Button -->
    <button
      @click="showInfoDialog = true"
      :title="$t('What are trackable fields?')"
      class="hover:border-light-foreground dark:hover:border-dark-foreground absolute top-2 right-0 flex cursor-pointer items-center gap-2 rounded-sm border border-transparent p-1"
      type="button"
    >
      <i class="fa-sharp fa-regular fa-circle-question" role="presentation"></i>
      <span class="sr-only">{{ $t('What are trackable fields?') }}</span>
    </button>

    <!-- Desktop Table Layout -->
    <table
      v-if="trackableFields.length > 0 && !isMobile"
      class="mb-2 w-full text-sm"
    >
      <thead>
        <tr>
          <th class="px-2 text-left">{{ $t('Name') }}</th>
          <th class="px-2 text-center">{{ $t('Used') }}</th>
          <th class="px-2 text-center">{{ $t('Max') }}</th>
          <th class="px-2 text-right">{{ $t('Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        <!-- Every other row is a note. -->
        <tr
          v-for="(field, i) in trackableFieldsAndNotes"
          :key="field.id"
          :class="{
            'border-t border-neutral-400 dark:border-neutral-500':
              field.isField,
          }"
          :style="{ 'z-index': trackableFields.length - i }"
        >
          <td v-if="field.isField" class="p-2">
            <field
              :auto-size="false"
              :placeholder="$t('Name')"
              :read-only="readOnly"
              :value="field.name"
              @update-value="updateTrackableField(field.id, 'name', $event)"
              class="w-full text-left text-[13px]! font-bold"
            ></field>
          </td>

          <td
            v-if="field.isField"
            class="w-px p-2 text-center whitespace-nowrap"
          >
            <field
              :read-only="readOnly"
              :value="field.used"
              @update-value="updateTrackableField(field.id, 'used', $event)"
              type="number"
            ></field>
          </td>

          <td
            v-if="field.isField"
            class="w-px p-2 text-center whitespace-nowrap"
          >
            <field
              :read-only="readOnly"
              :value="field.max"
              @update-value="updateTrackableField(field.id, 'max', $event)"
              type="number"
            ></field>
          </td>

          <td
            v-if="field.isField"
            class="w-px p-2 whitespace-nowrap"
            style="gap: 0.2em; justify-content: flex-end"
          >
            <div class="flex items-center justify-end gap-1">
              <button
                :disabled="readOnly"
                :title="$t('Move up')"
                @click="sortTrackableField(field.id, 'up')"
                class="button-icon"
                type="button"
                v-if="!readOnly && i > 0"
              >
                <span class="sr-only">{{ $t('Move up') }}</span>
                <i
                  class="fa-sharp fa-regular fa-arrow-up"
                  role="presentation"
                ></i>
              </button>

              <button
                :disabled="readOnly"
                :title="$t('Move down')"
                @click="sortTrackableField(field.id, 'down')"
                class="button-icon"
                type="button"
                v-if="!readOnly && i < trackableFieldsAndNotes.length - 2"
              >
                <span class="sr-only">{{ $t('Move down') }}</span>
                <i
                  class="fa-sharp fa-regular fa-arrow-down"
                  role="presentation"
                ></i>
              </button>

              <button
                :disabled="readOnly"
                :title="$t('Delete field')"
                @click="deleteTrackableField(field.id)"
                class="button-icon hover:border-red-600 hover:text-red-600"
                type="button"
                v-if="!readOnly"
              >
                <span class="sr-only">{{ $t('Delete field') }}</span>
                <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
              </button>
            </div>
          </td>

          <td v-if="field.isNote" colspan="4">
            <div class="flex items-center gap-2 pb-2">
              <span class="small-label">{{ $t('Notes') }}</span>
              <quill-editor
                :initial-contents="field.notes"
                :read-only="readOnly"
                @quill-text-change="
                  updateTrackableField(field.id, 'notes', $event)
                "
                style="width: 100%"
              ></quill-editor>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Mobile Card Layout -->
    <div v-if="trackableFields.length > 0 && isMobile" class="">
      <div
        v-for="(field, i) in trackableFields"
        :key="field.id"
        class="mb-4 rounded border border-neutral-400 p-2"
      >
        <div class="flex items-center justify-between gap-2">
          <field
            :auto-size="false"
            :class="isMobile ? '' : 'text-sm!'"
            :placeholder="$t('Name')"
            :read-only="readOnly"
            :value="field.name"
            @update-value="updateTrackableField(field.id, 'name', $event)"
            class="w-full grow font-bold"
          ></field>

          <button
            :disabled="readOnly"
            :title="$t('Move up')"
            @click="sortTrackableField(field.id, 'up')"
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
            @click="sortTrackableField(field.id, 'down')"
            class="button-icon"
            type="button"
            v-if="!readOnly && i < trackableFields.length - 1"
          >
            <span class="sr-only">{{ $t('Move down') }}</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>
          <button
            :disabled="readOnly"
            :title="$t('Delete field')"
            @click="deleteTrackableField(field.id)"
            class="button-icon hover:border-light-danger hover:text-light-danger dark:hover:border-dark-danger dark:hover:text-dark-danger"
            type="button"
            v-if="!readOnly"
          >
            <span class="sr-only">{{ $t('Delete field') }}</span>
            <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
          </button>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex items-baseline gap-1">
            <label
              :for="`trackable-field-used-${field.id}`"
              class="small-label"
            >
              {{ $t('Used') }}
            </label>
            <field
              :class="isMobile ? '' : 'text-sm!'"
              :id="`trackable-field-used-${field.id}`"
              :read-only="readOnly"
              :value="field.used"
              @update-value="updateTrackableField(field.id, 'used', $event)"
              type="number"
            ></field>
          </div>

          <div class="flex items-baseline gap-1">
            <label
              :for="`trackable-field-max-${field.id}`"
              class="small-label"
            >
              {{ $t('Max') }}
            </label>
            <field
              :class="isMobile ? '' : 'text-sm!'"
              :id="`trackable-field-max-${field.id}`"
              :read-only="readOnly"
              :value="field.max"
              @update-value="updateTrackableField(field.id, 'max', $event)"
              type="number"
            ></field>
          </div>
        </div>

        <div>
          <label class="small-label">{{ $t('Notes') }}</label>
          <quill-editor
            :initial-contents="field.notes"
            :read-only="readOnly"
            @quill-text-change="updateTrackableField(field.id, 'notes', $event)"
          ></quill-editor>
        </div>
      </div>
    </div>

    <p class="text-center" v-if="!readOnly">
      <button
        :disabled="readOnly"
        :title="$t('Add a trackable field')"
        @click="$store.commit('addTrackableField')"
        class="button-icon"
        type="button"
      >
        <span class="sr-only">{{ $t('Add a trackable field') }}</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>

    <!-- Info Dialog -->
    <app-dialog
      :title="$t('What are Trackable Fields?')"
      @close="showInfoDialog = false"
      v-if="showInfoDialog"
    >
      <template #content>
        <p>
          {{ $t('Track limited-use resources like Superiority Dice, Focus Points, attunement slots, and rechargeable class features.') }}
        </p>
      </template>
    </app-dialog>
  </details>
</template>

<script>
import { mapState } from 'vuex';
import AppDialog from './AppDialog.vue';
import Field from './Field';
import QuillEditor from './QuillEditor';

export default {
  name: 'TrackableFields',

  data() {
    return {
      isMobile: false,
      mediaQuery: null,
      showInfoDialog: false,
    };
  },

  computed: {
    ...mapState(['trackableFields', 'readOnly']),

    trackableFieldsAndNotes() {
      const rows = [];

      this.trackableFields.forEach((field, index) => {
        rows.push({
          ...field,
          isField: true,
        });
        rows.push({
          id: field.id + '-note',
          isNote: true,
          fieldId: field.id,
          notes: field.notes,
        });
      });

      return rows;
    },
  },

  mounted() {
    this.setupMediaQuery();
  },

  beforeDestroy() {
    if (this.mediaQuery) {
      this.mediaQuery.removeListener(this.handleMediaQueryChange);
    }
  },

  methods: {
    setupMediaQuery() {
      this.mediaQuery = window.matchMedia('(max-width: 675px)');
      this.isMobile = this.mediaQuery.matches;
      this.mediaQuery.addListener(this.handleMediaQueryChange);
    },

    handleMediaQueryChange(event) {
      this.isMobile = event.matches;
    },

    updateTrackableField(id, field, val) {
      if (id.toString().endsWith('-note')) {
        id = parseInt(id.slice(0, -5)); // Remove '-note' suffix for field ID
      }
      this.$store.commit('updateTrackableField', { id, field, val });
    },

    deleteTrackableField(id) {
      this.$store.commit('deleteTrackableField', { id });
    },

    sortTrackableField(id, direction) {
      this.$store.commit('sortTrackableField', { id, direction });
    },
  },

  components: {
    'app-dialog': AppDialog,
    field: Field,
    'quill-editor': QuillEditor,
  },
};
</script>
