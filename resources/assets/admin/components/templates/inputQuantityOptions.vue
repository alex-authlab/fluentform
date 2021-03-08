<template>
    <withLabel :item="item">
        <template v-if="inputType == 'number'">

            <el-input
                    :type="item.attributes.type"
                    :value="item.attributes.value"
                    >
            </el-input>

        </template>
        <template v-else-if="inputType == 'select'">
            <select class="select el-input__inner">
                <option v-for="(option, index) in item.settings.advanced_options" :key="index">{{option.label}}</option>
            </select>
        </template>

    </withLabel>
</template>

<script type="text/babel">
import withLabel from './withLabel.vue';

export default {
    name: 'inputQuantityOptions',
    props: ['item'],
    components: {
        withLabel
    },
    data() {
      return {

      }
    },
    computed: {
        inputType() {
            return this.item.attributes.type;
        }
    }
}
</script>
