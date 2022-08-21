const { Component, Mixin } = Shopware
import template from './verify-api-button.html.twig'

Component.register('coingate-verify-api-button', {
    template,

    inject: ['verifyApiTokenService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data () {
        return {
            isLoading: false,
        };
    },

    computed: {
        pluginConfig() {
            let $parent = this.$parent;

            while ($parent.actualConfigData === undefined) {
                $parent = $parent.$parent
            }

            return $parent.actualConfigData.null;
        }
    },

    methods: {
        check() {
            this.isLoading = true;

            this.verifyApiTokenService.check(this.pluginConfig).then(response => {

                if (! response.live) {
                    if (this.pluginConfig['CoinGatePayment.config.apiToken']) {
                        this.createNotificationError({
                            title: "Live API Connection",
                            message: "Connection failed."
                        })
                    }
                } else {
                    this.createNotificationSuccess({
                        title: "Live API Connection",
                        message: "Connection established successfully."
                    })
                }

                if (! response.sandbox) {
                    if (this.pluginConfig['CoinGatePayment.config.apiTokenForSandbox']) {
                        this.createNotificationError({
                            title: "Sandbox API Connection",
                            message: "Connection failed."
                        })
                    }
                } else {
                    this.createNotificationSuccess({
                        title: "Sandbox API Connection",
                        message: "Connection established successfully."
                    })
                }

                this.isLoading = false;
            });
        }
    }
})
