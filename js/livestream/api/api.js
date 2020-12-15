import {publisherAjax} from '../../ajax';
import {wpAjax} from '../../ajax';

export async function fetchActiveLivestreams() {
    const response = await publisherAjax('api/livestreams');

    return response.filter(stream => stream.date_ended === null);
}

export async function fetchLiveStreamDetails(uuid) {
    return await wpAjax('isset-video-fetch-livestream-details', {uuid});
}

export async function createLiveStream() {
    return await publisherAjax('api/livestreams/create', {},'post');
}