# CoinGate Payment Plugin for Shopware 6

Accept cryptocurrency payments on your **Shopware 6** store.

Read the module installation instructions below to get started with CoinGate payment plugin for your shop.

## Installation

Sign up for CoinGate account at <https://coingate.com> for production and <https://sandbox.coingate.com> for testing (sandbox) environment.

The plugin can be easily installed by following the steps below:
- Download the latest .zip, you can find this at https://github.com/coingate/shopware6-plugin/releases
- Navigate to Extensions -> My Extensions in the Shopware 6 Admin Panel
- Upload the zip-file and activate the extension
- Continue with configuring the plugin

## Notes

Please note, that for "Test" mode you **must** generate separate API credentials on <https://sandbox.coingate.com>. API credentials generated on <https://coingate.com> will **not** work for "Test" mode.

Also note, that *Receive Currency* parameter in your module configuration window defines the currency of your settlements from CoinGate. Set it to BTC, USDT, EUR or USD, depending on how you wish to receive payouts. To receive settlements in **Euros** or **U.S. Dollars** to your bank, you have to verify as a merchant on CoinGate (login to your CoinGate account and click *Verification*). If you set your receive currency to **Bitcoin**, verification is not needed.
