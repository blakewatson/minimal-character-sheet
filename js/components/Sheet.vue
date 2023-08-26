<template>
    <div id="sheet" class="sheet">
        
        <tabs :view="view" @update-view="view = $event"></tabs>

        <bio></bio>

        <div class="page" v-show="view === 'main'">
            <proficiency></proficiency>
            
            <abilities></abilities>
            
            <skills></skills>

            <attacks></attacks>

            <equipment></equipment>

            <text-section title="Other Proficiencies & Languages" field="proficienciesText" :read-only="readOnly"></text-section>

            <text-section title="Feature & Traits" field="featuresText" :read-only="readOnly"></text-section>
        </div>

        <div class="page" v-show="view === 'spells'">
            <spells></spells>
        </div>

        <div class="page" v-show="view === 'details'">
            <text-section title="Traits, Ideals, Bonds, & Flaws" field="personalityText" :read-only="readOnly"></text-section>

            <text-section title="Character Backstory" field="backstoryText" :read-only="readOnly"></text-section>
    
            <text-section title="Treasure" field="treasureText" :read-only="readOnly"></text-section>
    
            <text-section title="Allies & Organizations" field="organizationsText" :read-only="readOnly"></text-section>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Tabs from './Tabs';
import Bio from './Bio';
import Abilities from './Abilities';
import Skills from './Skills';
import Proficiency from './Proficiency';
import Attacks from './Attacks';
import Equipment from './Equipment';
import Spells from './Spells';
import TextSection from './TextSection';

export default {
    name: 'Sheet',

    data() {
        return {
            view: 'main',
            autosaveTimer: null,
            isPublic: false
        };
    },
    
    computed: {
        ...mapState(['readOnly'])
    },

	methods: {
		autosaveLoop() {
            if(this.isPublic) {
                return;
            }
            
            // trigger a quick autosave upon every store mutation
            this.$store.subscribe((mutation, state) => {
                window.sheetEvent.$emit('autosave', 1);
            });
	
			// when this event fires, schedule a save
			window.sheetEvent.$on('autosave', (seconds = 5) => {
				// convert to milliseconds
				var milliseconds = seconds * 1000;
	
				// reset the timer, if running
				if(this.autosaveTimer !== null) {
					clearTimeout(this.autosaveTimer);
					this.autosaveTimer = null;
				}
	
				// run the autosave after the specified delay
				this.autosaveTimer = setTimeout(() => {
					console.log('save the character sheet');
					this.saveSheetState();
					// go ahead and schedule another autosave
					// window.sheetEvent.$emit('autosave');
				}, milliseconds);
			});
	
			// go ahead and trigger the first autosave
			window.sheetEvent.$emit('autosave');
		},

		saveSheetState() {
            if(this.isPublic) {
                return;
            }
            
			this.$store.dispatch('getJSON').then(jsonState => {
                var sheetSlug = document.querySelector('#sheet-slug').value;
                var csrf = document.querySelector('#csrf').value;
                var formBody = new URLSearchParams();
                
                formBody.set('name', this.$store.state.characterName);
                formBody.set('data', jsonState);
                formBody = formBody.toString();

                fetch(`/sheet/${sheetSlug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-AJAX-CSRF': csrf
                    },
                    body: formBody
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        document.querySelector('#csrf').value = data.csrf;
                    }
                });
			}).catch(reason => console.error(reason));
		},
        
        refreshLoop() {
            var sheetSlug = document.querySelector('#sheet-slug').value;
            
            fetch(`/sheet-data/${sheetSlug}`)
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    this.$store.dispatch('updateState', { sheet: data.sheet })
                    .catch(reason => console.log(reason));
                }
            })
            .catch(reason => console.error(reason));
        }
	},

    components: {
        'tabs': Tabs,
        'bio': Bio,
        'abilities': Abilities,
        'skills': Skills,
        'proficiency': Proficiency,
        'attacks': Attacks,
        'equipment': Equipment,
        'spells': Spells,
        'text-section': TextSection
    },

	mounted() {
        const parsedSheet = JSON.parse(sheet);
        if(parsedSheet.is_public && parsedSheet.email === null) {
            this.isPublic = true;
            setInterval(() => this.refreshLoop(), 2000);
        }
        
        if(!this.isPublic) {
    		this.autosaveLoop();
        }
    },
    
    created() {
        // initialize state with the "sheet" global
        this.$store.dispatch('initializeState', { sheet })
        .catch(reason => console.log(reason));
    }
}
</script>
