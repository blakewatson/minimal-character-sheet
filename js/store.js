import Vue from 'vue';
import Vuex from 'vuex';
import levelData from './level-data';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        id: '',
        slug: '',
        readOnly: false,
        levelData: levelData,
        level: 1,
        characterName: '',
        race: '',
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
        inspiration: false,
        deathSaves: {
            successes: [false, false, false],
            failures: [false, false, false],
        },
        abilities: [
            { name: 'STR', score: 10 },
            { name: 'DEX', score: 10 },
            { name: 'CON', score: 10 },
            { name: 'INT', score: 10 },
            { name: 'WIS', score: 10 },
            { name: 'CHA', score: 10 }
        ],
        savingThrows: [
            { name: 'STR', proficient: false },
            { name: 'DEX', proficient: false },
            { name: 'CON', proficient: false },
            { name: 'INT', proficient: false },
            { name: 'WIS', proficient: false },
            { name: 'CHA', proficient: false }
        ],
        skills: [
            { name: 'Acrobatics', ability: 'DEX', proficient: false, doubleProficient: false },
            { name: 'Animal Handling', ability: 'WIS', proficient: false, doubleProficient: false },
            { name: 'Arcana', ability: 'INT', proficient: false, doubleProficient: false },
            { name: 'Athletics', ability: 'STR', proficient: false, doubleProficient: false },
            { name: 'Deception', ability: 'CHA', proficient: false, doubleProficient: false },
            { name: 'History', ability: 'INT', proficient: false, doubleProficient: false },
            { name: 'Insight', ability: 'WIS', proficient: false, doubleProficient: false },
            { name: 'Intimidation', ability: 'CHA', proficient: false, doubleProficient: false },
            { name: 'Investigation', ability: 'INT', proficient: false, doubleProficient: false },
            { name: 'Medicine', ability: 'WIS', proficient: false, doubleProficient: false },
            { name: 'Nature', ability: 'INT', proficient: false, doubleProficient: false },
            { name: 'Perception', ability: 'WIS', proficient: false, doubleProficient: false },
            { name: 'Performance', ability: 'CHA', proficient: false, doubleProficient: false },
            { name: 'Persuasion', ability: 'CHA', proficient: false, doubleProficient: false },
            { name: 'Religion', ability: 'INT', proficient: false, doubleProficient: false },
            { name: 'Sleight of Hand', ability: 'DEX', proficient: false, doubleProficient: false },
            { name: 'Stealth', ability: 'DEX', proficient: false, doubleProficient: false },
            { name: 'Survival', ability: 'WIS', proficient: false, doubleProficient: false }
        ],
        attacks: [],
        coins: [
            { name: 'cp', amount: 0 },
            { name: 'sp', amount: 0 },
            { name: 'ep', amount: 0 },
            { name: 'gp', amount: 0 },
            { name: 'pp', amount: 0 }
        ],
        equipmentText: {},
        proficienciesText: {},
        featuresText: {},
        personalityText: {},
        backstoryText: {},
        treasureText: {},
        organizationsText: {},
        notesText: {},
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
        lvl9Spells: { slots: 0, expended: 0, spells: [] }
    },

    getters: {
        modifiers(state) {
            return state.abilities.map(a => {
                return {
                    ability: a.name,
                    val: Math.floor(parseInt(a.score) / 2 - 5)
                };
            });
        },

        proficiencyBonus(state) {
            var level = state.level;
            var row = state.levelData.find(data => level === data.lvl);
            if(!row) return 2;
            return row.proficiency;
        }

        /* getLevelByXp(state) {
            return xp => {
                var row = state.levelData.find((data, i) => {
                    if(i + 1 === state.levelData.length) return true;
                    if(xp > data.xp && xp < state.levelData[i+1].xp) return true;
                });

                if(!row) return 1;
                return row.lvl;
            };
        },

        getXpByLevel(state) {} */
    },

    mutations: {
        replaceState(state, payload) {
            if(!payload.state) return;
            if(typeof payload.state !== 'object') return;
            for(let prop in payload.state) {
                if(!payload.state.hasOwnProperty(prop)) continue;
                state[prop] = payload.state[prop];
            } 
        },

        updateAbilityScore(state, payload) {
            state.abilities.forEach((ability, i) => {
                if(ability.name === payload.name) {
                    Vue.set(state.abilities[i], 'score', payload.score);
                }
            });
        },

        updateLevel(state, payload) {
            state.level = payload.level;
        },

        updateBio(state, payload) {
            var allowedFields = ["characterName", "race", "className", "xp", "alignment"];
            var field = payload.field;
            if(!allowedFields.includes(field)) return;
            if(!state.hasOwnProperty(field)) return;
            state[field] = payload.val;
        },

        updateVitals(state, payload) {
            var allowedFields = ['hp', 'maxHp', 'tempHp', 'hitDie', 'totalHitDie', 'ac', 'speed'];
            var field = payload.field;
            if(!allowedFields.includes(field)) return;
            if(!state.hasOwnProperty(field)) return;
            state[field] = payload.val;
        },

        updateDeathSaves(state, payload) {
            var key = payload.key; // 'successes' or 'failures'
            var i = payload.i; // 0, 1, 2
            var val = payload.val; // boolean
            var deathSaves = { ...state.deathSaves };
            deathSaves[key][i] = val;
            state.deathSaves = deathSaves;
        },

        updateInitiative(state, payload) {
            state.initiative = payload;
        },
        
        updateInspiration(state, payload) {
            state.inspiration = payload;
        },

        updateSkillProficiency(state, payload) {
            if(payload.i >= state.skills.links) return;
            Vue.set(state.skills[payload.i], 'proficient', payload.proficient);
            Vue.set(state.skills[payload.i], 'doubleProficient', payload.doubleProficient);
        },

        updateSavingThrow(state, payload) {
            var i = state.savingThrows.findIndex(savingThrow => payload.name === savingThrow.name);
            Vue.set(state.savingThrows[i], 'proficient', payload.proficient);
        },

        updateAttacks(state, payload) {
            if(payload.i >= state.attacks.length) return;
            if(!state.attacks[payload.i].hasOwnProperty(payload.field)) return;
            Vue.set(state.attacks[payload.i], payload.field, payload.val);
        },

        addAttack(state, payload) {
            var attack = { id: Date.now(), name: '', attackBonus: 0, damage: '' };
            state.attacks.push(attack);
        },

        deleteAttack(state, payload) {
            if(payload.i >= state.attacks.length) return;
            state.attacks.splice(payload.i, 1);
        },

        updateCoins(state, payload) {
            if(payload.i >= state.coins.length) return;
            Vue.set(state.coins[payload.i], 'amount', payload.amount);
        },

        updateEquipment(state, payload) {
            state.equipmentText = payload.val;
        },

        updateTextField(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            state[payload.field] = payload.val;
        },

        addToListField(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            state[payload.field].push({ val: payload.val, id: Math.random().toString() });
        },

        updateListField(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            Vue.set(state[payload.field][payload.i], 'val', payload.val);
        },

        deleteFromListField(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            if(payload.i >= state[payload.field].length) return;
            state[payload.field].splice(payload.i, 1);
        },

        updateSpellInfo(state, payload) {
            var allowedFields = ["spClass", "spAbility", "spSave", "spAttack"];
            var field = payload.field;
            if(!allowedFields.includes(field)) return;
            if(!state.hasOwnProperty(field)) return;
            state[field] = payload.val;
        },

        addSpell(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            state[payload.field].spells.push(payload.item);
        },

        updateSpellName(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            Vue.set(state[payload.field].spells[payload.i], 'name', payload.name);
        },

        updateSpellPrepared(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            Vue.set(state[payload.field].spells[payload.i], 'prepared', payload.prepared);
        },

        deleteSpell(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            state[payload.field].spells.splice(payload.i, 1);
        },

        updateSpellSlots(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            Vue.set(state[payload.field], 'slots', payload.val);
        },

        updateExpendedSlots(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            Vue.set(state[payload.field], 'expended', payload.val);
        }
    },

    actions: {
        getJSON({ state }) {
            return new Promise((resolve, reject) => {
                try {
                    var json = JSON.stringify(state);
                    resolve(json);
                } catch(err) {
                    reject(err);
                }
            });
        },

        initializeState({ commit }, payload) {
            var sheet = JSON.parse(payload.sheet);
            var state = {};
            
            if(sheet.data) {
                // maintain defaults for newly added fields that might not be in the json data
                state = Object.assign({}, state, JSON.parse(sheet.data));
            }

            // default initiative to dex modifier
            if(!state.initiative) {
                const dex = state.abilities.find(ability => ability.name === 'DEX');
                state.initiative = Math.floor(parseInt(dex.score) / 2 - 5);
            }
            
            
            state.id = sheet.id;
            state.slug = sheet.slug;
            state.characterName = sheet.name;
            state.readOnly = sheet.is_public && sheet.email === null;
            
            commit('replaceState', { state });
        },
        
        updateState({ commit }, payload) {
            var sheet = payload.sheet;
            var state = {};
            
            if(sheet.data) {
                // maintain defaults for newly added fields that might not be in the json data
                state = Object.assign({}, state, JSON.parse(sheet.data));
            }
            
            state.id = sheet.id;
            state.characterName = sheet.name;
            state.readOnly = sheet.is_public && sheet.email === null;
            
            commit('replaceState', { state });
            
            // we need to let the quill editors know to update their contents
            window.sheetEvent.$emit('quill-refresh');
        }
    }
});

function objectIsEmpty(obj) {
    for(let prop in obj) {
        if(prop === 'id') continue;
        if(!obj.hasOwnProperty(prop)) continue;
        if(obj[prop]) return false;
    }
    return true;
}