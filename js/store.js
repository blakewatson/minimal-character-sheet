import Vue from 'vue';
import Vuex from 'vuex';
import levelData from './level-data';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        levelData: levelData,
        level: 5,
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
        ]
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
        }
    }
});