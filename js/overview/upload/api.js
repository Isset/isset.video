import {publisherAjax} from '../../ajax';

export async function uploadCustomStill(uuid, file) {
    return await publisherAjax(`api/publishes/${uuid}/stills/upload-custom-image`, {},'post', {}, file);
}
