<template>
  <div class="print-sheet">
    <h1 class="character-name text-left">{{ characterName }}</h1>

    <!-- Character info -->
    <div class="flex flex-wrap mb-md">
      <print-field :value="className" label="Class"></print-field>

      <print-field :value="level" label="Level"></print-field>

      <print-field :value="background" label="Background"></print-field>

      <print-field
        :label="is_2024 ? 'Species' : 'Race'"
        :value="race"
      ></print-field>

      <print-field :value="xp" label="XP"></print-field>

      <print-field :value="alignment" label="Alignment"></print-field>
    </div>

    <!-- Vitals -->
    <div class="flex flex-wrap align-items-center gap-lg">
      <print-field :value="ac" label="AC" box big center></print-field>

      <print-field :value="`_________/${maxHp}`" label="HP"></print-field>

      <print-field value="_________" label="Temp HP"></print-field>

      <print-field :value="hitDieValue" label="Hit dice"></print-field>

      <print-field :value="speed" label="Speed"></print-field>

      <print-field label="Death saves" center>
        <div>
          <div class="flex align-items-center gap-sm mb-xs">
            <span class="mini-icon" style="position: relative; top: -2px">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path
                  d="M557 152.9L538.2 178.8L282.2 530.8L260.2 561.1C259.5 560.4 208 508.9 105.7 406.6L83 384L128.3 338.7C130.2 340.6 171.6 382 252.4 462.8L486.4 141.1L505.2 115.2L557 152.8z"
                />
              </svg>
            </span>
            <input type="checkbox" v-for="_ in Array(3).fill(true)" />
          </div>

          <div class="flex align-items-center gap-sm">
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
    <div class="flex mt-md">
      <print-field
        label="Conditions"
        value="____________________________________"
        center
      ></print-field>

      <print-field
        label="Concentration"
        value="____________________________________"
        center
      ></print-field>
    </div>

    <!-- Initiative, Proficiency bonus, Inspiration, Short rests -->
    <div class="flex align-items-end mt-sm">
      <print-field :value="initiative" label="Initiative"></print-field>

      <print-field
        :value="proficiencyBonus | signedNumString"
        label="Proficiency bonus"
      ></print-field>

      <print-field label="Inspiration">
        <input type="checkbox" v-model="inspiration" />
      </print-field>

      <print-field value="_________" label="Short rests"></print-field>
    </div>

    <!-- Ability scores -->
    <div class="flex mt-md">
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
              }}&nbsp;Save
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
      <p class="header">Skills</p>
      <ul class="mb-sm pl-none" style="columns: 3">
        <li class="flex align-items-center gap-sm" v-for="skill in skills">
          <div
            class="flex align-items-center justify-content-end no-gap"
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
        Passive Perception
        <small class="caps muted">(WIS)</small>
      </div>
    </div>

    <!-- Attacks -->
    <div class="mt-md">
      <p class="header">Attacks</p>

      <table class="attacks-table">
        <thead>
          <tr>
            <th>Name</th>
            <th class="text-right">Atk Bonus</th>
            <th>Damage</th>
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

    <!-- Equipment -->
    <div class="mt-md">
      <p class="header">Equipment</p>

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
      <p class="header">Proficiencies</p>

      <div
        class="quill-html mt-sm"
        v-html="getHtmlFromQuill(proficienciesText)"
      ></div>
    </div>

    <!-- Features & Traits -->
    <div class="mt-md" v-if="featuresText">
      <p class="header">Features & Traits</p>

      <div
        class="quill-html mt-sm"
        v-html="getHtmlFromQuill(featuresText)"
      ></div>
    </div>

    <!-- Spellcasting -->
    <div class="mt-md" v-if="hasSpells">
      <p class="header">Spellcasting</p>

      <div class="flex align-items-center">
        <print-field :value="spClass" label="Class"> </print-field>

        <print-field
          :value="`${spAbility} (${signedNumString(getAbilityModifier(spAbility))})`"
          label="Ability"
        ></print-field>

        <print-field box center>
          <div class="print-field-big">
            {{ spSave }}
          </div>

          <template #label>
            <p class="print-field-label">Save</p>
          </template>
        </print-field>

        <print-field box center>
          <div class="print-field-big">
            {{ spAttack }}
          </div>

          <template #label>
            <p class="print-field-label">Attack</p>
          </template>
        </print-field>
      </div>

      <div class="mt-md" v-if="cantripsList.length > 0">
        <p class="header">Cantrips</p>

        <div
          class="quill-html card"
          v-for="cantrip in cantripsList"
          v-html="getHtmlFromQuill(cantrip.val)"
        ></div>
      </div>

      <div v-for="(spellData, idx) in allSpellLevels">
        <div class="mt-lg" v-if="spellData.spells.length > 0">
          <p class="header">Level {{ idx + 1 }} spells</p>

          <div class="flex pt-md">
            <print-field
              :value="spellData.slots"
              label="Slots"
              bold
            ></print-field>

            <print-field
              label="Expended"
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
        this.lvl1Spells.length > 0 ||
        this.lvl2Spells.length > 0 ||
        this.lvl3Spells.length > 0 ||
        this.lvl4Spells.length > 0 ||
        this.lvl5Spells.length > 0 ||
        this.lvl6Spells.length > 0 ||
        this.lvl7Spells.length > 0 ||
        this.lvl8Spells.length > 0 ||
        this.lvl9Spells.length > 0
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
