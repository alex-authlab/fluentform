<template>
    <div class="ff_payment_item_wrapper">
        <el-form-item>
            <elLabel
                    slot="label"
                    label="Quantity Display Type"
                    helpText="Select which quantity type you want for your payment item. Please provide valid number only"
            />

            <el-radio-group
                    @change="editItem.attributes.value = ''"
                    v-model="editItem.attributes.type"
                    size="small"
            >
                <el-radio-button label="number">Number </el-radio-button>
                <el-radio-button label="select">Select Fields</el-radio-button>
            </el-radio-group>
        </el-form-item>

        <el-form-item  v-if="editItem.attributes.type == 'select'">
            <div class="clearfix">

                <elLabel slot="label" :label="listItem.label" :helpText="listItem.help_text" />
            </div>

            <vddl-list
                    :drop="handleDrop"
                    v-if="optionsToRender.length"
                    class="vddl-list__handle"
                    :list="editItem.settings.advanced_options"
                    :horizontal="false"
            >
                <vddl-draggable
                        :moved="handleMoved"
                        class="optionsToRender ff_t"
                        v-for="(option, index) in editItem.settings.advanced_options"
                        :key="option.id"
                        :draggable="option"
                        :index="index"
                        :wrapper="editItem.settings.advanced_options"
                        effect-allowed="move"
                >
                    <vddl-nodrag class="nodrag ff_tr">
                        <div class="checkbox">
                            <input
                                    ref="defaultOptions"
                                    class="form-control"
                                    :type="optionsType"
                                    name="fluentform__default-option"
                                    :value="option.value"
                                    :checked="isChecked(option.value)"
                                    @change="updateDefaultOption(option)"
                            />
                        </div>

                        <vddl-handle :handle-left="20" :handle-top="20"class="handle"></vddl-handle>


                        <div>
                            <el-input
                                    placeholder="label"
                                    v-model="option.label"
                                    @input="updateValue(option)"
                            />
                        </div>

                        <div>
                            <el-input
                                    min="0"
                                    step="1"
                                    type="number"
                                    placeholder="Quantity"
                                    v-model="option.value"
                            />
                        </div>

                        <div class="action-btn">
                            <i @click="increase(index)" class="icon icon-plus-circle"></i>
                            <i @click="decrease(index)" class="icon icon-minus-circle"></i>
                        </div>
                    </vddl-nodrag>

                </vddl-draggable>
            </vddl-list>
            <el-button
                    type="warning"
                    size="mini"
                    :disabled="!editItem.attributes.value"
                    @click.prevent="clear"
            >Clear Selection</el-button>
        </el-form-item>
        <div v-else>

        </div>



        <el-form-item v-if="editItem.attributes.type == 'select'" label="Placeholder">
            <el-input placeholder="Placeholder" v-model="editItem.settings.placeholder" />
        </el-form-item>

    </div>

</template>

<script type="text/babel">
    import elLabel from '../../includes/el-label.vue'
    import each from 'lodash/each';
    import PhotoWidget from '../../../../common/PhotoUploader'

    export default {
        name: 'quantity-options',
        props: ['editItem', 'listItem'],
        components: {
            elLabel,
            PhotoWidget
        },
        data() {
            return {
                valuesVisible: false,
                optionsToRender: [],
                bulkEditVisible: false,
                value_key_pair_text: '',
                has_pro: !!window.FluentFormApp.hasPro,
                pro_mock: false
            }
        },
        computed: {
            optionsType() {
                let item = this.editItem;
                let attributes = item.attributes;
                let determiner = attributes.type || (attributes.multiple && 'multiselect') || item.element;

                switch (determiner) {
                    case 'multiselect':
                    case 'checkbox':
                        return 'checkbox'
                        break;
                    case 'select':
                    case 'radio':
                        return 'radio'
                        break;
                    default:
                        return 'radio'
                }
            },
            hasImageSupport() {
                return this.editItem.element != 'select';
            }
        },
        methods: {
            handleDrop(data) {
                const {index, list, item} = data;
                item.id = new Date().getTime();
                list.splice(index, 0, item);
            },
            handleMoved(item) {
                const {index, list} = item;
                list.splice(index, 1);
            },

            isChecked(optVal) {
                if (typeof this.editItem.attributes.value != 'number') {
                    return this.editItem.attributes.value.includes(optVal);
                }
            },

            increase(index) {
                let options = this.editItem.settings.advanced_options;
                let key = options.length + 1;
                let optionStr = `Quantity ${key}`;

                let newOpt = {
                    label: optionStr,
                    value: key
                };

                options.splice(index + 1, 0, newOpt);
            },

            decrease(index) {
                let options = this.editItem.settings.advanced_options;
                if (options.length > 1) {
                    options.splice(index, 1);
                } else {
                    this.$notify.error({
                                           message: 'You have to have at least one option.',
                                           offset: 30
                                       });
                }
            },

            clear() {
                let attributes = this.editItem.attributes;
                if (attributes.type == 'checkbox' || attributes.multiple) {
                    attributes.value = [];
                } else {
                    attributes.value = '';
                }
                this.$refs.defaultOptions.map(el => el.checked = false);
            },

            updateDefaultOption(option) {
                let attributes = this.editItem.attributes;
                if (event.target.checked) {
                    attributes.value = option.value;
                } else {
                    attributes.value = '';
                }
            },

            createOptionsToRender() {
                this.optionsToRender = this.editItem.settings.advanced_options;
            },

        },
        mounted() {
            this.createOptionsToRender();
        }
    };
</script>

<style lang="scss">
    .optionsToRender {
        margin: 7px 0;
    }

    .action-btn {
        display: inline-block;
        min-width: 32px;

        .icon {
            vertical-align: middle;
            cursor: pointer;
        }
    }

    .pull-right.top-check-action {
        > label {
            margin-right: 10px;

            &:last-child {
                margin-right: 0px;
            }
        }

        span.el-checkbox__label {
            padding-left: 3px;
        }
    }
    .item_desc textarea {
        margin-top: 5px;
        width: 100%;
    }
</style>
