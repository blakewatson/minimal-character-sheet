<template>
  <details open class="section">
    <summary class="label centered">Attacks & Weapons</summary>

    <!-- Desktop Table Layout -->
    <table v-show="attacks.length > 0 && !isMobile" class="attacks-table">
      <thead>
        <tr>
          <th class="text-left">Name</th>
          <th class="text-left">Atk Bonus</th>
          <th class="text-left">Damage</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Every other row is a note. -->
        <tr
          v-for="(a, i) in attacksAndNotes"
          :key="a.id"
          :class="{ 'attack-row': a.isAttack, 'note-row': a.isNote }"
          :style="{ 'z-index': attacks.length - i }"
          class="deletable"
        >
          <td v-if="a.isAttack">
            <field
              :read-only="readOnly"
              :value="a.name"
              @update-value="updateAttacks(a.id, 'name', $event)"
              class="size-full small text-left strong"
              placeholder="Weapon"
            ></field>
          </td>
          <td v-if="a.isAttack" class="text-center small">
            <field
              :read-only="readOnly"
              :value="a.attackBonus"
              @update-value="updateAttacks(a.id, 'attackBonus', $event)"
            ></field>
          </td>
          <td v-if="a.isAttack">
            <field
              :read-only="readOnly"
              :value="a.damage"
              @update-value="updateAttacks(a.id, 'damage', $event)"
              class="size-full small text-left"
              placeholder="Ex: 1d6 slashing"
            ></field>
          </td>
          <td v-if="a.isAttack" class="vert-center" style="gap: 0.2em; justify-content: flex-end;">
            <button
                :disabled="readOnly"
                @click="sortAttacks(a.id, 'up')"
                class="button button-sort"
                type="button"
                v-if="!readOnly && i > 0"
            >
                <span class="sr-only">Move up</span>
                <span role="presentation">&uarr;</span>
            </button>

            <button
                :disabled="readOnly"
                @click="sortAttacks(a.id, 'down')"
                class="button button-sort"
                type="button"
                v-if="!readOnly && i < attacksAndNotes.length - 2"
            >
                <span class="sr-only">Move down</span>
                <span role="presentation">&darr;</span>
            </button>

            <button
              :disabled="readOnly"
              @click="deleteAttack(a.id)"
              class="button button-delete"
              type="button"
              v-if="!readOnly"
            >
              <span class="sr-only">Delete attack</span>
              <span role="presentation">&times;</span>
            </button>
          </td>

          <td v-if="a.isNote" colspan="4">
            <div class="vert-center pl-lg" style="gap: 0.5em;">
                <span class="meta small muted">Notes</span>
                <quill-editor
                  :initial-contents="a.weaponNotes"
                  :read-only="readOnly"
                  :toolbar-options="['bold', 'italic', 'strike', 'link']"
                  @quill-text-change="updateAttacks(a.id, 'weaponNotes', $event)"
                  class="attack-notes"
                  style="width: 100%"
                ></quill-editor>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Mobile Card Layout -->
    <div v-show="attacks.length > 0 && isMobile" class="attacks-mobile">
      <div v-for="(a, i) in attacks" :key="a.id" class="attack-card">
        <div class="attack-header">
            <field
                class="attack-name"
                align="left"
                :value="a.name"
                :read-only="readOnly"
                @update-value="updateAttacks(a.id, 'name', $event)"
                placeholder="Weapon name"
            ></field>
          
            <button
                :disabled="readOnly"
                @click="sortAttacks(a.id, 'up')"
                class="button button-sort"
                type="button"
                v-if="!readOnly && i > 0"
            >
                <span class="sr-only">Move up</span>
                <span role="presentation">&uarr;</span>
            </button>

            <button
                :disabled="readOnly"
                @click="sortAttacks(a.id, 'down')"
                class="button button-sort"
                type="button"
                v-if="!readOnly && i < attacks.length - 1"
            >
                <span class="sr-only">Move down</span>
                <span role="presentation">&darr;</span>
            </button>
          <button
            v-if="!readOnly"
            type="button"
            class="button button-delete"
            :disabled="readOnly"
            @click="deleteAttack(a.id)"
          >
            <span class="sr-only">Delete attack</span>
            <span role="presentation">&times;</span>
          </button>
        </div>

        <div class="attack-stats">
          <div class="stat-group">
            <label class="meta small muted" :for="`attack-bonus-${a.id}`">
              {{ isMobile ? "Atk Bonus" : "Attack Bonus" }}
            </label>
            <field
              :id="`attack-bonus-${a.id}`"
              class="stat-value"
              :value="a.attackBonus"
              :read-only="readOnly"
              @update-value="updateAttacks(a.id, 'attackBonus', $event)"
            ></field>
          </div>
          <div class="stat-group">
            <label class="meta small muted" :for="`attack-damage-${a.id}`"
              >Damage</label
            >
            <field
              :id="`attack-damage-${a.id}`"
              class="stat-value"
              :value="a.damage"
              :read-only="readOnly"
              @update-value="updateAttacks(a.id, 'damage', $event)"
            ></field>
          </div>
        </div>
        <div class="attack-notes">
          <label class="meta small muted">Notes</label>
          <quill-editor
            :initial-contents="a.weaponNotes"
            :read-only="readOnly"
            :toolbar-options="['bold', 'italic', 'strike', 'link']"
            @quill-text-change="updateAttacks(a.id, 'weaponNotes', $event)"
          ></quill-editor>
        </div>
      </div>
    </div>

    <p class="text-center" v-if="!readOnly">
      <button
        type="button"
        class="button button-add"
        :disabled="readOnly"
        @click="$store.commit('addAttack')"
      >
        <span class="sr-only">Add an attack</span>
        <span role="presentation">+</span>
      </button>
    </p>
  </details>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import Field from "./Field";
import QuillEditor from "./QuillEditor";

export default {
  name: "Attacks",

  data() {
    return {
      isMobile: false,
      mediaQuery: null,
    };
  },

  computed: {
    ...mapState(["attacks", "readOnly"]),
    ...mapGetters(["modifiers"]),

    attacksAndNotes() {
      const rows = [];

      this.attacks.forEach((attack, index) => {
        rows.push({
          ...attack,
          isAttack: true,
        });
        rows.push({
          id: attack.id + '-note',
          isNote: true,
          attackId: attack.id,
          weaponNotes: attack.weaponNotes,
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
      this.mediaQuery = window.matchMedia("(max-width: 675px)");
      this.isMobile = this.mediaQuery.matches;
      this.mediaQuery.addListener(this.handleMediaQueryChange);
    },

    handleMediaQueryChange(event) {
      this.isMobile = event.matches;
    },

    updateAttacks(id, field, val) {
      this.$store.commit("updateAttacks", { id, field, val });
    },

    deleteAttack(id) {
      this.$store.commit("deleteAttack", { id });
    },

    sortAttacks(id, direction) {
        console.log(id, direction);
        this.$store.commit('sortAttacks', { id, direction });
    }
  },

  components: {
    field: Field,
    "quill-editor": QuillEditor,
  },
};
</script>
