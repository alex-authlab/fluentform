<template>
    <div>
        <vddl-draggable class="panel__body--item js-editor-item"
                        :class="{ 'selected': editItem.uniqElKey == item.uniqElKey }"
                        :draggable="item"
                        :index="index"
                        effect-allowed="move"
                        type="existingElement"
                        :dragstart="handleDragstart"
                        :dragend="handleDragend"
                        :moved="handleMoved"
                        :wrapper="wrapper">

            <div @click="editSelected(index, item)" class="item-actions-wrapper"
                 :class="item.element == 'container' ? 'hover-action-top-right' : 'hover-action-middle'">
                <div class="item-actions">
                    <i class="icon icon-arrows"></i>
                    <i @click="editSelected(index, item)" class="icon icon-pencil"></i>
                    <i @click="duplicateSelected(index, item)" class="icon icon-clone"></i>
                    <i @click="askRemoveConfirm(index)" class="icon icon-trash-o"></i>
                </div>
            </div>

            <i @click.stop="editorInserterPopup(index, wrapper)" class="popup-search-element">+</i>

            <div v-if="item.element == 'container'" class="item-container">
                <div v-for="containerRow in item.columns" class="col">
                    <vddl-list class="panel__body"
                               :list="containerRow.fields"
                               :drop="handleDrop"
                               :horizontal="false">

                        <div v-show="!containerRow.fields.length" style="padding-top: 13px;"
                             class="empty-dropzone-placeholder">
                            <i @click.stop="editorInserterPopup(0, containerRow.fields)"
                               class="popup-search-element">+</i>
                        </div>

                        <list v-for="(field, list_index) in containerRow.fields"
                              :key="field.uniqElKey"
                              :item="field"
                              :index="list_index"
                              :handleEdit="handleEdit"
                              :allElements="allElements"
                              :editItem="editItem"
                              :wrapper="containerRow.fields">
                        </list>
                    </vddl-list>
                </div>
            </div>

            <template v-if="item.element != 'container' && hasRegistered(item)">
                <div class="ff_condition_icon" v-html="maybeConditionIcon(item.settings)"></div>
                <component :is="guessElTemplate(item)" :item="item"></component>
                <p style="font-style: italic;" v-if="item.settings.help_message" class="help-text"
                   v-html="item.settings.help_message"></p>
            </template>
        </vddl-draggable>
        <ff_removeElConfirm
                :visibility.sync="showRemoveElConfirm"
                @on-confirm="onRemoveElConfirm"/>
    </div>
</template>

<script type="text/babel">
    import {mapMutations} from 'vuex';

    import select from './templates/select.vue'
    import taxonomy from './templates/taxonomy.vue'
    import chainedSelect from './templates/chainedSelect.vue'
    import ratings from './templates/ratings.vue'
    import netPromoter from './templates/netPromoter.vue'
    import formStep from './templates/formStep.vue'
    import recaptcha from './templates/recaptcha.vue'
    import inputText from './templates/inputText.vue'
    import inputSlider from './templates/inputSlider'
    import inputFile from './templates/inputFile.vue'
    import shortcode from './templates/shortcode.vue'
    import inputRadio from './templates/inputRadio.vue'
    import inputCheckable from './templates/inputCheckable.vue'
    import nameFields from './templates/nameFields.vue'
    import actionHook from './templates/actionHook.vue'
    import customHTML from './templates/customHTML.vue'
    import inputHidden from './templates/inputHidden.vue'
    import buttonSubmit from './templates/buttonSubmit.vue'
    import sectionBreak from './templates/sectionBreak.vue'
    import repeatFields from './templates/repeatFields.vue'
    import selectCountry from './templates/selectCountry.vue'
    import inputTextarea from './templates/inputTextarea.vue'
    import addressFields from './templates/addressFields.vue'
    import termsCheckbox from './templates/termsCheckbox.vue'
    import inputCheckbox from './templates/inputCheckbox.vue'
    import checkableGrids from './templates/checkableGrids.vue'
    import removeElConfirm from './modals/deleteFormElConfirm.vue'
    import imagePlaceholder from './templates/imagePlaceholder.vue'
    import customButton from './templates/customButton.vue'
    import product from './templates/product.vue'
    import paymentMethodHolder from './templates/paymentMethodHolder.vue'
    import inputMultiPayment from './templates/inputMultiPayment.vue';

    export default {
        name: 'list',
        props: [
            'item',
            'index',
            'wrapper',
            'editItem',
            'handleDrop',
            'handleEdit',
            'allElements',
            'handleDragend',
            'handleDragstart',
        ],
        components: {
            ff_select: select,
            ff_taxonomy: taxonomy,
            ff_chainedSelect: chainedSelect,
            ff_ratings: ratings,
            ff_net_promoter: netPromoter,
            ff_formStep: formStep,
            ff_inputFile: inputFile,
            ff_inputText: inputText,
            ff_inputSlider: inputSlider,
            ff_shortcode: shortcode,
            ff_recaptcha: recaptcha,
            ff_inputRadio: inputRadio,
            ff_inputCheckable: inputCheckable,
            ff_nameFields: nameFields,
            ff_actionHook: actionHook,
            ff_customHTML: customHTML,
            ff_inputHidden: inputHidden,
            ff_buttonSubmit: buttonSubmit,
            ff_repeatFields: repeatFields,
            ff_sectionBreak: sectionBreak,
            ff_selectCountry: selectCountry,
            ff_inputTextarea: inputTextarea,
            ff_addressFields: addressFields,
            ff_termsCheckbox: termsCheckbox,
            ff_inputCheckbox: inputCheckbox,
            ff_checkableGrids: checkableGrids,
            ff_removeElConfirm: removeElConfirm,
            ff_imagePlaceholder: imagePlaceholder,
            ff_product: product,
            ff_inputPaymentMethods: paymentMethodHolder,
            ff_customButton: customButton,
            ff_inputMultiPayment: inputMultiPayment,
            ff_fieldsRepeatSettings: repeatFields
        },
        data() {
            return {
                showRemoveElConfirm: false,
                removeElIndex: null,
            }
        },
        methods: {
            ...mapMutations(['changeFieldMode', 'updateSidebar']),

            /**
             * To check if element template is registered.
             */
            hasRegistered(item) {
                if(!item || !item.editor_options) {
                    return false;
                }
                const dynamicComponent = 'ff_' + item.editor_options.template;
                const registeredComponents = Object.keys(this.$options.components);

                return registeredComponents.includes(dynamicComponent);
            },

            maybeConditionIcon(settings) {
                let status = settings && settings.conditional_logics && settings.conditional_logics.status;
                if(status) {
                    return '<i class="el-icon el-icon-guide"></i>';
                }
                return '';
            },

            /**
             * Fire an event on close of editor item inserter popup
             */
            editorInserterDismiss(e) {
                FluentFormEditorEvents.$emit('editor-inserter-dismiss', e);
            },

            /**
             * Remove the moved item from it's old place
             * @param {Object} vddl options
             */
            handleMoved({index, list}) {
                list.splice(index, 1);
            },

            /**
             * Action for clicking on edit icon
             * from an element
             * @param index
             * @param item
             */
            editSelected(index, item) {
                this.editorInserterDismiss();
                this.handleEdit(item);
            },

            /**
             * Action for clicking on copy icon
             * from an element
             * @param index
             * @param item
             */
            duplicateSelected(index, item) {
                let freshCopy = JSON.parse(JSON.stringify(item));
                this.makeUniqueNameAttr(this.allElements, freshCopy);
                if (index > -1) {
                    this.wrapper.splice(index + 1, 0, freshCopy);
                }
            },

            /**
             * Action for clicking on trash icon
             * from an element
             * @param index
             */
            askRemoveConfirm(index) {
                this.showRemoveElConfirm = true;
                this.removeElIndex = index;
            },

            /**
             * Editor element remove confirmation action
             */
            onRemoveElConfirm() {
                if (this.removeElIndex > -1) {
                    const el = this.wrapper[this.removeElIndex];
                    FluentFormEditorEvents.$emit('onElRemoveSuccess', el.attributes.name);
                    this.wrapper.splice(this.removeElIndex, 1);
                    this.showRemoveElConfirm = false;
                }
            },

            /**
             * Editor inserter popup show event
             */
            editorInserterPopup(index, wrapper) {
                FluentFormEditorEvents.$emit('editor-inserter-popup', index, wrapper, this.$el);

                if (jQuery(event.target).closest('.item-container').length) {
                    FluentFormEditorEvents.$emit('editor-inserter-in-container');
                }
            }
        }
    };
</script>
