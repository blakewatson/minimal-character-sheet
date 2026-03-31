import Field from './components/Field.vue';

var listMixin = {
  data() {
    return {
      items: [''],
    };
  },

  methods: {
    updateItem(i, value) {
      this.items[i] = value;

      var itemsLength = this.items.length;
      if (this.items[itemsLength - 1] !== '') {
        this.items.push('');
      }
    },
  },

  components: {
    field: Field,
  },
};

export { listMixin };
