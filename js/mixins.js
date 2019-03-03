import Vue from 'vue';
import Field from './components/Field';

var listMixin =  {
    data() {
        return {
            items: ['']
        };
    },

    methods: {
        updateItem(i, value) {
            Vue.set(this.items, i, value);

            var itemsLength = this.items.length;
            if(this.items[itemsLength - 1] !== '') {
                this.items.push('');
            }
        }
    },

    components: {
        'field': Field
    }
}

export { listMixin };