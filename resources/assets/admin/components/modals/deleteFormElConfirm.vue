<template>
<div :class="{'ff_backdrop': visibility}">
    <el-dialog
        title="Confirmation"
        :visible.sync="visibility"
        :before-close="close"
        class="text-center"
        width="30%">
        <span><strong>Are you sure you want to delete this field?</strong></span>

        <div slot="footer" class="text-center dialog-footer">
            <el-button @click="close">Cancel</el-button>
            <el-button type="primary" @click="$emit('on-confirm')">Confirm</el-button>
        </div>
    </el-dialog>
</div>
</template>

<script>
export default {
    name: 'deleteFormElConfirm',
    props: {
        visibility: Boolean
    },
    watch: {
        visibility() {
            if (this.visibility) {
                setTimeout( _ => {
                    const zIndex = Number(jQuery('.v-modal').css('z-index'));
                    jQuery('.ff_form_wrap').css('z-index', zIndex + 1);
                }, 0);
            }
        }
    },
    methods: {
        close() {
            this.$emit('update:visibility', false);
        }
    }
}
</script>