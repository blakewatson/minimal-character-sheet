<template>
  <details
    open
    class="border-light-foreground dark:border-dark-foreground mb-4 border-t"
  >
    <summary class="section-label mb-2">{{ $t('Equipment') }}</summary>

    <div class="mb-4 flex items-center justify-between gap-4">
      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
        v-for="(c, i) in coins"
      >
        <label
          :for="'coin-' + i"
          class="text-center text-sm tracking-wider uppercase"
          >{{ $t(c.name) }}</label
        >
        <field
          :id="'coin-' + i"
          :read-only="readOnly"
          :value="c.amount"
          @update-value="updateAmount(i, $event)"
          class="text-center min-[500px]:text-xl sm:text-2xl"
          type="number"
        ></field>
      </div>
    </div>

    <quill-editor
      :initial-contents="equipmentText"
      :read-only="readOnly"
      @quill-text-change="updateEquipment"
    ></quill-editor>
  </details>
</template>

<script>
import { state, updateCoins, updateEquipment as storeUpdateEquipment } from '../store';
import Field from './Field.vue';
import QuillEditor from './QuillEditor.vue';

export default {
  name: 'Equipment',

  computed: {
    coins() { return state.coins; },
    equipmentText() { return state.equipmentText; },
    readOnly() { return state.readOnly; },
  },

  methods: {
    updateAmount(i, val) {
      updateCoins({ i, amount: val });
    },

    updateEquipment(val) {
      storeUpdateEquipment({ val });
    },
  },

  components: {
    'quill-editor': QuillEditor,
    field: Field,
  },
};
</script>
