<template>
  <details
    open
    class="border-light-foreground dark:border-dark-foreground border-t pb-8"
  >
    <summary class="section-label">{{ $t('Attacks & Weapons') }}</summary>

    <!-- Desktop Table Layout -->
    <table v-if="attacks.length > 0 && !isMobile" class="mb-2 w-full text-sm">
      <thead>
        <tr>
          <th class="px-2 text-left">{{ $t('Name') }}</th>
          <th class="px-2 text-right">{{ $t('Atk Bonus') }}</th>
          <th class="px-2 text-left">{{ $t('Damage') }}</th>
          <th class="px-2 text-right">{{ $t('Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        <!-- Every other row is a note. -->
        <tr
          v-for="(a, i) in attacksAndNotes"
          :key="a.id"
          :class="{
            'border-t border-neutral-400 dark:border-neutral-500':
              'isAttack' in a,
          }"
          :style="{ 'z-index': attacks.length - i }"
        >
          <td v-if="'isAttack' in a" class="p-2">
            <field
              :auto-size="false"
              :placeholder="$t('Weapon')"
              :read-only="readOnly"
              :value="a.name"
              @update-value="updateAttacks(a.id, 'name', $event)"
              class="w-full text-left text-[13px]! font-bold"
            ></field>
          </td>

          <td
            v-if="'isAttack' in a"
            class="w-px p-2 text-right whitespace-nowrap"
          >
            <field
              :class="isMobile ? 'text-sm!' : ''"
              :read-only="readOnly"
              :value="a.attackBonus"
              @update-value="updateAttacks(a.id, 'attackBonus', $event)"
              class="text-right"
            ></field>
          </td>

          <td v-if="'isAttack' in a" class="p-2">
            <field
              :auto-size="false"
              :read-only="readOnly"
              :value="a.damage"
              @update-value="updateAttacks(a.id, 'damage', $event)"
              class="w-full text-left text-[13px]!"
              :placeholder="$t('Ex: 1d6 slashing')"
            ></field>
          </td>

          <td v-if="'isAttack' in a" class="w-px p-2 whitespace-nowrap">
            <div class="flex items-center justify-end gap-1">
              <button
                :disabled="readOnly"
                :title="$t('Move up')"
                @click="sortAttacks(a.id, 'up')"
                class="button-icon cursor-pointer"
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
                @click="sortAttacks(a.id, 'down')"
                class="button-icon cursor-pointer"
                type="button"
                v-if="!readOnly && i < attacksAndNotes.length - 2"
              >
                <span class="sr-only">{{ $t('Move down') }}</span>
                <i
                  class="fa-sharp fa-regular fa-arrow-down"
                  role="presentation"
                ></i>
              </button>

              <button
                :disabled="readOnly"
                :title="$t('Delete attack')"
                @click="deleteAttack(a.id)"
                class="button-icon danger"
                type="button"
                v-if="!readOnly"
              >
                <span class="sr-only">{{ $t('Delete attack') }}</span>
                <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
              </button>
            </div>
          </td>

          <td v-if="'isNote' in a" colspan="4">
            <div class="flex items-center gap-2 pb-2">
              <span class="small-label">{{ $t('Notes') }}</span>
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
            :class="isMobile ? '' : 'text-sm!'"
            :placeholder="$t('Weapon name')"
            :read-only="readOnly"
            :value="a.name"
            @update-value="updateAttacks(a.id, 'name', $event)"
            class="grow font-bold"
          ></field>

          <button
            :disabled="readOnly"
            :title="$t('Move up')"
            @click="sortAttacks(a.id, 'up')"
            class="button-icon cursor-pointer"
            type="button"
            v-if="!readOnly && i > 0"
          >
            <span class="sr-only">{{ $t('Move up') }}</span>
            <i class="fa-sharp fa-regular fa-arrow-up" role="presentation"></i>
          </button>

          <button
            :disabled="readOnly"
            :title="$t('Move down')"
            @click="sortAttacks(a.id, 'down')"
            class="button-icon cursor-pointer"
            type="button"
            v-if="!readOnly && i < attacks.length - 1"
          >
            <span class="sr-only">{{ $t('Move down') }}</span>
            <i
              class="fa-sharp fa-regular fa-arrow-down"
              role="presentation"
            ></i>
          </button>

          <button
            :disabled="readOnly"
            :title="$t('Delete attack')"
            @click="deleteAttack(a.id)"
            class="button-icon cursor-pointer hover:border-red-600 hover:text-red-600"
            type="button"
            v-if="!readOnly"
          >
            <span class="sr-only">{{ $t('Delete attack') }}</span>
            <i class="fa-sharp fa-regular fa-xmark" role="presentation"></i>
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-x-4">
          <div class="flex items-baseline gap-1">
            <label :for="`attack-bonus-${a.id}`" class="small-label">
              {{ $t(isMobile ? 'Atk Bonus' : 'Attack Bonus') }}
            </label>
            <field
              :class="isMobile ? '' : 'text-sm!'"
              :id="`attack-bonus-${a.id}`"
              :read-only="readOnly"
              :value="a.attackBonus"
              @update-value="updateAttacks(a.id, 'attackBonus', $event)"
            ></field>
          </div>

          <div class="flex items-baseline gap-1">
            <label :for="`attack-damage-${a.id}`" class="small-label">{{
              $t('Damage')
            }}</label>
            <field
              :class="isMobile ? '' : 'text-sm!'"
              :id="`attack-damage-${a.id}`"
              :read-only="readOnly"
              :value="a.damage"
              @update-value="updateAttacks(a.id, 'damage', $event)"
            ></field>
          </div>
        </div>

        <div>
          <label class="small-label">{{ $t('Notes') }}</label>
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
        :title="$t('Add an attack')"
        @click="addAttack()"
        class="button-icon cursor-pointer"
        type="button"
      >
        <span class="sr-only">{{ $t('Add an attack') }}</span>
        <i class="fa-sharp fa-regular fa-plus" role="presentation"></i>
      </button>
    </p>
  </details>
</template>

<script>
import { state, modifiers as storeModifiers } from '../store';
import Field from './Field.vue';
import QuillEditor from './QuillEditor.vue';

/** @typedef {import('../store').Attack} Attack */

/**
 * @typedef {Object} NoteRow
 * @property {string} id
 * @property {boolean} isNote
 * @property {number} attackId
 * @property {object | null} weaponNotes
 */

/**
 * @typedef {Attack & { isAttack: boolean }} AttackRow
 */

export default {
  name: 'Attacks',

  data() {
    return {
      isMobile: false,
      /** @type {MediaQueryList | null} */
      mediaQuery: null,
    };
  },

  computed: {
    attacks() {
      return state.attacks;
    },
    readOnly() {
      return state.readOnly;
    },
    modifiers() {
      return storeModifiers.value;
    },

    attacksAndNotes() {
      /** @type {Array<AttackRow | NoteRow>} */
      const rows = [];

      this.attacks.forEach((attack) => {
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

  beforeUnmount() {
    if (this.mediaQuery) {
      this.mediaQuery.removeEventListener(
        'change',
        this.handleMediaQueryChange,
      );
    }
  },

  methods: {
    setupMediaQuery() {
      this.mediaQuery = window.matchMedia('(max-width: 675px)');
      this.isMobile = this.mediaQuery.matches;
      this.mediaQuery.addEventListener('change', this.handleMediaQueryChange);
    },

    /** @param {MediaQueryListEvent} event */
    handleMediaQueryChange(event) {
      this.isMobile = event.matches;
    },

    /**
     * @param {string | number} id
     * @param {keyof Omit<Attack, 'id'>} field
     * @param {string | number | object | null} val
     */
    updateAttacks(id, field, val) {
      if (typeof id === 'string' && id.endsWith('-note')) {
        id = parseInt(id.slice(0, -5)); // Remove '-note' suffix for attack ID
      }

      state.attacks = state.attacks.map((attack) => {
        if (attack.id === id) {
          return {
            ...attack,
            [field]: val,
          };
        }
        return attack;
      });
    },

    /** @param {number} id */
    deleteAttack(id) {
      state.attacks = state.attacks.filter((attack) => attack.id !== id);
    },

    addAttack() {
      /** @type {Attack} */
      const attack = {
        id: Date.now(),
        name: '',
        attackBonus: '0',
        damage: '',
        weaponNotes: null,
      };
      state.attacks.push(attack);
    },

    /**
     * @param {number} id
     * @param {'up' | 'down'} direction
     */
    sortAttacks(id, direction) {
      let curIndex = state.attacks.findIndex((a) => a.id === id);

      if (curIndex === -1) {
        return;
      }

      if (direction === 'up') {
        if (curIndex === 0) return;
        var deletedAttacks = state.attacks.splice(curIndex, 1);
        var attackToMove = deletedAttacks[0];
        state.attacks.splice(curIndex - 1, 0, attackToMove);
        return;
      }

      if (direction === 'down') {
        if (curIndex === state.attacks.length - 1) return;
        var deletedAttacks = state.attacks.splice(curIndex, 1);
        var attackToMove = deletedAttacks[0];
        state.attacks.splice(curIndex + 1, 0, attackToMove);
        return;
      }
    },
  },

  components: {
    field: Field,
    'quill-editor': QuillEditor,
  },
};
</script>
