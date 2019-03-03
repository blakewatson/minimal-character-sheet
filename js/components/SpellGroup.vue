<template>
    <div>
        <div class="box box-lite clearfix">
            <span class="label float-left reverse">{{ level }}</span>
            <div class="spell-slots">
                <span class="label label-inline">Slots:</span>
                <field :value="totalSlots" type="number" class="spell-slots-total" min="0" @update-value="totalSlots = $event"></field>
                <span class="label label-inline">Expended:</span>
                <field :value="expendedSlots" type="number" class="spell-slots-expended" min="0" :max="totalSlots" @update-value="expendedSlots = $event"></field>
            </div>
        </div>
        <spell-list></spell-list>
    </div>
</template>

<script>
import SpellList from './SpellList';
import Field from './Field';

export default {
    name: 'SpellGroup',

    props: ['level'],

    data() {
        return {
            totalSlots: 0,
            expendedSlots: 0
        };
    },

    components: {
        'spell-list': SpellList,
        'field': Field
    }
}
</script>