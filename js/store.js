import Vue from 'vue';
import Vuex from 'vuex';
import levelData from './level-data';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        levelData: levelData,
        level: 5,
        characterName: 'Constantine',
        race: 'Half-elf',
        className: 'Druid',
        xp: 11000,
        alignment: 'LG',
        hp: 38,
        maxHp: 38,
        tempHp: 0,
        hitDie: '5d8',
        totalHitDie: 5,
        ac: 12,
        abilities: [
            { name: 'STR', score: 12 },
            { name: 'DEX', score: 10 },
            { name: 'CON', score: 10 },
            { name: 'INT', score: 10 },
            { name: 'WIS', score: 10 },
            { name: 'CHA', score: 10 }
        ],
        savingThrows: [
            { name: 'STR', proficient: false },
            { name: 'DEX', proficient: true },
            { name: 'CON', proficient: false },
            { name: 'INT', proficient: false },
            { name: 'WIS', proficient: true },
            { name: 'CHA', proficient: false }
        ],
        attacks: [{ id: 0, name: '', attackBonus: 0, damage: '' }],
        coins: [
            { name: 'cp', amount: 0 },
            { name: 'sp', amount: 0 },
            { name: 'ep', amount: 0 },
            { name: 'gp', amount: 5 },
            { name: 'pp', amount: 0 }
        ],
        equipmentText: {},
        proficienciesText: {},
        featuresText: {},
        backstoryText: {},
        treasureText: {},
        organizationsText: {},
        spClass: '',
        spAbility: 'WIS',
        spSave: '',
        spAttack: '',
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
            var row = state.levelData.find(data => state.level === data.lvl);
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
            var allowedFields = ['hp', 'maxHp', 'tempHp', 'hitDie', 'totalHitDie', 'ac'];
            var field = payload.field;
            if(!allowedFields.includes(field)) return;
            if(!state.hasOwnProperty(field)) return;
            state[field] = payload.val;
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
            state.attacks.splice(i, 0);
        },

        updateCoins(state, payload) {
            if(payload.i >= state.coins.length) return;
            if(typeof payload.amount !== 'number') return;
            Vue.set(state.coins, payload.i, payload.amount);
        },

        updateEquipment(state, payload) {
            state.equipmentText = payload.val;
        },

        updateTextField(state, payload) {
            if(!state.hasOwnProperty(payload.field)) return;
            state[payload.field] = payload.val;
        },

        updateSpellInfo(state, payload) {
            var allowedFields = ["spClass", "spAbility", "spSave", "spAttack"];
            var field = payload.field;
            if(!allowedFields.includes(field)) return;
            if(!state.hasOwnProperty(field)) return;
            state[field] = payload.val;
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