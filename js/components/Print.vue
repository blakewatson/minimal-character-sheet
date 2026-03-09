<template>
  <div class="print-sheet">
    <h1 class="character-name text-left">{{ characterName }}</h1>

    <!-- Character info -->
    <div class="mb-md flex flex-wrap">
      <print-field :label="$t('Class')" :value="className"></print-field>

      <print-field :label="$t('Level')" :value="level"></print-field>

      <print-field :label="$t('Background')" :value="background"></print-field>

      <print-field
        :label="$t(is_2024 ? 'Species' : 'Race')"
        :value="race"
      ></print-field>

      <print-field :label="$t('XP')" :value="xp"></print-field>

      <print-field :label="$t('Alignment')" :value="alignment"></print-field>
    </div>

    <!-- Vitals -->
    <div class="align-items-center gap-lg flex flex-wrap">
      <print-field :label="$t('AC')" :value="ac" big box center></print-field>

      <print-field :label="$t('HP')" :value="`_________/${maxHp}`"></print-field>

      <print-field :label="$t('Temp HP')" value="_________"></print-field>

      <print-field :label="$t('Hit die')" :value="hitDieValue"></print-field>

      <print-field :label="$t('Speed')" :value="speed"></print-field>

      <print-field :label="$t('Death saves')" center>
        <div>
          <div class="align-items-center gap-sm mb-xs flex">
            <span class="mini-icon" style="position: relative; top: -2px">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path
                  d="M557 152.9L538.2 178.8L282.2 530.8L260.2 561.1C259.5 560.4 208 508.9 105.7 406.6L83 384L128.3 338.7C130.2 340.6 171.6 382 252.4 462.8L486.4 141.1L505.2 115.2L557 152.8z"
                />
              </svg>
            </span>
            <input type="checkbox" v-for="_ in Array(3).fill(true)" />
          </div>

          <div class="align-items-center gap-sm flex">
            <span class="mini-icon" style="position: relative; top: -2px">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path
                  d="M432 467.4L432 528L392 528L392 480L344 480L344 528L296 528L296 480L248 480L248 528L208 528L208 467.4L188.8 453C141.3 417.2 112 363.4 112 304C112 200.8 202.2 112 320 112C437.8 112 528 200.8 528 304C528 363.4 498.7 417.2 451.2 453L432 467.4zM480 491.4C538.5 447.4 576 379.8 576 304C576 171.5 461.4 64 320 64C178.6 64 64 171.5 64 304C64 379.8 101.5 447.4 160 491.4L160 576L480 576L480 491.4zM288 320C288 289.1 262.9 264 232 264C201.1 264 176 289.1 176 320C176 350.9 201.1 376 232 376C262.9 376 288 350.9 288 320zM408 376C438.9 376 464 350.9 464 320C464 289.1 438.9 264 408 264C377.1 264 352 289.1 352 320C352 350.9 377.1 376 408 376z"
                />
              </svg>
            </span>
            <input type="checkbox" v-for="_ in Array(3).fill(true)" />
          </div>
        </div>
      </print-field>
    </div>

    <!-- Conditions -->
    <div class="mt-md flex">
      <print-field
        :label="$t('Conditions')"
        center
        value="____________________________________"
      ></print-field>

      <print-field
        :label="$t('Concentration')"
        center
        value="____________________________________"
      ></print-field>
    </div>

    <!-- Initiative, Proficiency bonus, Inspiration, Short rests -->
    <div class="align-items-end mt-sm flex">
      <print-field :label="$t('Initiative')" :value="initiative"></print-field>

      <print-field
        :label="$t('Proficiency bonus')"
        :value="proficiencyBonus | signedNumString"
      ></print-field>

      <print-field :label="$t('Inspiration')">
        <input type="checkbox" v-model="inspiration" />
      </print-field>

      <print-field :label="$t('Short rests')" value="_________"></print-field>
    </div>

    <!-- Ability scores -->
    <div class="mt-md flex">
      <print-field
        v-for="(ability, i) in abilities"
        :key="ability.name"
        box
        center
        style="position: relative"
      >
        <div>
          <div class="print-field-big">
            {{ modifiers[i].val | signedNumString }}
          </div>
          <div>{{ ability.score }}</div>

          <div v-if="savingThrows[i].proficient">
            <div
              class="print-field-label text-center"
              style="
                position: absolute;
                top: calc(100% + 0.25rem);
                left: 50%;
                transform: translateX(-50%);
                width: auto;
              "
            >
              {{
                (modifiers[i].val + proficiencyBonus) | signedNumString
              }}&nbsp;{{ $t('Save (throw)') }}
            </div>
          </div>
        </div>

        <template #label>
          <p class="print-field-label caps">{{ ability.name }}</p>
        </template>
      </print-field>
    </div>

    <!-- Skills -->
    <div class="skills mt-lg">
      <p class="header">{{ $t('Skills') }}</p>
      <ul class="mb-sm pl-none" style="columns: 3">
        <li class="align-items-center gap-sm flex" v-for="skill in skills">
          <div
            class="align-items-center justify-content-end no-gap flex"
            style="width: 32px"
          >
            <span
              class="mini-icon"
              style="position: relative; top: -2px"
              v-if="skill.doubleProficient"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path
                  d="M557 152.9L538.2 178.8L282.2 530.8L260.2 561.1C259.5 560.4 208 508.9 105.7 406.6L83 384L128.3 338.7C130.2 340.6 171.6 382 252.4 462.8L486.4 141.1L505.2 115.2L557 152.8z"
                />
              </svg>
            </span>

            <span
              class="mini-icon"
              style="position: relative; top: -2px"
              v-if="skill.proficient"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path
                  d="M557 152.9L538.2 178.8L282.2 530.8L260.2 561.1C259.5 560.4 208 508.9 105.7 406.6L83 384L128.3 338.7C130.2 340.6 171.6 382 252.4 462.8L486.4 141.1L505.2 115.2L557 152.8z"
                />
              </svg>
            </span>
          </div>

          <div class="text-left">
            <strong
              class="mr-xs text-right"
              style="width: 20px; display: inline-block"
              >{{ getSkillModifier(skill) | signedNumString }}</strong
            >
            {{ skill.name }}
            <small class="caps muted">({{ skill.ability }})</small>
          </div>
        </li>
      </ul>

      <div class="text-center">
        <strong class="text-right">{{ getPassivePerception() }}</strong>
        {{ $t('Passive Perception') }}
        <small class="caps muted">({{ $t('WIS') }})</small>
      </div>
    </div>

    <!-- Attacks -->
    <div class="mt-md">
      <p class="header">{{ $t('Attacks & Weapons') }}</p>

      <table class="attacks-table">
        <thead>
          <tr>
            <th>{{ $t('Name') }}</th>
            <th class="text-right">{{ $t('Atk Bonus') }}</th>
            <th>{{ $t('Damage') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in attacksAndNotes" :key="row.id">
            <td v-if="row.isAttack">{{ row.name }}</td>
            <td v-if="row.isAttack" class="text-right">
              {{ row.attackBonus }}
            </td>
            <td v-if="row.isAttack">{{ row.damage }}</td>

            <td
              :class="{ 'weapon-notes': row.isNote }"
              colspan="4"
              v-if="row.isNote"
            >
              <small class="muted" v-html="row.weaponNotes"></small>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Trackable Fields -->
    <div class="mt-md" v-if="trackableFields.length > 0">
      <p class="header">{{ $t('Trackable Fields') }}</p>

      <table class="attacks-table" style="max-width: 5in">
        <thead>
          <tr>
            <th>{{ $t('Name') }}</th>
            <th class="text-right">{{ $t('Used') }}</th>
            <th class="text-right">{{ $t('Max') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr :key="row.id" v-for="row in trackableFieldsAndNotes">
            <td v-if="row.isField">{{ row.name }}</td>
            <td v-if="row.isField" class="pt-md text-right">_________</td>
            <td v-if="row.isField" class="text-right">
              {{ row.max }}
            </td>

            <td
              :class="{ 'weapon-notes': row.isNote }"
              colspan="3"
              v-if="row.isNote"
            >
              <small class="muted" v-html="row.notes"></small>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Features & Traits -->
    <div class="mt-md" v-if="featuresText">
      <p class="header">{{ $t('Features & Traits') }}</p>

      <div
        class="quill-html mt-sm"
        v-html="getHtmlFromQuill(featuresText)"
      ></div>
    </div>

    <!-- Equipment -->
    <div class="mt-md">
      <p class="header">{{ $t('Equipment') }}</p>

      <div class="flex">
        <print-field
          v-for="coin in coins"
          :key="coin.name"
          :value="coin.amount"
          :label="coin.name"
        ></print-field>
      </div>

      <div
        class="quill-html mt-sm"
        v-if="equipmentText"
        v-html="getHtmlFromQuill(equipmentText)"
      ></div>
    </div>

    <!-- Proficiencies -->
    <div class="mt-md" v-if="proficienciesText">
      <p class="header">{{ $t('Other Proficiencies & Languages') }}</p>

      <div
        class="quill-html mt-sm"
        v-html="getHtmlFromQuill(proficienciesText)"
      ></div>
    </div>

    <!-- Spellcasting -->
    <div class="mt-md" v-if="hasSpells">
      <p class="header">{{ $t('Spellcasting') }}</p>

      <div class="align-items-center flex">
        <print-field :label="$t('Class')" :value="spClass"> </print-field>

        <print-field
          :label="$t('Ability')"
          :value="`${spAbility} (${signedNumString(getAbilityModifier(spAbility))})`"
        ></print-field>

        <print-field box center>
          <div class="print-field-big">
            {{ spSave }}
          </div>

          <template #label>
            <p class="print-field-label">{{ $t('Save (throw)') }}</p>
          </template>
        </print-field>

        <print-field box center>
          <div class="print-field-big">
            {{ spAttack }}
          </div>

          <template #label>
            <p class="print-field-label">{{ $t('Attack') }}</p>
          </template>
        </print-field>
      </div>

      <div class="cantrips mt-md" v-if="cantripsList.length > 0">
        <p class="header">{{ $t('Cantrips') }}</p>

        <div
          class="quill-html card"
          v-for="cantrip in cantripsList"
          v-html="getHtmlFromQuill(cantrip.val)"
        ></div>
      </div>

      <div v-for="(spellData, idx) in allSpellLevels">
        <div class="mt-lg" v-if="spellData.spells.length > 0">
          <p class="header">{{ $t('Level {level} spells', { level: idx + 1 }) }}</p>

          <div class="pt-md flex">
            <print-field
              :label="$t('Slots')"
              :value="spellData.slots"
              bold
            ></print-field>

            <print-field
              :label="$t('Expended')"
              value="_______________________"
            ></print-field>
          </div>

          <div class="card" v-for="spell in spellData.spells">
            <input
              type="checkbox"
              class="prepared-checkbox"
              v-model="spell.prepared"
            />
            <div class="quill-html" v-html="getHtmlFromQuill(spell.name)"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-for="section in textSections">
      <div
        class="mt-lg"
        v-if="hasQuillContent(section.text) || section.title === $t('Notes')"
      >
        <p class="header">{{ section.title }}</p>
        <div class="quill-html" v-html="getHtmlFromQuill(section.text)"></div>
      </div>
    </div>
  </div>
</template>

<script>
import Quill from 'quill';
import { mapState } from 'vuex';
import { mapGetters } from 'vuex/dist/vuex.common.js';
import { signedNumString } from '../utils';
import PrintField from './PrintField.vue';

export default {
  name: 'Print',

  data() {
    return {};
  },

  computed: {
    ...mapState([
      'characterName',
      'is_2024',
      'race',
      'background',
      'className',
      'level',
      'xp',
      'alignment',
      'hp',
      'maxHp',
      'tempHp',
      'hitDie',
      'totalHitDie',
      'ac',
      'speed',
      'initiative',
      'inspiration',
      'abilities',
      'savingThrows',
      'skills',
      'attacks',
      'coins',
      'trackableFields',
      'equipmentText',
      'proficienciesText',
      'featuresText',
      'personalityText',
      'backstoryText',
      'treasureText',
      'organizationsText',
      'notesText',
      'spClass',
      'spAbility',
      'spSave',
      'spAttack',
      'cantripsList',
      'lvl1Spells',
      'lvl2Spells',
      'lvl3Spells',
      'lvl4Spells',
      'lvl5Spells',
      'lvl6Spells',
      'lvl7Spells',
      'lvl8Spells',
      'lvl9Spells',
      'is_2024',
    ]),

    ...mapGetters(['modifiers', 'proficiencyBonus']),

    allSpellLevels() {
      return [
        this.lvl1Spells,
        this.lvl2Spells,
        this.lvl3Spells,
        this.lvl4Spells,
        this.lvl5Spells,
        this.lvl6Spells,
        this.lvl7Spells,
        this.lvl8Spells,
        this.lvl9Spells,
      ];
    },

    trackableFieldsAndNotes() {
      const rows = [];

      this.trackableFields.forEach((field) => {
        rows.push({
          ...field,
          isField: true,
        });

        if (field.notes) {
          var html = this.getHtmlFromQuill(field.notes);
        }

        rows.push({
          id: field.id + '-note',
          isNote: true,
          fieldId: field.id,
          notes: html || '',
        });
      });

      return rows;
    },

    attacksAndNotes() {
      const rows = [];

      this.attacks.forEach((attack, index) => {
        rows.push({
          ...attack,
          isAttack: true,
        });

        if (attack.weaponNotes) {
          var html = this.getHtmlFromQuill(attack.weaponNotes);
        }

        rows.push({
          id: attack.id + '-note',
          isNote: true,
          attackId: attack.id,
          weaponNotes: html || '',
        });
      });

      return rows;
    },

    hasSpells() {
      return (
        this.cantripsList.length > 0 ||
        this.lvl1Spells.spells.length > 0 ||
        this.lvl2Spells.spells.length > 0 ||
        this.lvl3Spells.spells.length > 0 ||
        this.lvl4Spells.spells.length > 0 ||
        this.lvl5Spells.spells.length > 0 ||
        this.lvl6Spells.spells.length > 0 ||
        this.lvl7Spells.spells.length > 0 ||
        this.lvl8Spells.spells.length > 0 ||
        this.lvl9Spells.spells.length > 0
      );
    },

    hitDieType() {
      const parts = this.hitDie.split('d');

      if (parts.length !== 2) {
        return null;
      }

      if (parseInt(parts[1])) {
        return parts[1];
      }

      return null;
    },

    hitDieValue() {
      if (!this.hitDieType) {
        return `_________ ${this.hitDie} / ${this.totalHitDie}`;
      }

      return `______ d${this.hitDieType} / ${this.totalHitDie}`;
    },

    textSections() {
      return [
        {
          title: this.$t('Traits, Ideals, Bonds, & Flaws'),
          text: this.personalityText,
        },
        {
          title: this.$t('Appearance & Backstory'),
          text: this.backstoryText,
        },
        {
          title: this.$t('Treasure'),
          text: this.treasureText,
        },
        {
          title: this.$t('Allies & Organizations'),
          text: this.organizationsText,
        },
        {
          title: this.$t('Notes'),
          text: this.notesText,
        },
      ];
    },
  },

  methods: {
    getAbilityModifier(ability) {
      const mod = this.modifiers.reduce((acc, m) => {
        if (m.ability === ability) return acc + m.val;
        return acc;
      }, 0);
      return mod;
    },

    getHtmlFromQuill(delta) {
      if (!delta) {
        return '';
      }

      const container = document.createElement('div'); // Not attached to the DOM
      const quill = new Quill(container);
      quill.setContents(delta);
      return quill.getSemanticHTML().replaceAll('&nbsp;', ' ');
    },

    getPassivePerception() {
      // Find the Perception skill
      const perceptionSkill = this.skills.find(
        (skill) => skill.name === 'Perception',
      );
      if (perceptionSkill) {
        return 10 + this.getSkillModifier(perceptionSkill);
      }
      // Fallback to just Wisdom modifier if Perception skill not found
      return 10 + this.getSkillModifier({ ability: 'WIS' });
    },

    getSkillModifier(skill) {
      var mod = this.modifiers.reduce((acc, m) => {
        if (m.ability === skill.ability) return acc + m.val;
        return acc;
      }, 0);

      if (
        skill.modifierOverride !== null &&
        skill.modifierOverride !== undefined
      ) {
        return skill.modifierOverride;
      }

      if (skill.doubleProficient) {
        return mod + this.proficiencyBonus * 2;
      }

      if (skill.proficient) {
        return mod + this.proficiencyBonus;
      }

      return mod;
    },

    hasQuillContent(delta) {
      if (!delta) {
        return false;
      }

      if (
        'ops' in delta &&
        delta.ops.length === 1 &&
        delta.ops[0].insert === '\n'
      ) {
        return false;
      }

      return Object.keys(delta).length > 0;
    },

    signedNumString(num) {
      return signedNumString(num);
    },
  },

  created() {
    // Initialize the Vuex store with character data from the server
    if (typeof window.sheet !== 'undefined') {
      this.$store.dispatch('initializeState', {
        sheet: window.sheet,
      });
    }
  },

  components: {
    'print-field': PrintField,
  },
};
</script>
