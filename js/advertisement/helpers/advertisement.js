import {__} from '@wordpress/i18n';

export const DEFAULT_PROVIDER = 1;

export function getExample(key) {
    switch (key) {
        case 'adsense':
            return __('The uri supplied by AdSense, ie.', 'isset-video') + ' /123456789/external/ad_rule_samples';
        case 'adsterra':
            return __('The key for your VAST endpoint supplied by Adsterra, ie.', 'isset-video') + ' a123456789123456789abc1234ab123a';
        case 'custom':
            return __('The endpoint supplied by your ad provider, ie.', 'isset-video') + ' https://your.ad.provider.com/url-to-vast-endpoint';
        default:
            return '';
    }
}

export const DEFAULT_ADVERTISEMENT = {
    enabled: false,
    uri: '',
    skippable: true,
    provider: DEFAULT_PROVIDER,
    positions: ['before'],
}