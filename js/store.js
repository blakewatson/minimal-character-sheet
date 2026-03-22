import { reactive, computed } from 'vue';
import levelData from './level-data';

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
  hp: 0,
  maxHp: 0,
  tempHp: 0,
  hitDie: '1d8',
  totalHitDie: 1,
  ac: 10,
  speed: 25,
  initiative: 0,
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
    { name: 'STR', proficient: false },
    { name: 'DEX', proficient: false },
    { name: 'CON', proficient: false },
    { name: 'INT', proficient: false },
    { name: 'WIS', proficient: false },
    { name: 'CHA', proficient: false },
  ],
  skills: [
    {
      name: 'Acrobatics',
      ability: 'DEX',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Animal Handling',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Arcana',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Athletics',
      ability: 'STR',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Deception',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'History',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Insight',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Intimidation',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Investigation',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Medicine',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Nature',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Perception',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Performance',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Persuasion',
      ability: 'CHA',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Religion',
      ability: 'INT',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Sleight of Hand',
      ability: 'DEX',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Stealth',
      ability: 'DEX',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
    },
    {
      name: 'Survival',
      ability: 'WIS',
      proficient: false,
      doubleProficient: false,
      modifierOverride: null,
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
  passivePerceptionOverride: null,
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

export const state = reactive(JSON.parse(JSON.stringify(defaultState)));

// Computed refs (replace Vuex getters)
export const modifiers = computed(() => {
  return state.abilities.map((a) => {
    return {
      ability: a.name,
      val: Math.floor(parseInt(a.score) / 2 - 5),
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

// Mutation functions (replace Vuex mutations)

export function updateAbilityScore(payload) {
  state.abilities.forEach((ability, i) => {
    if (ability.name === payload.name) {
      state.abilities[i].score = payload.score;
    }
  });
}

export function updateLevel(payload) {
  state.level = payload.level;
}

export function updateBio(payload) {
  var allowedFields = [
    'characterName',
    'race',
    'background',
    'className',
    'xp',
    'alignment',
  ];
  var field = payload.field;
  if (!allowedFields.includes(field)) return;
  if (!state.hasOwnProperty(field)) return;
  state[field] = payload.val;
}

export function updateVitals(payload) {
  var allowedFields = [
    'hp',
    'maxHp',
    'tempHp',
    'hitDie',
    'totalHitDie',
    'ac',
    'speed',
    'conditions',
    'concentration',
  ];
  var field = payload.field;
  if (!allowedFields.includes(field)) return;
  if (!state.hasOwnProperty(field)) return;
  state[field] = payload.val;
}

export function updateDeathSaves(payload) {
  var key = payload.key; // 'successes' or 'failures'
  var i = payload.i; // 0, 1, 2
  var val = payload.val; // boolean
  var deathSaves = { ...state.deathSaves };
  deathSaves[key][i] = val;
  state.deathSaves = deathSaves;
}

export function updateInitiative(payload) {
  state.initiative = payload;
}

export function updateInspiration(payload) {
  state.inspiration = payload;
}

export function updateShortRests(payload) {
  state.shortRests = payload;
}

export function updateProficiencyOverride(payload) {
  state.proficiencyOverride = payload;
}

export function updateSkillProficiency(payload) {
  if (payload.i >= state.skills.links) return;
  state.skills[payload.i].proficient = payload.proficient;
  state.skills[payload.i].doubleProficient = payload.doubleProficient;
}

export function updateSkillModifierOverride(payload) {
  var skill = state.skills.find(
    (skill) => skill.name === payload.skillName,
  );
  if (!skill) return;

  state.skills = state.skills.map((skill) => {
    if (skill.name === payload.skillName) {
      console.log('setting modifier override', payload.modifierOverride);
      skill.modifierOverride = payload.modifierOverride;
    }
    return skill;
  });
}

export function updatePassivePerceptionOverride(payload) {
  state.passivePerceptionOverride = payload;
}

export function updateSavingThrow(payload) {
  var i = state.savingThrows.findIndex(
    (savingThrow) => payload.name === savingThrow.name,
  );
  state.savingThrows[i].proficient = payload.proficient;
}

export function updateAttacks(payload) {
  var attack = state.attacks.find((attack) => attack.id === payload.id);
  if (!attack) return;

  state.attacks = state.attacks.map((a) => {
    if (a.id === payload.id) {
      a[payload.field] = payload.val;
    }
    return a;
  });
}

export function addAttack(payload) {
  var attack = {
    id: Date.now(),
    name: '',
    attackBonus: 0,
    damage: '',
    weaponNotes: '',
  };
  state.attacks.push(attack);
}

export function deleteAttack(payload) {
  state.attacks = state.attacks.filter((a) => a.id !== payload.id);
}

export function sortAttacks(payload) {
  var id = payload.id;
  var direction = payload.direction;
  var curIndex = state.attacks.findIndex((a) => a.id === id);

  if (curIndex === -1) return;

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
}

export function updateTrackableField(payload) {
  var field = state.trackableFields.find(
    (field) => field.id === payload.id,
  );
  if (!field) return;

  state.trackableFields = state.trackableFields.map((f) => {
    if (f.id === payload.id) {
      f[payload.field] = payload.val;
    }
    return f;
  });
}

export function addTrackableField(payload) {
  var field = {
    id: Date.now(),
    name: '',
    used: 0,
    max: 0,
    notes: '',
  };
  state.trackableFields.push(field);
}

export function deleteTrackableField(payload) {
  state.trackableFields = state.trackableFields.filter(
    (f) => f.id !== payload.id,
  );
}

export function sortTrackableField(payload) {
  var id = payload.id;
  var direction = payload.direction;
  var curIndex = state.trackableFields.findIndex((f) => f.id === id);

  if (curIndex === -1) return;

  if (direction === 'up') {
    if (curIndex === 0) return;
    var deletedFields = state.trackableFields.splice(curIndex, 1);
    var fieldToMove = deletedFields[0];
    state.trackableFields.splice(curIndex - 1, 0, fieldToMove);
    return;
  }

  if (direction === 'down') {
    if (curIndex === state.trackableFields.length - 1) return;
    var deletedFields = state.trackableFields.splice(curIndex, 1);
    var fieldToMove = deletedFields[0];
    state.trackableFields.splice(curIndex + 1, 0, fieldToMove);
    return;
  }
}

export function updateCoins(payload) {
  if (payload.i >= state.coins.length) return;
  state.coins[payload.i].amount = payload.amount;
}

export function updateEquipment(payload) {
  state.equipmentText = payload.val;
}

export function updateTextField(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field] = payload.val;
}

export function addToListField(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].push({
    val: payload.val,
    id: Date.now().toString(),
  });
}

export function updateListField(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field][payload.i].val = payload.val;
  state[payload.field][payload.i].collapsed = payload.collapsed;
}

export function deleteFromListField(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  if (payload.i >= state[payload.field].length) return;
  state[payload.field].splice(payload.i, 1);
}

export function sortListField(payload) {
  var field = payload.field;
  var direction = payload.direction;
  var curIndex = state[field].findIndex((item) => item.id === payload.id);

  if (curIndex === -1) return;

  if (direction === 'up') {
    if (curIndex === 0) return;
    var deletedItems = state[field].splice(curIndex, 1);
    var itemToMove = deletedItems[0];
    state[field].splice(curIndex - 1, 0, itemToMove);
    return;
  }

  if (direction === 'down') {
    if (curIndex === state[field].length - 1) return;
    var deletedItems = state[field].splice(curIndex, 1);
    var itemToMove = deletedItems[0];
    state[field].splice(curIndex + 1, 0, itemToMove);
    return;
  }
}

export function updateSpellInfo(payload) {
  var allowedFields = ['spClass', 'spAbility', 'spSave', 'spAttack'];
  var field = payload.field;
  if (!allowedFields.includes(field)) return;
  if (!state.hasOwnProperty(field)) return;
  state[field] = payload.val;
}

export function addSpell(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].spells.push(payload.item);
}

export function updateSpellName(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].spells[payload.i].name = payload.name;
}

export function updateSpellPrepared(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].spells[payload.i].prepared = payload.prepared;
}

export function updateSpellCollapsed(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].spells[payload.i].collapsed = payload.collapsed;
}

export function deleteSpell(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].spells.splice(payload.i, 1);
}

export function updateSpellSlots(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].slots = payload.val;
}

export function updateExpendedSlots(payload) {
  if (!state.hasOwnProperty(payload.field)) return;
  state[payload.field].expended = payload.val;
}

export function sortSpells(payload) {
  var field = payload.field;
  var direction = payload.direction;
  var curIndex = state[field].spells.findIndex(
    (spell) => spell.id === payload.id,
  );

  if (curIndex === -1) return;

  if (direction === 'up') {
    if (curIndex === 0) return;
    var deletedSpells = state[field].spells.splice(curIndex, 1);
    var spellToMove = deletedSpells[0];
    state[field].spells.splice(curIndex - 1, 0, spellToMove);
    return;
  }

  if (direction === 'down') {
    if (curIndex === state[field].spells.length - 1) return;
    var deletedSpells = state[field].spells.splice(curIndex, 1);
    var spellToMove = deletedSpells[0];
    state[field].spells.splice(curIndex + 1, 0, spellToMove);
    return;
  }
}

// Action functions (replace Vuex actions)

export function getJSON() {
  return JSON.stringify(state);
}

export function initializeState(payload) {
  var sheet = JSON.parse(payload.sheet);
  // Start with a deep copy of the default state
  var newState = JSON.parse(JSON.stringify(defaultState));

  if (sheet.data) {
    // merge sheet data on top of defaults
    newState = Object.assign({}, newState, sheet.data);
  }

  // default initiative to dex modifier
  if (!newState.initiative) {
    const dex = newState.abilities.find((ability) => ability.name === 'DEX');
    newState.initiative = Math.floor(parseInt(dex.score) / 2 - 5);
  }

  // ensure existing attacks have weaponNotes field
  if (newState.attacks && newState.attacks.length > 0) {
    newState.attacks.forEach((attack) => {
      if (!attack.hasOwnProperty('weaponNotes')) {
        attack.weaponNotes = '';
      }
    });
  }

  // ensure existing trackable fields have all required properties
  if (newState.trackableFields && newState.trackableFields.length > 0) {
    newState.trackableFields.forEach((field) => {
      if (!field.hasOwnProperty('name')) field.name = '';
      if (!field.hasOwnProperty('used')) field.used = 0;
      if (!field.hasOwnProperty('max')) field.max = 0;
      if (!field.hasOwnProperty('notes')) field.notes = '';
    });
  }

  // Assign stable IDs and normalize collapsed for all spell levels (D-10, D-11)
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
  if (
    !newState.characterName &&
    typeof window.characterName !== 'undefined'
  ) {
    newState.characterName = window.characterName;
  }

  // Use window.is_2024 if available, otherwise use sheet.is_2024
  if (typeof window.is_2024 !== 'undefined') {
    newState.is_2024 = window.is_2024;
  } else if (sheet.is_2024 !== undefined) {
    newState.is_2024 = sheet.is_2024;
  }

  newState.readOnly = sheet.is_public && sheet.email === null;

  Object.assign(state, newState);
}

export function updateState(payload) {
  var sheet = payload.sheet;
  // Start with a deep copy of the default state
  var newState = JSON.parse(JSON.stringify(defaultState));

  if (sheet.data) {
    // support older sheets with stringified data
    const sheetData =
      typeof sheet.data === 'string' ? JSON.parse(sheet.data) : sheet.data;
    // merge sheet data on top of defaults
    newState = Object.assign({}, newState, sheetData);
  }

  // ensure existing attacks have weaponNotes field
  if (newState.attacks && newState.attacks.length > 0) {
    newState.attacks.forEach((attack) => {
      if (!attack.hasOwnProperty('weaponNotes')) {
        attack.weaponNotes = '';
      }
    });
  }

  // ensure existing trackable fields have all required properties
  if (newState.trackableFields && newState.trackableFields.length > 0) {
    newState.trackableFields.forEach((field) => {
      if (!field.hasOwnProperty('name')) field.name = '';
      if (!field.hasOwnProperty('used')) field.used = 0;
      if (!field.hasOwnProperty('max')) field.max = 0;
      if (!field.hasOwnProperty('notes')) field.notes = '';
    });
  }

  newState.id = sheet.id;
  newState.characterName = sheet.name;
  newState.readOnly = sheet.is_public && sheet.email === null;

  Object.assign(state, newState);

  // we need to let the quill editors know to update their contents
  window.sheetEvent.emit('quill-refresh');
}

function objectIsEmpty(obj) {
  for (let prop in obj) {
    if (prop === 'id') continue;
    if (!obj.hasOwnProperty(prop)) continue;
    if (obj[prop]) return false;
  }
  return true;
}

