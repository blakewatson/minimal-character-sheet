<template>
    <nav class="sheet-nav">
        <ul>
            <li class="back-button">
                <a href="/dashboard">
                    <img src="/images/arrow-left.svg" alt="Dashboard">
                </a>
            </li>
            <li :class="{ active: view === 'main' }"><button @click="updateView('main')">Main</button></li>
            <li :class="{ active: view === 'spells' }"><button @click="updateView('spells')">Spells</button></li>
            <li :class="{ active: view === 'details' }"><button @click="updateView('details')">Details</button></li>
            <li :class="{ active: view === 'notes' }"><button @click="updateView('notes')">Notes</button></li>
            <li class="delete-character-button" v-if="!readOnly">
                <button @click="deleteCharacter">
                    <img src="/images/trash-alt.svg" alt="Dashboard">
                </button>
            </li>
        </ul>
    </nav>
</template>

<script>
import { mapState } from 'vuex';

export default {
    name: 'Tabs',

    props: ['view'],

    computed: {
        ...mapState(['readOnly']),
        
        sheetSlug() {
            return this.$store.state.slug;
        }
    },

    methods: {
        updateView(view) {
            this.$emit('update-view', view);
        },

        deleteCharacter() {
            var csrf = document.querySelector('#csrf').value;
            var areYouSure = confirm('Are you sure you want to *permanantly* delete this character sheet?');
            if(!areYouSure) return;

            fetch(`/sheet/${this.sheetSlug}`, {
                method: 'delete',
                headers: {
                    'X-Ajax-Csrf': csrf
                }
            })
            .then(r => r.json())
            .then(r => {
                window.location = '/dashboard';
            });
        }
    }
}
</script>
