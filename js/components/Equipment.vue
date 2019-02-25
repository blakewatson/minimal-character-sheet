<template>
    <section>
        <p class="label centered">Equipment</p>

        <div class="row vert-after">
            <div class="box box-lite" v-for="(c, i) in coins">
                <span class="label centered">{{ c.name }}</span>
                <field class="centered huge padded" :value="c.amount" @update-value="updateAmount(i, $event)"></field>
            </div>
        </div>

        <quill-editor></quill-editor>
    </section>
</template>

<script>
import Vue from 'vue';
import QuillEditor from './QuillEditor';
import Field from './Field';

export default {
    name: 'Equipment',

    data() {
        return {
            coins: [
                { name: 'cp', amount: 0 },
                { name: 'sp', amount: 0 },
                { name: 'ep', amount: 0 },
                { name: 'gp', amount: 5 },
                { name: 'pp', amount: 0 }
            ]
        };
    },

    methods: {
        updateAmount(i, value) {
            Vue.set(this.coins[i], 'amount', value);
        }
    },

    components: {
        'quill-editor': QuillEditor,
        'field': Field
    }
}
</script>
