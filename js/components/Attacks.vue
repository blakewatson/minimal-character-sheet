<template>
  <details open class="border-t border-neutral-950 pb-8">
    <summary class="section-label">Attacks & Weapons</summary>

    <!-- Desktop Table Layout -->
    <table v-if="attacks.length > 0 && !isMobile" class="mb-2 w-full text-sm">
      <thead>
        <tr>
          <th class="px-2 text-left">Name</th>
          <th class="px-2 text-right">Atk&nbsp;Bonus</th>
          <th class="px-2 text-left">Damage</th>
          <th class="px-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Every other row is a note. -->
        <tr
          v-for="(a, i) in attacksAndNotes"
          :key="a.id"
          :class="{
            'border-t border-neutral-400': a.isAttack,
          }"
          :style="{ 'z-index': attacks.length - i }"
        >
          <td v-if="a.isAttack" class="p-2">
            <field
              :auto-size="false"
              :read-only="readOnly"
              :value="a.name"
              @update-value="updateAttacks(a.id, 'name', $event)"
              class="w-full text-left text-[13px]! font-bold"
              placeholder="Weapon"
            ></field>
          </td>

          <td v-if="a.isAttack" class="w-px p-2 text-right whitespace-nowrap">
            <field
              :read-only="readOnly"
              :value="a.attackBonus"
              @update-value="updateAttacks(a.id, 'attackBonus', $event)"
              class="text-right text-sm!"
            ></field>
          </td>

          <td v-if="a.isAttack" class="p-2">
            <field
              :auto-size="false"
              :read-only="readOnly"
              :value="a.damage"
              @update-value="updateAttacks(a.id, 'damage', $event)"
              class="w-full text-left text-[13px]!"
              placeholder="Ex: 1d6 slashing"
            ></field>
          </td>

          <td v-if="a.isAttack" class="w-px p-2 whitespace-nowrap">
            <div class="flex items-center justify-end gap-1">
              <button
                :disabled="readOnly"
                @click="sortAttacks(a.id, 'up')"
                class="button-icon cursor-pointer"
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
                @click="sortAttacks(a.id, 'down')"
                class="button-icon cursor-pointer"
                title="Move down"
                type="button"
                v-if="!readOnly && i < attacksAndNotes.length - 2"
              >
                <span class="sr-only">Move down</span>
                <i
                  class="fa-sharp fa-regular fa-arrow-down"
                  role="presentation"
                ></i>
              </button>

              <button
                :disabled="readOnly"
                @click="deleteAttack(a.id)"
                class="button-icon cursor-pointer hover:border-red-600 hover:text-red-600"
                title="Delete attack"
                type="button"
                v-if="!readOnly"
              >
                <span class="sr-only">Delete attack</span>
                <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
              </button>
            </div>
          </td>

          <td v-if="a.isNote" colspan="4">
            <div class="flex items-center gap-2 pb-2">
              <span class="small-label">Notes</span>
              <quill-editor
                :initial-contents="a.weaponNotes"
                :read-only="readOnly"
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
    <div v-if="attacks.length > 0 && isMobile" class="">
      <div
        v-for="(a, i) in attacks"
        :key="a.id"
        class="mb-4 rounded border border-neutral-400 p-2"
      >
        <div class="flex items-center justify-between gap-2">
          <field
            :auto-size="false"
            :read-only="readOnly"
            :value="a.name"
            @update-value="updateAttacks(a.id, 'name', $event)"
            class="grow text-sm font-bold"
            placeholder="Weapon name"
          ></field>

          <button
            :disabled="readOnly"
            @click="sortAttacks(a.id, 'up')"
            class="button-icon cursor-pointer"
            type="button"
            v-if="!readOnly && i > 0"
          >
            <span class="sr-only">Move up</span>
            <i class="fa-sharp fa-regular fa-arrow-up" role="presentation"></i>
          </button>

          <button
            :disabled="readOnly"
            @click="sortAttacks(a.id, 'down')"
            class="button-icon cursor-pointer"
            type="button"
            v-if="!readOnly && i < attacks.length - 1"
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
            class="button-icon cursor-pointer hover:border-red-600 hover:text-red-600"
            :disabled="readOnly"
            @click="deleteAttack(a.id)"
          >
            <span class="sr-only">Delete attack</span>
            <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
          </button>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex items-baseline gap-1">
            <label class="small-label" :for="`attack-bonus-${a.id}`">
              {{ isMobile ? 'Atk Bonus' : 'Attack Bonus' }}
            </label>
            <field
              :id="`attack-bonus-${a.id}`"
              class="text-sm!"
              :value="a.attackBonus"
              :read-only="readOnly"
              @update-value="updateAttacks(a.id, 'attackBonus', $event)"
            ></field>
          </div>

          <div class="flex items-baseline gap-1">
            <label class="small-label" :for="`attack-damage-${a.id}`"
              >Damage</label
            >
            <field
              :id="`attack-damage-${a.id}`"
              class="text-sm!"
              :value="a.damage"
              :read-only="readOnly"
              @update-value="updateAttacks(a.id, 'damage', $event)"
            ></field>
          </div>
        </div>

        <div>
          <label class="small-label">Notes</label>
          <quill-editor
            :initial-contents="a.weaponNotes"
            :read-only="readOnly"
            @quill-text-change="updateAttacks(a.id, 'weaponNotes', $event)"
          ></quill-editor>
        </div>
      </div>
    </div>

    <p class="text-center" v-if="!readOnly">
      <button
        :disabled="readOnly"
        @click="$store.commit('addAttack')"
        class="button-icon cursor-pointer"
        title="Add an attack"
        type="button"
      >
        <span class="sr-only">Add an attack</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>
  </details>
</template>

<script>
import { mapGetters, mapState } from 'vuex';
import Field from './Field';
import QuillEditor from './QuillEditor';

export default {
  name: 'Attacks',

  data() {
    return {
      isMobile: false,
      mediaQuery: null,
    };
  },

  computed: {
    ...mapState(['attacks', 'readOnly']),
    ...mapGetters(['modifiers']),

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
      this.mediaQuery = window.matchMedia('(max-width: 675px)');
      this.isMobile = this.mediaQuery.matches;
      this.mediaQuery.addListener(this.handleMediaQueryChange);
    },

    handleMediaQueryChange(event) {
      this.isMobile = event.matches;
    },

    updateAttacks(id, field, val) {
      if (id.toString().endsWith('-note')) {
        id = parseInt(id.slice(0, -5)); // Remove '-note' suffix for attack ID
      }
      this.$store.commit('updateAttacks', { id, field, val });
    },

    deleteAttack(id) {
      this.$store.commit('deleteAttack', { id });
    },

    sortAttacks(id, direction) {
      this.$store.commit('sortAttacks', { id, direction });
    },
  },

  components: {
    field: Field,
    'quill-editor': QuillEditor,
  },
};
</script>
