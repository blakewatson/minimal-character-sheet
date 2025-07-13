<template>
    <details open class="section">
        <summary class="label centered">Equipment</summary>

        <div class="row vert-after">
            <div class="box box-lite" v-for="(c, i) in coins">
                <label :for="'coin-' + i" class="label centered">{{ c.name }}</label>
                <field :id="'coin-' + i" type="number" classNames="centered huge padded" :value="c.amount" :read-only="readOnly" @update-value="updateAmount(i, $event)"></field>
            </div>
        </div>

        <quill-editor :initial-contents="equipmentText" :read-only="readOnly" @quill-text-change="updateEquipment"></quill-editor>
    </details>
</template>

<script>
import { mapState } from 'vuex';
import QuillEditor from './QuillEditor';
import Field from './Field';

export default {
    name: 'Equipment',

    computed: {
        ...mapState(['coins', 'equipmentText', 'readOnly'])
    },

    methods: {
        updateAmount(i, val) {
            this.$store.commit('updateCoins', { i, amount: val });
        },

        updateEquipment(val) {
            this.$store.commit('updateEquipment', { val });
        }
    },

    components: {
        'quill-editor': QuillEditor,
        'field': Field
    }
}
</script>
