import {publisherAjax} from '../../ajax';

export async function fetchAdProviders() {
    return await publisherAjax('api/advertisement/providers');
}

export async function fetchAdvertisementSettings() {
    return await publisherAjax('api/advertisement/settings');
}