<template>
    <div>
        <el-row class="setting_header">
            <el-col :md="24">
                <h2>Google reCAPTCHA Settings </h2>

                <p>
                    Fluent Forms integrates with both reCAPTCHA v2 and v3, a free service that protects your website from spam and
                    abuse. Please note, these settings are required only if you decide to use the reCAPTCHA field.

                    <a href="http://www.google.com/recaptcha/" target="_blank">
                        Read more about reCAPTCHA.
                    </a>
                </p>
                <p><b>Please generate API key and API secret using reCaptcha Version 2 or 3</b></p>
            </el-col>
        </el-row>


        <div class="section-body">
            <el-form label-width="205px" label-position="left" v-loading="loading">
                <!--reCaptcha version-->
                <el-form-item>
                    <template slot="label">
                        reCAPTCHA Type
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>reCAPTCHA Type</h3>
                                <p>
                                    Select your reCAPTCHA version. if you do not have <br>
                                    it yet you can register for one at the provided link. <br>
                                    reCAPTCHA is a free service.
                                </p>
                            </div>

                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>
                    <el-radio-group v-model="reCaptcha.api_version"  @change="load">
                        <el-radio label="v2_visible" >V2 Visible</el-radio>
                        <el-radio label="v3_invisible" >V3 InVisible</el-radio>
                    </el-radio-group>
                </el-form-item>
                <!--Site key-->
                <el-form-item>
                    <template slot="label">
                        Site Key
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>reCAPTCHA Site Key</h3>
                                <p>
                                    Enter your reCAPTCHA Site Key, if you do not have <br>
                                    a key you can register for one at the provided link. <br>
                                    reCAPTCHA is a free service.
                                </p>
                            </div>

                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>

                    <el-input v-model="reCaptcha.siteKey" @change="load"></el-input>
                </el-form-item>

                <!--Secret key-->
                <el-form-item>
                    <template slot="label">
                        Secret Key
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>reCAPTCHA Secret Key</h3>

                                <p>
                                    Enter your reCAPTCHA Secret Key, if you do not have <br>
                                    a key you can register for one at the provided link. <br>
                                    reCAPTCHA is a free service.
                                </p>
                            </div>

                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>

                    <el-input type="password" v-model="reCaptcha.secretKey" @change="load"></el-input>
                </el-form-item>

                <!--Validate Keys-->
                <el-form-item label="Validate Keys" v-if="siteKeyChanged && reCaptcha.api_version == 'v2_visible'">
                    <div class="g-recaptcha" id="reCaptcha" :data-sitekey="reCaptcha.siteKey"></div>
                </el-form-item>


                <el-form-item>
                    <el-button
                        type="danger"
                        icon="el-icon-delete"
                        size="medium"
                        @click="clearSettings"
                        :loading="clearing"
                    >Clear Settings</el-button>

                    <el-button
                        type="success"
                        icon="el-icon-success"
                        size="medium"
                        @click="save"
                        :disabled="disabled"
                        :loading="saving"
                    >Save Settings</el-button>
                </el-form-item>
            </el-form>

            <div v-if="reCaptcha_status">
                <p>Your reCAPTCHA is valid</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "reCaptcha",
        props: ['app'],
        data() {
            return {
                reCaptcha: {
                    siteKey: '',
                    secretKey: '',
                    api_version: ''
                },
                reCaptcha_status: false,
                siteKeyChanged: false,
                disabled: false,
                saving: false,
                clearing: false,
                loading:false
            }
        },
        methods: {
            load() {
                if (! this.validate()) {
                    this.disabled = false;
                    this.siteKeyChanged = false;
                    return;
                } else {
                    this.disabled = true;
                    this.siteKeyChanged = true;
                }
                this.loadScript();

                let siteKey = this.reCaptcha.siteKey;
                switch (this.reCaptcha.api_version) {
                    case 'v2_visible':
                        this.$nextTick(() => {

                            let id = 'reCaptcha';
                            let $reCaptcha = jQuery('#' + id);
                            $reCaptcha.html('');
                            window.___grecaptcha_cfg.clients = {};
                            grecaptcha.render(id, {
                                'sitekey' : siteKey,
                                'callback' : (token) => {
                                    this.reCaptcha.token = token;
                                    this.disabled = false;
                                }
                            });

                        });
                        break;

                    case 'v3_invisible':
                        let that = this;
                        setTimeout(() => {
                            grecaptcha.execute(siteKey, {
                                action : 'ff_submit/admin'
                            }).then(function (token) {
                                that.reCaptcha.token = token;
                                that.disabled = false;
                            })
                        }, 1000);

                        break;
                }
            },
            save() {
                if (! this.validate()) {
                    return this.$notify.error({
                        title: 'Error!',
                        message: 'Missing required fields.',
                        offset: 30
                    });
                }

                this.saving = true;
                FluentFormsGlobal.$post({
                    action: 'fluentform-global-settings-store',
                    key: 'reCaptcha',
                    reCaptcha: this.reCaptcha
                }).then(response => {
                    this.reCaptcha_status = response.data.status;
                    this.$notify.success({
                        title: 'Success!',
                        message: response.data.message,
                        offset: 30
                    });
                })
                .fail(error => {
                    this.reCaptcha_status = parseInt(error.responseJSON.data.status, 10);
                    let title = this.reCaptcha_status === 1 ? 'Warning!' : 'Error!';
                    let method = this.reCaptcha_status === 1 ? 'warning' : 'error';
                    this.$notify[method]({
                        title: title,
                        message: error.responseJSON.data.message,
                        offset: 30
                    });
                }).always(r => {
                    this.saving = false;
                });
            },
            clearSettings() {
                this.clearing = true;
                FluentFormsGlobal.$post({
                    action: 'fluentform-global-settings-store',
                    key: 'reCaptcha',
                    reCaptcha: 'clear-settings'
                }).then(response => {
                    this.reCaptcha_status = response.data.status;
                    this.reCaptcha = { siteKey: '', secretKey: '' };
                    this.$notify.success({
                        title: 'Success!',
                        message: response.data.message,
                        offset: 30
                    });
                })
                .fail(error => {
                    this.reCaptcha_status = error.responseJSON.data.status;
                    this.$notify.error({
                        title: 'Oops!',
                        message: 'Something went wrong.',
                        offset: 30
                    });
                }).always(r => {
                    this.clearing = false;
                });
            },
            validate() {
                return ! ! (this.reCaptcha.siteKey && this.reCaptcha.secretKey);
            },
            getReCaptchaSettings() {
                this.loading = true;
                FluentFormsGlobal.$get({
                    action: 'fluentform-global-settings',
                    key: [
                        '_fluentform_reCaptcha_details',
                        '_fluentform_reCaptcha_keys_status'
                    ]
                })
                .then(response => {
                    const recaptcha = response.data._fluentform_reCaptcha_details || { siteKey: '', secretKey: '' };
                    if(!recaptcha.api_version) {
                        recaptcha.api_version = 'v2_visible';
                    }
                    this.reCaptcha = recaptcha;
                    this.reCaptcha_status = response.data._fluentform_reCaptcha_keys_status;

                })
                .always(()=>{
                  this.loading = false;
                });
            },
            loadScript(){
                let recaptchaScript = document.createElement('script');
                let reCAPTCHA_site_key = this.reCaptcha.siteKey;

                if(this.reCaptcha.api_version == 'v3_invisible' ){
                    recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js?render='+reCAPTCHA_site_key);

                }else{
                    recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js');
                }
                //remove previous script and add new one
                document.getElementById("ff_admin_recaptcha_script").innerHTML = "";
                document.getElementById("ff_admin_recaptcha_script").appendChild(recaptchaScript);

            }
        },
        mounted() {
            this.getReCaptchaSettings();
            this.loadScript();
        },
        created() {
            let recaptchaScript = document.createElement('div');

            recaptchaScript.id= 'ff_admin_recaptcha_script';

            document.body.appendChild(recaptchaScript);
        },
    }
</script>
