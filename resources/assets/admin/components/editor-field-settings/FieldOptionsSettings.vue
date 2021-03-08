<template>
<el-form label-position="top" label-width="120px">
    <div :class="optionFieldsSection == 'generalEditOptions' ? 'option-fields-section_active' : ''" class="option-fields-section">
        <h5 @click="toggleFieldsSection('generalEditOptions')"
            :class="optionFieldsSection == 'generalEditOptions' ? 'active' : ''"
            class="option-fields-section--title">
            {{ editItem.editor_options.title }}
        </h5>

        <transition name="slide-fade">
            <div v-if="optionFieldsSection == 'generalEditOptions'" class="option-fields-section--content">
                <template v-for="(listItem, key, i) in generalEditOptions"
                          v-if="elementOptions.includes(key) && dependancyPass(listItem)">
                    <component
                        :is="guessElTemplate(listItem)"
                        v-model="vModelFinder(key)[key]"
                        :editItem="editItem"
                        :prop="key"
                        :form_items="form_items"
                        :listItem="listItem">
                    </component>
                </template>
            </div>
        </transition>
    </div>

    <div :class="optionFieldsSection == 'advancedEditOptions' ? 'option-fields-section_active' : ''" class="option-fields-section">
        <template v-if="haveSettings(advancedEditOptions)">
            <h5 @click="toggleFieldsSection('advancedEditOptions')"
                :class="optionFieldsSection == 'advancedEditOptions' ? 'active' : ''"
                class="option-fields-section--title">
                Advanced Options
            </h5>

            <transition name="slide-fade">
                <div v-if="optionFieldsSection == 'advancedEditOptions'" class="option-fields-section--content">
                    <template v-for="listItem, key, i in advancedEditOptions">
                        <component
                            v-if="elementOptions.includes(key) && dependancyPass(listItem)"
                            :is="guessElTemplate(listItem)"
                            v-model="vModelFinder(key)[key]"
                            :form_items="form_items"
                            :editItem="editItem"
                            :listItem="listItem">
                        </component>
                    </template>
                </div>
            </transition>
        </template>
    </div>
</el-form>
</template>

<script type="text/babel">

import select from './templates/select.vue';
import nameAttr from './templates/nameAttr.vue';
import infoBlock from './templates/infoBlock.vue';
import inputText from './templates/inputText.vue';
import inputCalculationSettings from './templates/inputCalculationSettings.vue';
import inputNumber from './templates/inputNumber.vue';
import customMask from './templates/customMask.vue';
import nameFields from './templates/nameFields.vue';
import inputValue from './templates/inputValue.vue';
import inputColor from './templates/inputColor.vue';
import inputRadio from './templates/inputRadio.vue';
import gridRowCols from './templates/gridRowCols.vue';
import radioButton from './templates/radioButton.vue';
import addressFields from './templates/addressFields.vue';
import inputTextarea from './templates/inputTextarea.vue';
import inputHTML from './templates/inputHTML.vue';
import inputCheckbox from './templates/inputCheckbox.vue';
import selectOptions from './templates/select-options.vue';
import advancedOptions from './templates/advanced-options.vue';
import pricingOptions from './templates/pricing-options.vue';
import prevNextButton from './templates/prevNextButton.vue';
import selectBtnStyle from './templates/selectBtnStyle.vue';
import customHookName from './templates/customHookName.vue';
import validationRules from './templates/validationRules.vue';
import customStepTitles from './templates/customStepTitles.vue';
import conditionalLogics from './templates/conditionalLogics.vue';
import customCountryList from './templates/customCountryList.vue';
import productFieldTypes from './templates/productFieldTypes.vue';
import customRepeatFields from './templates/customRepeatFields.vue';
import validationRulesForm from './templates/validationRulesForm.vue';
import inputRequiredFieldText from './templates/inputRequiredFieldText.vue';
import chainSelectDataSource from './templates/chainSelectDataSource.vue';
import paymentMethodsConfig from './templates/paymentMethodsConfig.vue';
import targetProduct from './templates/targetProduct.vue';
import inputYesNoCheckBox from "./templates/inputYesNoCheckbox";
import fieldsRepeatSettings from "./templates/fieldsRepeatSettings";

export default {
    name: 'FieldOptionsSettings',
    props: [
        'editItem',
        'form_items',
        'generalEditOptions',
        'advancedEditOptions'
    ],
    components: {
        ff_select: select,
        ff_radio: inputRadio,
        ff_nameAttr: nameAttr,
        ff_inputText: inputText,
        ff_inputNumber: inputNumber,
        ff_infoBlock: infoBlock,
        ff_customMask: customMask,
        ff_inputValue: inputValue,
        ff_nameFields: nameFields,
        ff_inputColor: inputColor,
        ff_radioButton: radioButton,
        ff_gridRowCols: gridRowCols,
        ff_inputTextarea: inputTextarea,
        ff_inputHTML: inputHTML,
        ff_inputCheckbox: inputCheckbox,
        ff_selectOptions: selectOptions,
        ff_advancedOptions: advancedOptions,
        ff_pricingOptions: pricingOptions,
        ff_addressFields: addressFields,
        ff_customHookName: customHookName,
        ff_selectBtnStyle: selectBtnStyle,
        ff_prevNextButton: prevNextButton,
        ff_validationRules: validationRules,
        ff_customStepTitles: customStepTitles,
        ff_customCountryList: customCountryList,
        ff_productFieldTypes: productFieldTypes,
        ff_conditionalLogics: conditionalLogics,
        ff_customRepeatFields: customRepeatFields,
        ff_validationRulesForm: validationRulesForm,
        ff_inputCalculationSettings: inputCalculationSettings,
        ff_inputRequiredFieldText: inputRequiredFieldText,
        ff_chainSelectDataSource: chainSelectDataSource,
        ff_paymentMethodsConfig: paymentMethodsConfig,
        ff_targetProduct: targetProduct,
        ff_inputYesNoCheckBox: inputYesNoCheckBox,
        ff_fieldsRepeatSettings: fieldsRepeatSettings
    },
    data() {
        return {
            optionFieldsSection: 'generalEditOptions'
        }
    },
    computed: {
        /**
         * All configurable options of an element
         * What is shown in the sidebar edit option is determined here
         * @return {Array}
         */
        elementOptions() {
            let elementOptions = [];

            _ff.each(this.editItem.attributes, (value, name) => {
                elementOptions.push(name);
            });

            _ff.each(this.editItem.settings, (value, name) => {
                elementOptions.push(name);
            });

            if (_ff.has(this.editItem, 'options')) {
                elementOptions.push('options');
            }

            if (_ff.has(this.editItem, 'fields')) {
                const fieldsTempl = _ff.snakeCase(this.editItem.editor_options.template);
                elementOptions.push(fieldsTempl);
            }

            return elementOptions;
        }
    },
    methods: {
        /**
         * Determine the input v-model parent object
         * @param attr
         */
        vModelFinder(attr) {
            if (_ff.has(this.editItem.attributes, attr)) {
                return this.editItem.attributes
            } else {
                return this.editItem.settings
            }
        },

        /**
         * This determins to show/hide "general" and "advanced" settings
         * @param targetObj
         * @return {number}
         */
        haveSettings(targetObj) {
            let total = 0;
            _ff.each(this.elementOptions, (el_key) => {
                if(_ff.has(targetObj, el_key)) {
                    total++;
                }
            });
            return total;
        },

        /**
        * Helper function for show/hide dependent elements
        & @return {Boolean}
         */
        compare(operand1, operator, operand2) {
            switch(operator) {
                case '==':
                    return operand1 == operand2
                    break;
                case '!=':
                    return operand1 != operand2
                    break;
            }
        },

        /**
         * Checks if a prop is dependent on another
         * @param listItem
         * @return {boolean}
         */
        dependancyPass(listItem) {
            if (listItem.dependency) {
                let optionPaths = listItem.dependency.depends_on.split('/');

                let dependencyVal = optionPaths.reduce((obj, prop) => {
                    return obj[prop]
                }, this.editItem);

                if ( this.compare(listItem.dependency.value, listItem.dependency.operator, dependencyVal) ) {
                    return true;
                }
                return false;
            }
            return true;
        }
    },
};
</script>
