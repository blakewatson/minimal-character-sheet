// @ts-check

import { computed, reactive } from 'vue';
import { global } from './global';
import levelData from './level-data';

/** @typedef {import('./level-data').LevelData} LevelData */

/**
 * The backend representation of a character sheet.
 * @typedef {Object} Sheet
 * @property {string} id
 * @property {string} slug
 * @property {string} name
 * @property {AppState} data
 * @property {boolean} is_public
 * @property {boolean} is_2024
 * @property {string} created_at
 * @property {string} updated_at
 * @property {string | null} email
 */

/** @typedef {('STR' | 'DEX' | 'CON' | 'INT' | 'WIS' | 'CHA')} AbilityName

/**
 * @typedef {Object} Ability
 * @property {AbilityName} name
 * @property {number} score
 */

/**
 * @typedef {Object} SavingThrow
 * @property {AbilityName} name
 * @property {boolean} proficient
 * @property {number | null} modifierOverride
 * @property {boolean} isAdditive
 */

/**
 * @typedef {Object} Skill
 * @property {string} name
 * @property {AbilityName} ability
 * @property {boolean} proficient
 * @property {boolean} doubleProficient
 * @property {number | null} modifierOverride
 * @property {boolean} isAdditive
 * @property {boolean} [isPassivePerception]
 */

/**
 * @typedef {Object} Attack
 * @property {number} id
 * @property {string} name
 * @property {string} attackBonus
 * @property {string} damage
 * @property {(object | null)} weaponNotes
 */

/**
 * @typedef {Object} TrackableField
 * @property {number} id
 * @property {string} name
 * @property {number} used
 * @property {number} max
 * @property {object | null} notes
 */

/**
 * @typedef {Object} ListItem
 * @property {string} id
 * @property {object | null} val
 * @property {boolean} collapsed
 */

/**
 * @typedef {Object} Spell
 * @property {string} id
 * @property {object | null} name The text in the quill editor
 * @property {boolean} prepared
 * @property {boolean} collapsed
 */

/**
 * @typedef {Object} SpellGroup
 * @property {number} slots
 * @property {number} expended
 * @property {Spell[]} spells
 */

/**
 * @typedef {(
 *  'lvl1Spells' |
 *  'lvl2Spells' |
 *  'lvl3Spells' |
 *  'lvl4Spells' |
 *  'lvl5Spells' |
 *  'lvl6Spells' |
 *  'lvl7Spells' |
 *  'lvl8Spells' |
 *  'lvl9Spells'
 * )} SpellGroupKey
 */

/**
 * @typedef {Object} AppState
 * @property {string} id
 * @property {string} slug
 * @property {boolean} is_2024
 * @property {boolean} readOnly
 * @property {LevelData[]} levelData
 * @property {number} level
 * @property {string} characterName
 * @property {string} race
 * @property {string} background
 * @property {string} className
 * @property {number} xp
 * @property {string} alignment
 * @property {string} hp
 * @property {string} maxHp
 * @property {string} tempHp
 * @property {string} hitDie
 * @property {string} totalHitDie
 * @property {string} ac
 * @property {string} speed
 * @property {string} initiative
 * @property {number | null} proficiencyOverride
 * @property {boolean} inspiration
 * @property {number} shortRests
 * @property {({
 *   successes: boolean[],
 *   failures: boolean[]
 * })} deathSaves
 * @property {string} conditions
 * @property {string} concentration
 * @property {Ability[]} abilities
 * @property {SavingThrow[]} savingThrows
 * @property {Skill[]} skills
 * @property {Attack[]} attacks
 * @property {TrackableField[]} trackableFields
 * @property {({
 *   name: string,
 *   amount: number
 * }[])} coins
 * @property {object | null} equipmentText
 * @property {object | null} proficienciesText
 * @property {object | null} featuresText
 * @property {object | null} personalityText
 * @property {object | null} backstoryText
 * @property {object | null} treasureText
 * @property {object | null} organizationsText
 * @property {object | null} notesText
 * @property {boolean} diceMaximized
 * @property {number | null} passivePerceptionOverride
 * @property {boolean} passivePerceptionOverrideIsAdditive
 * @property {string} spClass
 * @property {AbilityName} spAbility
 * @property {string} spSave
 * @property {string} spAttack
 * @property {ListItem[]} cantripsList
 * @property {SpellGroup} lvl1Spells
 * @property {SpellGroup} lvl2Spells
 * @property {SpellGroup} lvl3Spells
 * @property {SpellGroup} lvl4Spells
 * @property {SpellGroup} lvl5Spells
 * @property {SpellGroup} lvl6Spells
 * @property {SpellGroup} lvl7Spells
 * @property {SpellGroup} lvl8Spells
 * @property {SpellGroup} lvl9Spells
 */

/** @type {AppState} */
const defaultState = {
  id: '',
  slug: '',
  is_2024: false,
  readOnly: false,
  levelData: levelData,
  level: 1,
  characterName: '',
  race: '',
  background: '',
  className: '',
  xp: 0,
  alignment: '',
  hp: '0',
  maxHp: '0',
  tempHp: '0',
  hitDie: '1d8',
  totalHitDie: '1',
  ac: '10',
  speed: '25',
  initiative: '',
  proficiencyOverride: null,
  inspiration: false,
  shortRests: 0,
  deathSaves: {
    successes: [false, false, false],
    failures: [false, false, false],
  },
  conditions: '',
  concentration: '',
  abilities: [
    { name: 'STR', score: 10 },
    { name: 'DEX', score: 10 },
    { name: 'CON', score: 10 },
    { name: 'INT', score: 10 },
    { name: 'WIS', score: 10 },
    { name: 'CHA', score: 10 },
  ],
  savingThrows: [
    {
      name: 'STR',
      proficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'DEX',
      proficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'CON',
      proficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'INT',
      proficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'WIS',
      proficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'CHA',
      proficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
  ],
  skills: [
    {
      name: 'Acrobatics',
      ability: 'DEX',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Animal Handling',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Arcana',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Athletics',
      ability: 'STR',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Deception',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'History',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Insight',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Intimidation',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Investigation',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Medicine',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Nature',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Perception',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Performance',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Persuasion',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Religion',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Sleight of Hand',
      ability: 'DEX',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Stealth',
      ability: 'DEX',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
    {
      name: 'Survival',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
      isAdditive: false,
    },
  ],
  attacks: [],
  trackableFields: [],
  coins: [
    { name: 'cp', amount: 0 },
    { name: 'sp', amount: 0 },
    { name: 'ep', amount: 0 },
    { name: 'gp', amount: 0 },
    { name: 'pp', amount: 0 },
  ],
  equipmentText: {},
  proficienciesText: {},
  featuresText: {},
  personalityText: {},
  backstoryText: {},
  treasureText: {},
  organizationsText: {},
  notesText: {},
  diceMaximized: false,
  passivePerceptionOverride: null,
  passivePerceptionOverrideIsAdditive: false,
  spClass: '',
  spAbility: 'WIS',
  spSave: '',
  spAttack: '',
  cantripsList: [],
  lvl1Spells: { slots: 0, expended: 0, spells: [] },
  lvl2Spells: { slots: 0, expended: 0, spells: [] },
  lvl3Spells: { slots: 0, expended: 0, spells: [] },
  lvl4Spells: { slots: 0, expended: 0, spells: [] },
  lvl5Spells: { slots: 0, expended: 0, spells: [] },
  lvl6Spells: { slots: 0, expended: 0, spells: [] },
  lvl7Spells: { slots: 0, expended: 0, spells: [] },
  lvl8Spells: { slots: 0, expended: 0, spells: [] },
  lvl9Spells: { slots: 0, expended: 0, spells: [] },
};

/** @type {Attack} */
const defaultAttack = {
  id: 0,
  name: '',
  attackBonus: '',
  damage: '',
  weaponNotes: null,
};

/** @type {Ability[]} */
const defaultAbilities = [
  { name: 'STR', score: 10 },
  { name: 'DEX', score: 10 },
  { name: 'CON', score: 10 },
  { name: 'INT', score: 10 },
  { name: 'WIS', score: 10 },
  { name: 'CHA', score: 10 },
];

/** @type {TrackableField} */
const defaultTrackableField = {
  id: 0,
  name: '',
  used: 0,
  max: 0,
  notes: null,
};

/** @type {AppState} */
export const state = reactive(JSON.parse(JSON.stringify(defaultState)));

// UI-only keys are excluded from database serialization
const uiOnlyKeys = new Set(['diceMaximized']);

// Computed refs

/**
 * @typedef {Object} Modifier
 * @property {AbilityName} ability
 * @property {number} val
 */

/** @type {import('vue').ComputedRef<Modifier[]>} */
export const modifiers = computed(() => {
  return state.abilities.map((a) => {
    const score =
      typeof a.score === 'number' ? a.score : parseInt(a.score || 0);
    return {
      ability: a.name,
      val: Math.floor(score / 2 - 5),
    };
  });
});

export const proficiencyBonus = computed(() => {
  if (
    state.proficiencyOverride !== null &&
    state.proficiencyOverride !== undefined
  ) {
    return state.proficiencyOverride;
  }
  var level = state.level;
  var row = state.levelData.find((data) => level === data.lvl);
  if (!row) return 2;
  return row.proficiency;
});

// Action functions

export function getJSON() {
  return JSON.stringify(state);
}

export function getSheetJSON() {
  const raw = JSON.parse(getJSON());
  for (const key of uiOnlyKeys) {
    delete raw[key];
  }
  return JSON.stringify(raw);
}

/** @param {{sheet: string}} payload  */
export function initializeState(payload) {
  var sheet = JSON.parse(payload.sheet);
  // Start with a deep copy of the default state
  /** @type {AppState} */
  var newState = JSON.parse(JSON.stringify(defaultState));

  if (sheet.data) {
    // merge sheet data on top of defaults
    newState = Object.assign({}, newState, sheet.data);
  }

  if (newState.skills.length === 0) {
    newState.skills = defaultState.skills.map((skill) => ({ ...skill }));
  }

  if (newState.savingThrows.length === 0) {
    newState.savingThrows = defaultState.savingThrows.map((st) => ({ ...st }));
  }

  // normalize skills
  newState.skills = newState.skills.map((skill) => {
    const defaultSkill = defaultState.skills.find((s) => s.name === skill.name);
    if (!defaultSkill) {
      return skill;
    }
    return {
      ...defaultSkill,
      ...skill,
    };
  });

  // normalize saving throws
  newState.savingThrows = newState.savingThrows.map((savingThrow) => {
    const defaultSavingThrow = defaultState.savingThrows.find(
      (s) => s.name === savingThrow.name,
    );
    if (!defaultSavingThrow) {
      return savingThrow;
    }
    return {
      ...defaultSavingThrow,
      ...savingThrow,
    };
  });

  // default initiative to dex modifier
  if (!newState.initiative) {
    const dex = newState.abilities.find((ability) => ability.name === 'DEX');

    const score = dex?.score || 10;

    newState.initiative = Math.floor(
      // @ts-ignore
      parseInt(score) / 2 - 5,
    ).toString();
  }

  // normalize attacks
  if (newState.attacks && newState.attacks.length > 0) {
    newState.attacks = newState.attacks.map((attack, idx) => ({
      ...defaultAttack,
      ...attack,
      id: attack.id ?? idx,
    }));
  }

  // normalize trackable fields
  if (newState.trackableFields && newState.trackableFields.length > 0) {
    newState.trackableFields = newState.trackableFields.map((field) => ({
      ...defaultTrackableField,
      ...field,
    }));
  }

  // Assign stable IDs and normalize collapsed for all spell levels (D-10, D-11)
  /** @type {SpellGroupKey[]} */
  var spellLevels = [
    'lvl1Spells',
    'lvl2Spells',
    'lvl3Spells',
    'lvl4Spells',
    'lvl5Spells',
    'lvl6Spells',
    'lvl7Spells',
    'lvl8Spells',
    'lvl9Spells',
  ];
  spellLevels.forEach((level) => {
    if (newState[level] && newState[level].spells) {
      newState[level].spells.forEach((spell, idx) => {
        if (!spell.id) {
          spell.id = (Date.now() + idx).toString();
        }
        if (!spell.hasOwnProperty('collapsed')) {
          spell.collapsed = false;
        }
      });
    }
  });

  // Also normalize cantripsList IDs and collapsed
  if (newState.cantripsList && newState.cantripsList.length > 0) {
    newState.cantripsList.forEach((cantrip, idx) => {
      if (!cantrip.id) {
        cantrip.id = (Date.now() + idx + 100).toString();
      }
      if (!cantrip.hasOwnProperty('collapsed')) {
        cantrip.collapsed = false;
      }
    });
  }

  newState.id = sheet.id;
  newState.slug = sheet.slug;

  // Use window.characterName if newState.characterName is missing or empty
  if (!newState.characterName && typeof global.characterName !== 'undefined') {
    newState.characterName = global.characterName;
  }

  // Use window.is_2024 if available, otherwise use sheet.is_2024
  if (typeof global.is_2024 !== 'undefined') {
    newState.is_2024 = global.is_2024;
  } else if (sheet.is_2024 !== undefined) {
    newState.is_2024 = sheet.is_2024;
  }

  newState.readOnly = sheet.is_public && sheet.email === null;

  Object.assign(state, newState);

  // Restore UI-only state from localStorage
  var hideDiceRoller =
    localStorage.getItem('setting-hide-dice-roller') === 'true';
  state.diceMaximized =
    !hideDiceRoller && localStorage.getItem('dicePanelMaximized') === 'true';
}

/**
 * Used to update read-only sheets on a loop.
 *
 * @param {{sheet: Sheet}} payload
 */
export function updateState(payload) {
  var sheet = payload.sheet;
  // Start with a deep copy of the default state
  /** @type {AppState} */
  var newState = JSON.parse(JSON.stringify(defaultState));

  if (sheet.data) {
    // support older sheets with stringified data
    const sheetData =
      typeof sheet.data === 'string' ? JSON.parse(sheet.data) : sheet.data;
    // merge sheet data on top of defaults
    newState = Object.assign({}, newState, sheetData);
  }

  // normalize attacks
  if (newState.attacks && newState.attacks.length > 0) {
    newState.attacks = newState.attacks.map((attack, idx) => ({
      ...defaultAttack,
      ...attack,
      id: idx,
    }));
  }

  // normalize trackable fields
  if (newState.trackableFields && newState.trackableFields.length > 0) {
    newState.trackableFields = newState.trackableFields.map((field) => ({
      ...defaultTrackableField,
      ...field,
    }));
  }

  newState.id = sheet.id;
  newState.characterName = sheet.name;
  newState.readOnly = sheet.is_public && sheet.email === null;

  Object.assign(state, newState);

  // we need to let the quill editors know to update their contents
  global.sheetEvent.emit('quill-refresh');
}

/** @param {object} obj  */
function objectIsEmpty(obj) {
  for (let prop in obj) {
    if (prop === 'id') continue;
    if (!obj.hasOwnProperty(prop)) continue;
    // @ts-ignore
    if (obj[prop]) return false;
  }
  return true;
}
