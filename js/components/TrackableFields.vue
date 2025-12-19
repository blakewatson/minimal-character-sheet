<template>
  <details open class="section trackable-fields-section">
    <summary class="label centered">Trackable Fields</summary>

    <!-- Info Button -->
    <button
      @click="openInfoDialog"
      class="button-discoverable trackable-fields-info-button"
      title="What are trackable fields?"
      type="button"
    >
      <i class="fa-sharp fa-regular fa-circle-question" role="presentation"></i>
      <span class="sr-only">What are trackable fields?</span>
    </button>

    <!-- Desktop Table Layout -->
    <table
      v-if="trackableFields.length > 0 && !isMobile"
      class="trackable-fields-table"
    >
      <thead>
        <tr>
          <th class="text-left">Name</th>
          <th class="text-center">Used</th>
          <th class="text-center">Max</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Every other row is a note. -->
        <tr
          v-for="(field, i) in trackableFieldsAndNotes"
          :key="field.id"
          :class="{ 'field-row': field.isField, 'note-row': field.isNote }"
          :style="{ 'z-index': trackableFields.length - i }"
          class="deletable"
        >
          <td v-if="field.isField">
            <field
              :read-only="readOnly"
              :value="field.name"
              @update-value="updateTrackableField(field.id, 'name', $event)"
              class="size-full small text-left strong"
              placeholder="Name"
            ></field>
          </td>
          <td v-if="field.isField" class="text-center">
            <field
              :read-only="readOnly"
              :value="field.used"
              @update-value="updateTrackableField(field.id, 'used', $event)"
              type="number"
            ></field>
          </td>
          <td v-if="field.isField" class="text-center">
            <field
              :read-only="readOnly"
              :value="field.max"
              @update-value="updateTrackableField(field.id, 'max', $event)"
              type="number"
            ></field>
          </td>
          <td
            v-if="field.isField"
            class="vert-center"
            style="gap: 0.2em; justify-content: flex-end"
          >
            <button
              :disabled="readOnly"
              @click="sortTrackableField(field.id, 'up')"
              class="button button-sort"
              title="Move up"
              type="button"
              v-if="!readOnly && i > 0"
            >
              <span class="sr-only">Move up</span>
              <i
                class="fa-sharp fa-regular fa-arrow-up"
                role="presentation"
              ></i>
            </button>

            <button
              :disabled="readOnly"
              @click="sortTrackableField(field.id, 'down')"
              class="button button-sort"
              title="Move down"
              type="button"
              v-if="!readOnly && i < trackableFieldsAndNotes.length - 2"
            >
              <span class="sr-only">Move down</span>
              <i
                class="fa-sharp fa-regular fa-arrow-down"
                role="presentation"
              ></i>
            </button>

            <button
              :disabled="readOnly"
              @click="deleteTrackableField(field.id)"
              class="button button-delete"
              title="Delete field"
              type="button"
              v-if="!readOnly"
            >
              <span class="sr-only">Delete field</span>
              <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
            </button>
          </td>

          <td v-if="field.isNote" colspan="4">
            <div class="vert-center pl-lg" style="gap: 0.5em">
              <span class="meta small muted">Notes</span>
              <quill-editor
                :initial-contents="field.notes"
                :read-only="readOnly"
                @quill-text-change="
                  updateTrackableField(field.id, 'notes', $event)
                "
                class="trackable-field-notes"
                style="width: 100%"
              ></quill-editor>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Mobile Card Layout -->
    <div
      v-if="trackableFields.length > 0 && isMobile"
      class="trackable-fields-mobile"
    >
      <div
        v-for="(field, i) in trackableFields"
        :key="field.id"
        class="trackable-field-card"
      >
        <div class="trackable-field-header">
          <field
            class="trackable-field-name"
            align="left"
            :value="field.name"
            :read-only="readOnly"
            @update-value="updateTrackableField(field.id, 'name', $event)"
            placeholder="Name"
          ></field>

          <button
            :disabled="readOnly"
            @click="sortTrackableField(field.id, 'up')"
            class="button button-sort"
            type="button"
            v-if="!readOnly && i > 0"
          >
            <span class="sr-only">Move up</span>
            <i class="fa-sharp fa-regular fa-arrow-up" role="presentation"></i>
          </button>

          <button
            :disabled="readOnly"
            @click="sortTrackableField(field.id, 'down')"
            class="button button-sort"
            type="button"
            v-if="!readOnly && i < trackableFields.length - 1"
          >
            <span class="sr-only">Move down</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>
          <button
            v-if="!readOnly"
            type="button"
            class="button button-delete"
            :disabled="readOnly"
            @click="deleteTrackableField(field.id)"
          >
            <span class="sr-only">Delete field</span>
            <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
          </button>
        </div>

        <div class="trackable-field-stats">
          <div class="stat-group">
            <label
              class="meta small muted"
              :for="`trackable-field-used-${field.id}`"
            >
              Used
            </label>
            <field
              :id="`trackable-field-used-${field.id}`"
              class="stat-value"
              :value="field.used"
              :read-only="readOnly"
              @update-value="updateTrackableField(field.id, 'used', $event)"
              type="number"
            ></field>
          </div>
          <div class="stat-group">
            <label
              class="meta small muted"
              :for="`trackable-field-max-${field.id}`"
            >
              Max
            </label>
            <field
              :id="`trackable-field-max-${field.id}`"
              class="stat-value"
              :value="field.max"
              :read-only="readOnly"
              @update-value="updateTrackableField(field.id, 'max', $event)"
              type="number"
            ></field>
          </div>
        </div>
        <div class="trackable-field-notes">
          <label class="meta small muted">Notes</label>
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
        @click="$store.commit('addTrackableField')"
        class="button button-add"
        title="Add a trackable field"
        type="button"
      >
        <span class="sr-only">Add a trackable field</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>

    <!-- Info Dialog -->
    <dialog class="skill-override-dialog" ref="infoDialog">
      <div>
        <p><strong>What are Trackable Fields?</strong></p>
        <p>
          Track limited-use resources like Superiority Dice, Focus Points,
          attunement slots, and rechargeable class features.
        </p>
        <div class="mt-md">
          <button
            @click="closeInfoDialog"
            class="button button-primary"
            type="button"
          >
            Close
          </button>
        </div>
      </div>
    </dialog>
  </details>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';
import QuillEditor from './QuillEditor';

export default {
  name: 'TrackableFields',

  data() {
    return {
      isMobile: false,
      mediaQuery: null,
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

    openInfoDialog() {
      this.$refs.infoDialog.showModal();
    },

    closeInfoDialog() {
      this.$refs.infoDialog.close();
    },
  },

  components: {
    field: Field,
    'quill-editor': QuillEditor,
  },
};
</script>
